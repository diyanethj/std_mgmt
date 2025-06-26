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

    public function getRegisteredLeads($user_id = null, $course_name = null) {
        return $this->registrationModel->getRegisteredLeads($user_id, $course_name);
    }

    public function approveRegistration($lead_id, $role) {
        if ($role !== 'marketing_manager' && $role !== 'academic_user') {
            error_log("Invalid role for approval: $role");
            return false;
        }
        $field = $role === 'marketing_manager' ? 'marketing_manager_approval' : 'academic_user_approval';
        $result = $this->registrationModel->updateApproval($lead_id, $field, 'accepted');
        if ($result) {
            return $this->updateRegistrationStatus($lead_id);
        }
        error_log("Failed to update approval for lead_id: $lead_id, role: $role");
        return false;
    }

    public function declineRegistration($lead_id, $role) {
        if ($role !== 'marketing_manager' && $role !== 'academic_user') {
            error_log("Invalid role for decline: $role");
            return false;
        }
        $field = $role === 'marketing_manager' ? 'marketing_manager_approval' : 'academic_user_approval';
        $other_field = $role === 'marketing_manager' ? 'academic_user_approval' : 'marketing_manager_approval';
        // Set both approvals to declined
        $result1 = $this->registrationModel->updateApproval($lead_id, $field, 'declined');
        $result2 = $this->registrationModel->updateApproval($lead_id, $other_field, 'declined');
        if ($result1 && $result2) {
            $this->registrationModel->updateStatus($lead_id, 'declined');
            $this->leadModel->updateStatus($lead_id, 'declined');
            return true;
        }
        error_log("Failed to decline registration for lead_id: $lead_id, role: $role");
        return false;
    }

    private function updateRegistrationStatus($lead_id) {
        $registration = $this->registrationModel->getRegistrationByLeadId($lead_id);
        if (!$registration) {
            error_log("No registration found for lead_id: $lead_id");
            return false;
        }
        if ($registration['marketing_manager_approval'] === 'accepted' && $registration['academic_user_approval'] === 'accepted') {
            $this->registrationModel->updateStatus($lead_id, 'completed');
            $this->leadModel->updateStatus($lead_id, 'registered');
            return true;
        } elseif ($registration['marketing_manager_approval'] === 'declined' || $registration['academic_user_approval'] === 'declined') {
            $this->registrationModel->updateStatus($lead_id, 'declined');
            $this->leadModel->updateStatus($lead_id, 'declined');
            return true;
        }
        return true; // No status change needed if approvals are still pending
    }

    public function getRegistrationByLeadId($lead_id) {
        return $this->registrationModel->getRegistrationByLeadId($lead_id);
    }
}