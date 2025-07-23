<?php
class PaymentController {
    private $pdo;
    private $uploadDir = __DIR__ . '/../../uploads/payments/';

    public function __construct($pdo) {
        $this->pdo = $pdo;
        if (!is_dir($this->uploadDir)) {
            mkdir($this->uploadDir, 0777, true);
        }
    }

    public function addPayment($lead_id, $payment_name, $amount, $file) {
        try {
            if (!is_numeric($amount) || $amount <= 0) {
                error_log("Invalid payment amount: $amount for lead_id $lead_id at " . date('Y-m-d H:i:s'));
                return false;
            }

            if (!isset($file['error']) || $file['error'] !== UPLOAD_ERR_OK) {
                error_log("File upload error: " . ($file['error'] ?? 'No file') . " for lead_id $lead_id at " . date('Y-m-d H:i:s'));
                return false;
            }

            $fileName = uniqid() . '_' . basename($file['name']);
            $filePath = $this->uploadDir . $fileName;

            if (!move_uploaded_file($file['tmp_name'], $filePath)) {
                error_log("Failed to move uploaded file to $filePath for lead_id $lead_id at " . date('Y-m-d H:i:s'));
                return false;
            }

            // Store only the filename in the database, adjust if full path is needed
            $receiptPath = $fileName;

            $stmt = $this->pdo->prepare("INSERT INTO payments (lead_id, payment_name, amount, receipt_path, created_at) VALUES (?, ?, ?, ?, NOW())");
            $success = $stmt->execute([$lead_id, $payment_name, $amount, $receiptPath]);

            if (!$success) {
                unlink($filePath); // Clean up file if DB insert fails
                error_log("Database insert failed for payment with lead_id $lead_id at " . date('Y-m-d H:i:s') . ": " . print_r($stmt->errorInfo(), true));
            } else {
                error_log("Payment added successfully for lead_id $lead_id: amount=$amount, name=$payment_name, receipt=$receiptPath");
            }

            return $success;
        } catch (PDOException $e) {
            error_log("PDO Exception in addPayment: " . $e->getMessage() . " for lead_id $lead_id at " . date('Y-m-d H:i:s'));
            if (file_exists($filePath)) unlink($filePath); // Clean up on exception
            return false;
        }
    }

