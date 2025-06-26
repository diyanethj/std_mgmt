<?php
  class DocumentController {
      private $documentModel;

      public function __construct($pdo) {
          $this->documentModel = new Document($pdo);
      }

      public function uploadDocument($lead_id, $document_type, $file) {
          return $this->documentModel->uploadDocument($lead_id, $document_type, $file); // Line 10
      }

      public function getDocumentsByLead($lead_id) {
          return $this->documentModel->getDocumentsByLead($lead_id);
      }

      public function deleteDocument($document_id, $lead_id) {
          return $this->documentModel->deleteDocument($document_id, $lead_id);
      }
  }