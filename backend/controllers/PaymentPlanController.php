<?php
require_once __DIR__ . '/../../backend/config/db_connect.php';

class PaymentPlanController {
    private $pdo;

    /**
     * Constructor to initialize the PDO connection
     * @param PDO $pdo Database connection
     */
    public function __construct($pdo) {
        $this->pdo = $pdo;
    }

    /**
     * Create a new payment plan with its installments
     * @param string $plan_name Name of the payment plan
     * @param float $total_amount Total amount of the payment plan
     * @param array $installments Array of installment data (name => string, amount => float)
     * @return bool Success status of the operation
     */
    public function createPaymentPlan($plan_name, $total_amount, $installments) {
        try {
            // Begin transaction
            $this->pdo->beginTransaction();

            // Insert the payment plan
            $stmt = $this->pdo->prepare("INSERT INTO payment_plans (plan_name, total_amount, created_at) VALUES (?, ?, NOW())");
            $stmt->execute([$plan_name, $total_amount]);
            $plan_id = $this->pdo->lastInsertId();

            // Insert each installment
            $stmt = $this->pdo->prepare("INSERT INTO plan_installments (plan_id, installment_name, amount, created_at) VALUES (?, ?, ?, NOW())");
            foreach ($installments as $installment) {
                $stmt->execute([$plan_id, $installment['name'], $installment['amount']]);
            }

            // Commit transaction
            $this->pdo->commit();
            error_log("Payment plan '$plan_name' created successfully with plan_id=$plan_id at " . date('Y-m-d H:i:s'));
            return true;
        } catch (PDOException $e) {
            // Rollback transaction on error
            $this->pdo->rollBack();
            error_log("Error creating payment plan '$plan_name': " . $e->getMessage() . " at " . date('Y-m-d H:i:s'));
            return false;
        }
    }

    /**
     * Get all payment plans
     * @return array Array of payment plans
     */
    public function getAllPaymentPlans() {
        try {
            $stmt = $this->pdo->query("SELECT * FROM payment_plans ORDER BY created_at DESC");
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error fetching payment plans: " . $e->getMessage() . " at " . date('Y-m-d H:i:s'));
            return [];
        }
    }

    /**
     * Get details of a specific payment plan including its installments
     * @param int $plan_id ID of the payment plan
     * @return array|null Payment plan details or null if not found
     */
    public function getPaymentPlanById($plan_id) {
        try {
            $stmt = $this->pdo->prepare("SELECT * FROM payment_plans WHERE id = ?");
            $stmt->execute([$plan_id]);
            $plan = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($plan) {
                $stmt = $this->pdo->prepare("SELECT * FROM plan_installments WHERE plan_id = ? ORDER BY created_at ASC");
                $stmt->execute([$plan_id]);
                $plan['installments'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
            }

            return $plan ?: null;
        } catch (PDOException $e) {
            error_log("Error fetching payment plan with id=$plan_id: " . $e->getMessage() . " at " . date('Y-m-d H:i:s'));
            return null;
        }
    }

    /**
     * Update an existing payment plan
     * @param int $plan_id ID of the payment plan to update
     * @param string $plan_name New name of the payment plan
     * @param float $total_amount New total amount
     * @param array $installments New installment data
     * @return bool Success status of the operation
     */
    public function updatePaymentPlan($plan_id, $plan_name, $total_amount, $installments) {
        try {
            $this->pdo->beginTransaction();

            $stmt = $this->pdo->prepare("UPDATE payment_plans SET plan_name = ?, total_amount = ?, updated_at = NOW() WHERE id = ?");
            $stmt->execute([$plan_name, $total_amount, $plan_id]);

            // Delete existing installments
            $stmt = $this->pdo->prepare("DELETE FROM plan_installments WHERE plan_id = ?");
            $stmt->execute([$plan_id]);

            // Insert new installments
            $stmt = $this->pdo->prepare("INSERT INTO plan_installments (plan_id, installment_name, amount, created_at) VALUES (?, ?, ?, NOW())");
            foreach ($installments as $installment) {
                $stmt->execute([$plan_id, $installment['name'], $installment['amount']]);
            }

            $this->pdo->commit();
            error_log("Payment plan with id=$plan_id updated successfully at " . date('Y-m-d H:i:s'));
            return true;
        } catch (PDOException $e) {
            $this->pdo->rollBack();
            error_log("Error updating payment plan with id=$plan_id: " . $e->getMessage() . " at " . date('Y-m-d H:i:s'));
            return false;
        }
    }

    /**
     * Delete a payment plan
     * @param int $plan_id ID of the payment plan to delete
     * @return bool Success status of the operation
     */
    public function deletePaymentPlan($plan_id) {
        try {
            $this->pdo->beginTransaction();

            $stmt = $this->pdo->prepare("DELETE FROM plan_installments WHERE plan_id = ?");
            $stmt->execute([$plan_id]);

            $stmt = $this->pdo->prepare("DELETE FROM payment_plans WHERE id = ?");
            $stmt->execute([$plan_id]);

            $this->pdo->commit();
            error_log("Payment plan with id=$plan_id deleted successfully at " . date('Y-m-d H:i:s'));
            return true;
        } catch (PDOException $e) {
            $this->pdo->rollBack();
            error_log("Error deleting payment plan with id=$plan_id: " . $e->getMessage() . " at " . date('Y-m-d H:i:s'));
            return false;
        }
    }

    public function getInstallmentsByPlanId($plan_id) {
        try {
            $stmt = $this->pdo->prepare("SELECT id, installment_name, amount FROM plan_installments WHERE plan_id = ?");
            $stmt->execute([$plan_id]);
            $installments = $stmt->fetchAll(PDO::FETCH_ASSOC);
            error_log("Fetched installments for plan_id $plan_id: " . print_r($installments, true) . " at " . date('Y-m-d H:i:s'));
            return $installments ?: [];
        } catch (PDOException $e) {
            error_log("Error fetching installments for plan_id $plan_id: " . $e->getMessage() . " at " . date('Y-m-d H:i:s'));
            return [];
        }
    }
}