    public function getPaymentsByLead($lead_id) {
        try {
            $stmt = $this->pdo->prepare("SELECT id, amount, payment_name, receipt_path, created_at FROM payments WHERE lead_id = ? ORDER BY created_at DESC");
            $stmt->execute([$lead_id]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("PDO Exception in getPaymentsByLead: " . $e->getMessage() . " for lead_id $lead_id at " . date('Y-m-d H:i:s'));
            return [];
        }
    }

    public function deletePayment($payment_id, $lead_id) {
        try {
            $stmt = $this->pdo->prepare("SELECT receipt_path FROM payments WHERE id = ? AND lead_id = ?");
            $stmt->execute([$payment_id, $lead_id]);
            $payment = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($payment) {
                $stmt = $this->pdo->prepare("DELETE FROM payments WHERE id = ? AND lead_id = ?");
                $success = $stmt->execute([$payment_id, $lead_id]);

                if ($success && file_exists($this->uploadDir . $payment['receipt_path'])) {
                    unlink($this->uploadDir . $payment['receipt_path']); // Clean up file
                }

                return $success;
            }
            return false;
        } catch (PDOException $e) {
            error_log("PDO Exception in deletePayment: " . $e->getMessage() . " for payment_id $payment_id at " . date('Y-m-d H:i:s'));
            return false;
        }
    }

    public function assignPaymentPlan($lead_id, $plan_id, $paid_amounts, $paid_dates = [], $invoice_files = []) {
        try {
            $this->pdo->beginTransaction();

            // Check if a plan is already assigned
            $stmt = $this->pdo->prepare("SELECT plan_id FROM lead_payment_plans WHERE lead_id = ?");
            $stmt->execute([$lead_id]);
            if ($stmt->fetch()) {
                throw new Exception("A payment plan is already assigned to this lead. Only one plan is allowed per lead.");
            }

            // Validate plan exists
            $stmt = $this->pdo->prepare("SELECT * FROM payment_plans WHERE id = ?");
            $stmt->execute([$plan_id]);
            if (!$stmt->fetch()) {
                throw new Exception("Invalid payment plan ID");
            }

            // Validate and fetch installments
            $stmt = $this->pdo->prepare("SELECT id, amount, installment_name FROM plan_installments WHERE plan_id = ?");
            $stmt->execute([$plan_id]);
            $installments = $stmt->fetchAll(PDO::FETCH_ASSOC);
            if (empty($installments)) {
                throw new Exception("No installments found for the selected plan");
            }

            // Validate paid amounts and process invoices (optional)
            $uploadDir = __DIR__ . '/../../uploads/invoices/';
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0777, true);
            }

            $stmt = $this->pdo->prepare("INSERT INTO lead_payment_plans (lead_id, plan_id, assigned_at) VALUES (?, ?, NOW())");
            $stmt->execute([$lead_id, $plan_id]);
            $assignment_id = $this->pdo->lastInsertId();

            $stmt = $this->pdo->prepare("INSERT INTO payment_records (lead_id, plan_installment_id, amount_paid, paid_date, invoice_path, created_at) VALUES (?, ?, ?, ?, ?, NOW())");
            foreach ($installments as $installment) {
                $installment_id = $installment['id'];
                $max_amount = $installment['amount'];
                $paid_amount_key = "paid_amount_$installment_id";
                $paid_date_key = "paid_date_$installment_id";
                $invoice_key = "invoice_$installment_id";

                $paid_amount = isset($paid_amounts[$installment_id]) && floatval($paid_amounts[$installment_id]) > 0 ? floatval($paid_amounts[$installment_id]) : 0;
                if ($paid_amount > $max_amount || $paid_amount < 0) {
                    throw new Exception("Paid amount for installment $installment_id exceeds $max_amount or is negative");
                }

                $paid_date = isset($paid_dates[$installment_id]) ? $paid_dates[$installment_id] : date('Y-m-d');
                $invoice_path = null;
                if (isset($invoice_files[$installment_id]) && $invoice_files[$installment_id]['error'] === UPLOAD_ERR_OK) {
                    $fileName = uniqid() . '_' . basename($invoice_files[$installment_id]['name']);
                    $filePath = $uploadDir . $fileName;
                    if (move_uploaded_file($invoice_files[$installment_id]['tmp_name'], $filePath)) {
                        $invoice_path = $filePath;
                    } else {
                        throw new Exception("Failed to upload invoice for installment $installment_id");
                    }
                }

                // Only insert payment records if a paid amount is provided
                if ($paid_amount > 0) {
                    $stmt->execute([$lead_id, $installment_id, $paid_amount, $paid_date, $invoice_path]);
                }
            }

            $this->pdo->commit();
            error_log("Payment plan $plan_id assigned to lead $lead_id at " . date('Y-m-d H:i:s'));
            return true;
        } catch (PDOException | Exception $e) {
            $this->pdo->rollBack();
            error_log("Error assigning payment plan $plan_id to lead $lead_id: " . $e->getMessage() . " at " . date('Y-m-d H:i:s'));
            return false;
        }
    }

    public function getAssignedPaymentPlan($lead_id) {
        try {
            $stmt = $this->pdo->prepare("SELECT pp.* FROM lead_payment_plans lpp 
                                        JOIN payment_plans pp ON lpp.plan_id = pp.id 
                                        WHERE lpp.lead_id = ? LIMIT 1");
            $stmt->execute([$lead_id]);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            error_log("DEBUG - getAssignedPaymentPlan result: " . print_r($result, true));
            return $result ?: null;
        } catch (PDOException $e) {
            error_log("Error in getAssignedPaymentPlan: " . $e->getMessage());
            return null;
        }
    }

    public function getPaymentRecordsByLead($lead_id) {
        try {
            $stmt = $this->pdo->prepare("SELECT * FROM payment_records WHERE lead_id = ?");
            $stmt->execute([$lead_id]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error fetching payment records for lead $lead_id: " . $e->getMessage() . " at " . date('Y-m-d H:i:s'));
            return [];
        }
    }

    public function getPaymentPlanById($plan_id) {
        try {
            $stmt = $this->pdo->prepare("SELECT p.*, pi.id AS installment_id, pi.installment_name, pi.amount 
                                        FROM payment_plans p 
                                        LEFT JOIN plan_installments pi ON p.id = pi.plan_id 
                                        WHERE p.id = ?");
            $stmt->execute([$plan_id]);
            $plan = $stmt->fetch(PDO::FETCH_ASSOC);
            if ($plan) {
                $stmt = $this->pdo->prepare("SELECT id AS installment_id, installment_name, amount, due_date FROM plan_installments WHERE plan_id = ?");
                $stmt->execute([$plan_id]);
                $plan['installments'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
            }
            return $plan ?: null;
        } catch (PDOException $e) {
            error_log("Error fetching payment plan: " . $e->getMessage() . " at " . date('Y-m-d H:i:s'));
            return null;
        }
    }

    public function getInstallmentsForPlan($lead_id, $plan_id) {
        try {
            error_log("Fetching installments for lead $lead_id, plan $plan_id");
            $stmt = $this->pdo->prepare("SELECT i.id, i.installment_name, i.amount, 
                                        pr.amount_paid, pr.paid_date, pr.receipt_path
                                        FROM plan_installments i
                                        LEFT JOIN payment_records pr ON i.id = pr.plan_installment_id AND pr.lead_id = ?
                                        WHERE i.plan_id = ?
                                        ORDER BY i.id ASC");
            $stmt->execute([$lead_id, $plan_id]);
            $installments = $stmt->fetchAll(PDO::FETCH_ASSOC);
            error_log("Fetched installments: " . print_r($installments, true));
            return $installments;
        } catch (PDOException $e) {
            error_log("Error in getInstallmentsForPlan: " . $e->getMessage());
            return [];
        }
    }

    public function updatePaymentRecords($lead_id, $plan_id, $paid_amounts, $paid_dates = [], $receipt_paths = []) {
        try {
            $this->pdo->beginTransaction();

            $stmt = $this->pdo->prepare("SELECT id, plan_installment_id FROM payment_records WHERE lead_id = ? AND plan_installment_id IN (SELECT id FROM plan_installments WHERE plan_id = ?)");
            $stmt->execute([$lead_id, $plan_id]);
            $existing_records = $stmt->fetchAll(PDO::FETCH_ASSOC);

            foreach ($existing_records as $record) {
                $installment_id = $record['plan_installment_id'];
                $paid_amount = isset($paid_amounts[$installment_id]) ? floatval($paid_amounts[$installment_id]) : 0;
                $paid_date = isset($paid_dates[$installment_id]) ? $paid_dates[$installment_id] : date('Y-m-d');
                $receipt_path = isset($receipt_paths[$installment_id]) ? $receipt_paths[$installment_id] : null;

                if ($paid_amount < 0) {
                    throw new Exception("Paid amount for installment $installment_id cannot be negative");
                }

                $stmt_update = $this->pdo->prepare("UPDATE payment_records SET amount_paid = ?, paid_date = ?, receipt_path = ? WHERE id = ?");
                $stmt_update->execute([$paid_amount, $paid_date, $receipt_path, $record['id']]);
            }

            $this->pdo->commit();
            error_log("Payment records updated for lead $lead_id, plan $plan_id at " . date('Y-m-d H:i:s'));
            return true;
        } catch (PDOException | Exception $e) {
            $this->pdo->rollBack();
            error_log("Error updating payment records for lead $lead_id: " . $e->getMessage() . " at " . date('Y-m-d H:i:s'));
            return false;
        }
    }
}