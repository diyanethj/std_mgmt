<?php
class Lead {
    private $pdo;

    public function __construct($pdo) {
        $this->pdo = $pdo;
    }

    public function getPdo() {
        return $this->pdo;
    }

    public function createLead($form_name, $full_name, $email, $phone) {
        if (empty($form_name) || empty($full_name) || empty($email) || empty($phone)) {
            return false;
        }
        $stmt = $this->pdo->prepare("INSERT INTO leads (form_name, full_name, email, phone, status) VALUES (?, ?, ?, ?, 'new')");
        $result = $stmt->execute([$form_name, $full_name, $email, $phone]);
        if (!$result) {
            error_log("Failed to create lead: " . print_r([$form_name, $full_name, $email, $phone], true));
        }
        return $result;
    }

    public function assignLead($lead_id, $user_id) {
        $stmt = $this->pdo->prepare("UPDATE leads SET assigned_user_id = ?, status = 'assigned' WHERE id = ?");
        return $stmt->execute([$user_id, $lead_id]);
    }

    public function getLeadsByCourse($course_name, $user_id = null, $registration_status = null) {
        if ($course_name) {
            $query = "SELECT l.*, u.username, r.status AS registration_status
                      FROM leads l
                      LEFT JOIN users u ON l.assigned_user_id = u.id
                      LEFT JOIN registrations r ON l.id = r.lead_id
                      WHERE l.form_name = ?";
            $params = [$course_name];
            if ($user_id !== null) {
                $query .= " AND l.assigned_user_id = ?";
                $params[] = $user_id;
            }
            if ($registration_status !== null) {
                if ($registration_status === 'N/A') {
                    $query .= " AND r.status IS NULL";
                } else {
                    $query .= " AND r.status = ?";
                    $params[] = $registration_status;
                }
            }
            $stmt = $this->pdo->prepare($query);
            $stmt->execute($params);
        } else {
            $query = "SELECT DISTINCT form_name FROM leads";
            if ($user_id !== null) {
                $query .= " WHERE assigned_user_id = ?";
                $stmt = $this->pdo->prepare($query);
                $stmt->execute([$user_id]);
            } else {
                $stmt = $this->pdo->prepare($query);
                $stmt->execute();
            }
        }
        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
        error_log("getLeadsByCourse($course_name, $user_id, $registration_status) returned: " . print_r($results, true));
        return $results;
    }

    public function getAssignedLeads($user_id = null, $course_name = null) {
        $query = "SELECT l.*, u.username, r.status AS registration_status
                  FROM leads l
                  LEFT JOIN users u ON l.assigned_user_id = u.id
                  LEFT JOIN registrations r ON l.id = r.lead_id
                  WHERE l.assigned_user_id IS NOT NULL
                  AND l.status IN ('new', 'assigned')
                  AND r.status IS NULL";
        $params = [];
        if ($user_id !== null) {
            $query .= " AND l.assigned_user_id = ?";
            $params[] = $user_id;
        }
        if ($course_name !== null) {
            $query .= " AND l.form_name = ?";
            $params[] = $course_name;
        }
        $stmt = $this->pdo->prepare($query);
        $stmt->execute($params);
        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
        error_log("getAssignedLeads($user_id, $course_name) returned: " . print_r($results, true));
        return $results;
    }

