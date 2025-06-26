<?php
  class Registration {
      private $pdo;

      public function __construct($pdo) {
          $this->pdo = $pdo;
      }

      public function createRegistration($lead_id) {
          $stmt = $this->pdo->prepare("INSERT INTO registrations (lead_id, marketing_manager_approval, academic_user_approval, status) VALUES (?, 'pending', 'pending', 'pending')");
          return $stmt->execute([$lead_id]);
      }

      public function getPendingRegistrations($user_id = null, $role = null) {
          $query = "SELECT r.*, l.form_name, l.full_name, l.email, l.phone, l.permanent_address, l.work_experience, l.status AS lead_status, l.created_at AS lead_created_at
                    FROM registrations r
                    JOIN leads l ON r.lead_id = l.id
                    WHERE r.status = 'pending'";
          $params = [];
          if ($role === 'marketing_user' && $user_id !== null) {
              $query .= " AND l.assigned_user_id = ?";
              $params[] = $user_id;
          }
          $stmt = $this->pdo->prepare($query);
          $stmt->execute($params);
          return $stmt->fetchAll(PDO::FETCH_ASSOC);
      }

      public function getRegisteredLeads($user_id = null, $role = null) {
          $query = "SELECT r.*, l.form_name, l.full_name, l.email, l.phone, l.permanent_address, l.work_experience, l.status AS lead_status, l.created_at AS lead_created_at
                    FROM registrations r
                    JOIN leads l ON r.lead_id = l.id
                    WHERE r.status = 'completed'";
          $params = [];
          if ($role === 'marketing_user' && $user_id !== null) {
              $query .= " AND l.assigned_user_id = ?";
              $params[] = $user_id;
          }
          $stmt = $this->pdo->prepare($query);
          $stmt->execute($params);
          return $stmt->fetchAll(PDO::FETCH_ASSOC);
      }

      public function approveRegistration($lead_id, $role, $status) {
          $column = $role === 'marketing_manager' ? 'marketing_manager_approval' : 'academic_user_approval';
          $stmt = $this->pdo->prepare("UPDATE registrations SET $column = ? WHERE lead_id = ?");
          $result = $stmt->execute([$status, $lead_id]);
          if ($result) {
              // Check if both approvals are accepted
              $stmt = $this->pdo->prepare("SELECT marketing_manager_approval, academic_user_approval FROM registrations WHERE lead_id = ?");
              $stmt->execute([$lead_id]);
              $approvals = $stmt->fetch(PDO::FETCH_ASSOC);
              if ($approvals['marketing_manager_approval'] === 'accepted' && $approvals['academic_user_approval'] === 'accepted') {
                  $stmt = $this->pdo->prepare("UPDATE registrations SET status = 'completed' WHERE lead_id = ?");
                  $stmt->execute([$lead_id]);
                  $stmt = $this->pdo->prepare("UPDATE leads SET status = 'registered' WHERE id = ?");
                  $stmt->execute([$lead_id]);
              } elseif ($approvals['marketing_manager_approval'] === 'declined' || $approvals['academic_user_approval'] === 'declined') {
                  $stmt = $this->pdo->prepare("UPDATE registrations SET status = 'declined' WHERE lead_id = ?");
                  $stmt->execute([$lead_id]);
                  $stmt = $this->pdo->prepare("UPDATE leads SET status = 'declined' WHERE id = ?");
                  $stmt->execute([$lead_id]);
              }
          }
          return $result;
      }
  }