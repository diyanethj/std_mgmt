<?php
class Registration {
    private $pdo;

    public function __construct($pdo) {
        $this->pdo = $pdo;
    }

    public function createRegistration($lead_id) {
        $stmt = $this->pdo->prepare("INSERT INTO registrations (lead_id) VALUES (?)");
        return $stmt->execute([$lead_id]);
    }

    public function updateApproval($lead_id, $role, $status) {
        $field = $role === 'marketing_manager' ? 'marketing_manager_approval' : 'academic_approval';
        $stmt = $this->pdo->prepare("UPDATE registrations SET $field = ? WHERE lead_id = ?");
        return $stmt->execute([$status, $lead_id]);
    }

    public function getPendingRegistrations() {
        $stmt = $this->pdo->prepare("SELECT r.*, l.* FROM registrations r JOIN leads l ON r.lead_id = l.id WHERE r.marketing_manager_approval = 'pending' OR r.academic_approval = 'pending'");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getRegistration($lead_id) {
        $stmt = $this->pdo->prepare("SELECT * FROM registrations WHERE lead_id = ?");
        $stmt->execute([$lead_id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
}