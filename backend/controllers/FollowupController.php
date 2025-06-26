<?php
  class FollowupController {
      private $followupModel;

      public function __construct($pdo) {
          $this->followupModel = new Followup($pdo);
      }

      public function addFollowup($lead_id, $number, $followup_date, $comment) {
          return $this->followupModel->addFollowup($lead_id, $number, $followup_date, $comment);
      }

      public function getFollowupsByLead($lead_id) {
          return $this->followupModel->getFollowupsByLead($lead_id);
      }

      public function updateFollowup($followup_id, $lead_id, $number, $followup_date, $comment) {
          return $this->followupModel->updateFollowup($followup_id, $lead_id, $number, $followup_date, $comment);
      }

      public function deleteFollowup($followup_id, $lead_id) {
          return $this->followupModel->deleteFollowup($followup_id, $lead_id);
      }
  }