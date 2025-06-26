<?php
require_once __DIR__ . '/../models/Registration.php';
require_once __DIR__ . '/../models/Lead.php';

class RegistrationController {
    private $registrationModel;
    private $leadModel;

    public function __construct($pdo) {
        $this->registrationModel = new Registration($pdo);
        $this->leadModel = new Lead($pdo);
    }

    public function createRegistration($lead_id) {
        if ($this->registrationModel->createRegistration($lead_id)) {
            return $this->leadModel->updateStatus($lead_id, 'pending_registration');
        }
        return false;
    }

    public function getPendingRegistrations($user_id = null, $course_name = null) {
        return $this->registrationModel->getPendingRegistrations($user_id, $course_name);
    }

    public function approveRegistration($lead_id, $role) {
        if ($role === 'marketing_manager' || $role === 'academic_user') {
            $field = $role === 'marketing_manager' ? 'marketing_manager_approval' : 'academic_user_approval';
            if ($this->registrationModel->updateApproval($lead_id, $field, 'accepted')) {
                return $this->updateRegistrationStatus($lead_id);
            }
        }
        return false;
    }

    public function declineRegistration($lead_id, $role) {
        if ($role === 'marketing_manager' || $role === 'academic_user') {
            $field = $role === 'marketing_manager' ? 'marketing_manager_approval' : 'academic_user_approval';
            if ($this->registrationModel->updateApproval($lead_id, $field, 'declined')) {
                $this->registrationModel->updateStatus($lead_id, 'declined');
                $this->leadModel->updateStatus($lead_id, 'declined');
                return true;
            }
        }
        return false;
    }

    private function updateRegistrationStatus($lead_id) {
        $registration = $this->registrationModel->getRegistrationByLeadId($lead_id);
        if ($registration && $registration['marketing_manager_approval'] === 'accepted' && $registration['academic_user_approval'] === 'accepted') {
            $this->registrationModel->updateStatus($lead_id, 'completed');
            $this->leadModel->updateStatus($lead_id, 'registered');
            return true;
        } elseif ($registration && ($registration['marketing_manager_approval'] === 'declined' || $registration['academic_user_approval'] === 'declined')) {
            $this->registrationModel->updateStatus($lead_id, 'declined');
            $this->leadModel->updateStatus($lead_id, 'declined');
            return true;
        }
        return false;
    }

    public function getRegistrationByLeadId($lead_id) {
        return $this->registrationModel->getRegistrationByLeadId($lead_id);
    }
}