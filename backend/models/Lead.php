<?php
    class Lead {
        private $pdo;

        public function __construct($pdo) {
            $this->pdo = $pdo;
        }

        public function createLead($form_name, $full_name, $email, $phone) {
            if (empty($form_name) || empty($full_name) || empty($email) || empty($phone)) {
                return false;
            }
            $stmt = $this->pdo->prepare("INSERT INTO leads (form_name, full_name, email, phone) VALUES (?, ?, ?, ?)");
            return $stmt->execute([$form_name, $full_name, $email, $phone]);
        }

        public function assignLead($lead_id, $user_id) {
            $stmt = $this->pdo->prepare("UPDATE leads SET assigned_user_id = ?, status = 'assigned' WHERE id = ?");
            return $stmt->execute([$user_id, $lead_id]);
        }

        public function getLeadsByCourse($course_name, $user_id = null) {
            if ($course_name) {
                $query = "SELECT * FROM leads WHERE form_name = ?";
                $params = [$course_name];
                if ($user_id !== null) {
                    $query .= " AND assigned_user_id = ?";
                    $params[] = $user_id;
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
            error_log("getLeadsByCourse($course_name, $user_id) returned: " . print_r($results, true));
            return $results;
        }

        public function getAssignedLeads($user_id = null) {
            if ($user_id !== null) {
                $stmt = $this->pdo->prepare("SELECT * FROM leads WHERE assigned_user_id = ?");
                $stmt->execute([$user_id]);
            } else {
                $stmt = $this->pdo->prepare("SELECT * FROM leads WHERE assigned_user_id IS NOT NULL");
                $stmt->execute();
            }
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        }

        public function getLeadById($lead_id) {
            $stmt = $this->pdo->prepare("SELECT * FROM leads WHERE id = ?");
            $stmt->execute([$lead_id]);
            return $stmt->fetch(PDO::FETCH_ASSOC);
        }

        public function updateStatus($lead_id, $status) {
            $stmt = $this->pdo->prepare("UPDATE leads SET status = ? WHERE id = ?");
            return $stmt->execute([$status, $lead_id]);
        }

        public function updateLeadDetails($lead_id, $permanent_address, $work_experience) {
            $stmt = $this->pdo->prepare("UPDATE leads SET permanent_address = ?, work_experience = ? WHERE id = ?");
            return $stmt->execute([$permanent_address, $work_experience, $lead_id]);
        }
    }