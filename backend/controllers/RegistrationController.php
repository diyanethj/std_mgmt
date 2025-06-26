<?php
require_once __DIR__ . '/../config/db_connect.php';
require_once __DIR__ . '/../models/Registration.php';
require_once __DIR__ . '/../models/Lead.php';

class RegistrationController {
    private $registrationModel;
    private $leadModel;

    public function __construct($pdo) {
        $this->registrationModel = new Registration($pdo);
        $this->leadModel = new Lead($pdo);
    }

    public function approveRegistration($lead_id, $role, $status) {
        $result = $this->registrationModel->updateApproval($lead_id, $role, $status);
        
        $registration = $this->registrationModel->getRegistration($lead_id);
        if ($registration['marketing_manager_approval'] === 'approved' && 
            $registration['academic_approval'] === 'approved') {
            $this->leadModel->updateStatus($lead_id, 'registered');
        } elseif ($registration['marketing_manager_approval'] === 'declined' || 
                 $registration['academic_approval'] === 'declined') {
            $this->leadModel->updateStatus($lead_id, 'declined');
        }
        return $result;
    }

    public function getPendingRegistrations() {
        return $this->registrationModel->getPendingRegistrations();
    }
}