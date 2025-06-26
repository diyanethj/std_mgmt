<?php
  require_once __DIR__ . '/../models/Registration.php';

  class RegistrationController {
      private $registrationModel;

      public function __construct($pdo) {
          $this->registrationModel = new Registration($pdo);
      }

      public function createRegistration($lead_id) {
          return $this->registrationModel->createRegistration($lead_id);
      }

      public function getPendingRegistrations($user_id = null, $role = null) {
          return $this->registrationModel->getPendingRegistrations($user_id, $role);
      }

      public function getRegisteredLeads($user_id = null, $role = null) {
          return $this->registrationModel->getRegisteredLeads($user_id, $role);
      }

      public function approveRegistration($lead_id, $role, $status) {
          return $this->registrationModel->approveRegistration($lead_id, $role, $status);
      }
  }