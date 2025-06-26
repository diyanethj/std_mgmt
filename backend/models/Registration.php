<?php
class Registration {
    private $pdo;

    public function __construct($pdo) {
        $this->pdo = $pdo;
    }

    public function createRegistration($lead_id) {
        $stmt = $this->pdo->prepare("INSERT INTO registrations (lead_id, status, marketing_manager_approval, academic_user_approval) VALUES (?, 'pending', 'pending', 'pending')");
        return $stmt->execute([$lead_id]);
    }

    public function getPendingRegistrations($user_id = null, $course_name = null) {
        $query = "SELECT r.*, l.form_name, l.full_name, l.email, l.phone, l.assigned_user_id, u.username
                  FROM registrations r
                  JOIN leads l ON r.lead_id = l.id
                  LEFT JOIN users u ON l.assigned_user_id = u.id
                  WHERE r.status = 'pending'";
        $params = [];
        if ($course_name) {
            $query .= " AND l.form_name = ?";
            $params[] = $course_name;
        }
        if ($user_id) {
            $query .= " AND l.assigned_user_id = ?";
            $params[] = $user_id;
        }
        $stmt = $this->pdo->prepare($query);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function updateApproval($lead_id, $field, $status) {
        $stmt = $this->pdo->prepare("UPDATE registrations SET $field = ? WHERE lead_id = ?");
        return $stmt->execute([$status, $lead_id]);
    }

    public function updateStatus($lead_id, $status) {
        $stmt = $this->pdo->prepare("UPDATE registrations SET status = ? WHERE lead_id = ?");
        return $stmt->execute([$status, $lead_id]);
    }

    public function getRegistrationByLeadId($lead_id) {
        $stmt = $this->pdo->prepare("SELECT * FROM registrations WHERE lead_id = ?");
        $stmt->execute([$lead_id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
}