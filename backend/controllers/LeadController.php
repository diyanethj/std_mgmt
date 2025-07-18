<?php
require_once __DIR__ . '/../config/db_connect.php';
require_once __DIR__ . '/../models/Lead.php';
require_once __DIR__ . '/../models/Document.php';
require_once __DIR__ . '/../models/Followup.php';

class LeadController {
    private $leadModel;
    private $documentModel;
    private $followupModel;

    public function __construct($pdo) {
        $this->leadModel = new Lead($pdo);
        $this->documentModel = new Document($pdo);
        $this->followupModel = new Followup($pdo);
    }

    public function uploadLeads($file, $user) {
        if ($file['error'] !== UPLOAD_ERR_OK) {
            error_log("File upload error: " . $file['error']);
            header('Location: /std_mgmt/views/' . ($user['role'] === 'marketing_manager' ? 'marketing_manager' : 'admin') . '/upload_leads.php?error=File upload failed');
            exit;
        }
        if (($handle = fopen($file['tmp_name'], "r")) !== FALSE) {
            fgetcsv($handle); // Skip header
            $row = 1;
            $success_count = 0;
            while (($data = fgetcsv($handle)) !== FALSE) {
                $row++;
                if (count($data) < 4) {
                    error_log("Invalid CSV format at row $row: " . print_r($data, true));
                    header('Location: /std_mgmt/views/' . ($user['role'] === 'marketing_manager' ? 'marketing_manager' : 'admin') . '/upload_leads.php?error=Invalid CSV format at row ' . $row);
                    exit;
                }
                $form_name = trim($data[0] ?? '');
                $full_name = trim($data[1] ?? '');
                $email = trim($data[2] ?? '');
                $phone = trim($data[3] ?? '');
                if (empty($form_name) || empty($full_name) || empty($email) || empty($phone)) {
                    error_log("Empty fields at row $row: " . print_r($data, true));
                    continue;
                }
                if ($this->leadModel->createLead($form_name, $full_name, $email, $phone)) {
                    $success_count++;
                } else {
                    error_log("Failed to insert lead at row $row: " . print_r($data, true));
                }
            }
            fclose($handle);
            header('Location: /std_mgmt/views/' . ($user['role'] === 'marketing_manager' ? 'marketing_manager' : 'admin') . '/upload_leads.php?success=Uploaded ' . $success_count . ' leads successfully');
            exit;
        } else {
            error_log("Unable to open CSV file: " . $file['tmp_name']);
            header('Location: /std_mgmt/views/' . ($user['role'] === 'marketing_manager' ? 'marketing_manager' : 'admin') . '/upload_leads.php?error=Unable to open CSV file');
            exit;
        }
    }

    public function assignLead($lead_id, $user_id) {
        return $this->leadModel->assignLead($lead_id, $user_id);
    }

    public function getLeadsByCourse($course_name, $user_id = null, $registration_status = null) {
        return $this->leadModel->getLeadsByCourse($course_name, $user_id, $registration_status);
    }

    public function getAssignedLeads($user_id = null, $course_name = null) {
        return $this->leadModel->getAssignedLeads($user_id, $course_name);
    }

    public function getDistinctCourses() {
        return $this->leadModel->getDistinctCourses();
    }

    public function getLeadById($lead_id) {
        return $this->leadModel->getLeadById($lead_id);
    }

    public function sendToRegistration($lead_id) {
        return $this->leadModel->updateStatus($lead_id, 'pending_registration');
    }

    public function updateLeadDetails($lead_id, $form_name, $title, $full_name, $nic_number, $passport_number, $date_of_birth, $gender, $nationality, $marital_status, $permanent_address, $current_address, $mobile_no, $email_address, $office_address, $office_email, $parent_guardian_name, $parent_contact_number, $parent_address, $company_institution, $postcode) {
        return $this->leadModel->updateLeadDetails($lead_id, $form_name, $title, $full_name, $nic_number, $passport_number, $date_of_birth, $gender, $nationality, $marital_status, $permanent_address, $current_address, $mobile_no, $email_address, $office_address, $office_email, $parent_guardian_name, $parent_contact_number, $parent_address, $company_institution, $postcode);
    }

    public function getTotalLeads()
    {
        return $this->leadModel->getTotalLeads();
    }

    public function getNewLeads()
    {
        return $this->leadModel->getNewLeads();
    }

    public function getPendingRegistrationsCount() 
    {
        return $this->leadModel->getPendingRegistrationsCount();
    }

    public function getAssignedLeadsCount()
    {
        return $this->leadModel->getAssignedLeadsCount();
    }

    public function getRegisteredLeadsCount()
    {
        return $this->leadModel->getRegisteredLeadsCount();
    }

    public function getDeclinedLeadsCount()
    {
        return $this->leadModel->getDeclinedLeadsCount();
    }

    public function getAssignedUserLeadsCount($user_id) {
        return $this->leadModel->getAssignedUserLeadsCount($user_id);
    }

    public function getRegisteredUserLeadsCount($user_id) {
        return $this->leadModel->getRegisteredUserLeadsCount($user_id);
    }

    public function getPendingUserLeadsCount($user_id) {
        return $this->leadModel->getPendingUserLeadsCount($user_id);
    }

    public function getDeclinedUserLeadsCount($user_id) {
        return $this->leadModel->getDeclinedUserLeadsCount($user_id);
    }
}