    public function getDistinctCourses() {
        $stmt = $this->pdo->prepare("SELECT DISTINCT form_name FROM leads WHERE assigned_user_id IS NOT NULL AND status IN ('new', 'assigned')");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_COLUMN);
    }

    public function getLeadById($lead_id) {
        $stmt = $this->pdo->prepare("SELECT l.*, u.username, r.status AS registration_status
                                     FROM leads l
                                     LEFT JOIN users u ON l.assigned_user_id = u.id
                                     LEFT JOIN registrations r ON l.id = r.lead_id
                                     WHERE l.id = ?");
        $stmt->execute([$lead_id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function updateStatus($lead_id, $status) {
        $stmt = $this->pdo->prepare("UPDATE leads SET status = ? WHERE id = ?");
        return $stmt->execute([$status, $lead_id]);
    }

    public function updateLeadDetails($lead_id, $form_name, $title, $full_name, $nic_number, $passport_number, $date_of_birth, $gender, $nationality, $marital_status, $permanent_address, $current_address, $mobile_no, $email_address, $office_address, $office_email, $parent_guardian_name, $parent_contact_number, $parent_address, $company_institution, $postcode) {
        $stmt = $this->pdo->prepare("UPDATE leads SET form_name = ?, title = ?, full_name = ?, nic_number = ?, passport_number = ?, date_of_birth = ?, gender = ?, nationality = ?, marital_status = ?, permanent_address = ?, current_address = ?, mobile_no = ?, email_address = ?, office_address = ?, office_email = ?, parent_guardian_name = ?, parent_contact_number = ?, parent_address = ?, company_institution = ?, postcode = ? WHERE id = ?");
        $result = $stmt->execute([$form_name ?: null, $title ?: null, $full_name ?: null, $nic_number ?: null, $passport_number ?: null, $date_of_birth ?: null, $gender ?: null, $nationality ?: null, $marital_status ?: null, $permanent_address ?: null, $current_address ?: null, $mobile_no ?: null, $email_address ?: null, $office_address ?: null, $office_email ?: null, $parent_guardian_name ?: null, $parent_contact_number ?: null, $parent_address ?: null, $company_institution ?: null, $postcode ?: null, $lead_id]);
        if (!$result) {
            error_log("Failed to update lead details for lead_id $lead_id: " . print_r([$form_name, $title, $full_name, $nic_number, $passport_number, $date_of_birth, $gender, $nationality, $marital_status, $permanent_address, $current_address, $mobile_no, $email_address, $office_address, $office_email, $parent_guardian_name, $parent_contact_number, $parent_address, $company_institution, $postcode], true));
        }
        return $result;
    }

    public function getTotalLeads()
    {
        $stmt = $this->pdo->query('SELECT COUNT(*) as total FROM leads');
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return (int) $result['total'];
    }

    public function getAssignedLeadsCount()
    {
        $stmt = $this->pdo->query('SELECT COUNT(*) as total FROM leads where status="assigned" OR status="registered" OR status="declined" OR status= "pending_registration"');
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return (int) $result['total'];
    }

    public function getNewLeads()
    {
        $stmt = $this->pdo->query('SELECT COUNT(*) as total FROM leads where status="new"');
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return (int) $result['total'];
    }

    public function getPendingRegistrationsCount()
    {
        $stmt = $this->pdo->query('SELECT COUNT(*) as total FROM registrations where status= "pending"');
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return (int) $result['total'];
    }

    public function getRegisteredLeadsCount()
    {
        $stmt = $this->pdo->query('SELECT COUNT(*) as total FROM registrations where status= "completed"');
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return (int) $result['total'];
    }

    public function getDeclinedLeadsCount()
    {
        $stmt = $this->pdo->query('SELECT COUNT(*) as total FROM registrations where status= "declined"');
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return (int) $result['total'];
    }

    public function getAssignedUserLeadsCount($user_id) {
        $stmt = $this->pdo->prepare("SELECT COUNT(*) FROM leads WHERE assigned_user_id = ?");
        $stmt->execute([$user_id]);
        return $stmt->fetchColumn();
    }

    public function getRegisteredUserLeadsCount($user_id) {
        $stmt = $this->pdo->prepare('SELECT COUNT(*) FROM leads WHERE status = "registered" AND assigned_user_id = ? ');
        $stmt->execute([$user_id]);
        return $stmt->fetchColumn();
    }

    public function getPendingUserLeadsCount($user_id) {
        $stmt = $this->pdo->prepare('SELECT COUNT(*) FROM leads WHERE status = "pending_registration" AND assigned_user_id = ? ');
        $stmt->execute([$user_id]);
        return $stmt->fetchColumn();
    }

    public function getDeclinedUserLeadsCount($user_id) {
        $stmt = $this->pdo->prepare('SELECT COUNT(*) FROM leads WHERE status = "declined" AND assigned_user_id = ? ');
        $stmt->execute([$user_id]);
        return $stmt->fetchColumn();
    }
}