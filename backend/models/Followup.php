<?php
  class Followup {
      private $pdo;

      public function __construct($pdo) {
          $this->pdo = $pdo;
      }

      public function addFollowup($lead_id, $number, $followup_date, $comment) {
          $stmt = $this->pdo->prepare("INSERT INTO followups (lead_id, number, followup_date, comment) VALUES (?, ?, ?, ?)");
          return $stmt->execute([$lead_id, $number, $followup_date, $comment]);
      }

      public function getFollowupsByLead($lead_id) {
          $stmt = $this->pdo->prepare("SELECT * FROM followups WHERE lead_id = ? ORDER BY number ASC");
          $stmt->execute([$lead_id]);
          return $stmt->fetchAll(PDO::FETCH_ASSOC);
      }

      public function updateFollowup($followup_id, $lead_id, $number, $followup_date, $comment) {
          $stmt = $this->pdo->prepare("UPDATE followups SET number = ?, followup_date = ?, comment = ? WHERE id = ? AND lead_id = ?");
          return $stmt->execute([$number, $followup_date, $comment, $followup_id, $lead_id]);
      }

      public function deleteFollowup($followup_id, $lead_id) {
          $stmt = $this->pdo->prepare("DELETE FROM followups WHERE id = ? AND lead_id = ?");
          return $stmt->execute([$followup_id, $lead_id]);
      }
  }