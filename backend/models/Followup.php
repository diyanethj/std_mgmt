<?php
class Followup {
    private $pdo;

    public function __construct($pdo) {
        $this->pdo = $pdo;
    }

    public function createFollowup($lead_id, $date, $comment) {
        $stmt = $this->pdo->prepare("INSERT INTO followups (lead_id, followup_date, comment) VALUES (?, ?, ?)");
        return $stmt->execute([$lead_id, $date, $comment]);
    }

    public function getFollowupsByLead($lead_id) {
        $stmt = $this->pdo->prepare("SELECT * FROM followups WHERE lead_id = ? ORDER BY followup_date DESC");
        $stmt->execute([$lead_id]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function deleteFollowup($followup_id) {
        $stmt = $this->pdo->prepare("DELETE FROM followups WHERE id = ?");
        return $stmt->execute([$followup_id]);
    }
}