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

    public function uploadLeads($file) {
        if ($file['error'] !== UPLOAD_ERR_OK) {
            error_log("File upload error: " . $file['error']);
            header('Location: /std_mgmt/views/admin/upload_leads.php?error=File upload failed');
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
                    header('Location: /std_mgmt/views/admin/upload_leads.php?error=Invalid CSV format at row ' . $row);
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
            header('Location: /std_mgmt/views/admin/upload_leads.php?success=Uploaded ' . $success_count . ' leads successfully');
            exit;
        } else {
            error_log("Unable to open CSV file: " . $file['tmp_name']);
            header('Location: /std_mgmt/views/admin/upload_leads.php?error=Unable to open CSV file');
            exit;
        }
    }

    public function assignLead($lead_id, $user_id) {
        return $this->leadModel->assignLead($lead_id, $user_id);
    }

    public function getLeadsByCourse($course_name) {
        return $this->leadModel->getLeadsByCourse($course_name); // Use leadModel
    }

    public function getAssignedLeads($user_id = null) {
        return $this->leadModel->getAssignedLeads($user_id);
    }

    public function sendToRegistration($lead_id) {
        return $this->leadModel->updateStatus($lead_id, 'pending_registration');
    }
}