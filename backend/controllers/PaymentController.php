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

    public function addPayment($lead_id, $amount, $payment_name, $file) {
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

            $stmt = $this->pdo->prepare("INSERT INTO payments (lead_id, amount, payment_name, receipt_path, created_at) VALUES (?, ?, ?, ?, NOW())");
            $success = $stmt->execute([$lead_id, $amount, $payment_name, $filePath]);

            if (!$success) {
                unlink($filePath); // Clean up file if DB insert fails
                error_log("Database insert failed for payment with lead_id $lead_id at " . date('Y-m-d H:i:s'));
            }

            return $success;
        } catch (PDOException $e) {
            error_log("PDO Exception in addPayment: " . $e->getMessage() . " for lead_id $lead_id at " . date('Y-m-d H:i:s'));
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

                if ($success && file_exists($payment['receipt_path'])) {
                    unlink($payment['receipt_path']); // Clean up file
                }

                return $success;
            }
            return false;
        } catch (PDOException $e) {
            error_log("PDO Exception in deletePayment: " . $e->getMessage() . " for payment_id $payment_id at " . date('Y-m-d H:i:s'));
            return false;
        }
    }

    public function assignPaymentPlan($lead_id, $plan_id, $paid_amounts) {
        try {
            $this->pdo->beginTransaction();
    
            // Validate plan exists
            $stmt = $this->pdo->prepare("SELECT * FROM payment_plans WHERE id = ?");
            $stmt->execute([$plan_id]);
            if (!$stmt->fetch()) {
                throw new Exception("Invalid payment plan ID");
            }
    
            // Validate and fetch installments
            $stmt = $this->pdo->prepare("SELECT id, amount FROM plan_installments WHERE plan_id = ?");
            $stmt->execute([$plan_id]);
            $installments = $stmt->fetchAll(PDO::FETCH_ASSOC);
            if (empty($installments)) {
                throw new Exception("No installments found for the selected plan");
            }
    
            // Validate paid amounts
            foreach ($installments as $installment) {
                $installment_id = $installment['id'];
                $max_amount = $installment['amount'];
                if (isset($paid_amounts[$installment_id]) && ($paid_amounts[$installment_id] > $max_amount || $paid_amounts[$installment_id] < 0)) {
                    throw new Exception("Paid amount for installment $installment_id exceeds $max_amount or is negative");
                }
            }
    
            // Insert payment plan assignment
            $stmt = $this->pdo->prepare("INSERT INTO lead_payment_plans (lead_id, plan_id, assigned_at) VALUES (?, ?, NOW())");
            $stmt->execute([$lead_id, $plan_id]);
            $assignment_id = $this->pdo->lastInsertId();
    
            // Record paid amounts
            $stmt = $this->pdo->prepare("INSERT INTO payment_records (lead_id, plan_installment_id, amount_paid, created_at) VALUES (?, ?, ?, NOW())");
            foreach ($paid_amounts as $installment_id => $amount) {
                if ($amount > 0) {
                    $stmt->execute([$lead_id, $installment_id, $amount]);
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
}