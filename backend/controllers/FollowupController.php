<?php
require_once __DIR__ . '/../config/db_connect.php';
require_once __DIR__ . '/../models/Followup.php';

class FollowupController {
    private $followupModel;

    public function __construct($pdo) {
        $this->followupModel = new Followup($pdo);
    }

    public function addFollowup($lead_id, $date, $comment) {
        return $this->followupModel->createFollowup($lead_id, $date, $comment);
    }

    public function getFollowupsByLead($lead_id) {
        return $this->followupModel->getFollowupsByLead($lead_id);
    }

    public function deleteFollowup($followup_id) {
        return $this->followupModel->deleteFollowup($followup_id);
    }
}