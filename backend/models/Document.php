<?php
  class Document {
      private $pdo;

      public function __construct($pdo) {
          $this->pdo = $pdo;
      }

      public function uploadDocument($lead_id, $document_type, $file) {
          if ($file['error'] !== UPLOAD_ERR_OK) {
              error_log("Document upload error: " . $file['error']);
              return false;
          }
          $upload_dir = 'C:/wamp64/www/std_mgmt/uploads/documents/';
          if (!is_dir($upload_dir)) {
              mkdir($upload_dir, 0777, true);
          }
          $file_name = uniqid() . '_' . basename($file['name']);
          $file_path = $upload_dir . $file_name;
          if (move_uploaded_file($file['tmp_name'], $file_path)) {
              $stmt = $this->pdo->prepare("INSERT INTO documents (lead_id, document_type, file_path) VALUES (?, ?, ?)");
              return $stmt->execute([$lead_id, $document_type, $file_path]);
          }
          return false;
      }

      public function getDocumentsByLead($lead_id) {
          $stmt = $this->pdo->prepare("SELECT * FROM documents WHERE lead_id = ?");
          $stmt->execute([$lead_id]);
          return $stmt->fetchAll(PDO::FETCH_ASSOC);
      }

      public function deleteDocument($document_id, $lead_id) {
          $stmt = $this->pdo->prepare("SELECT file_path FROM documents WHERE id = ? AND lead_id = ?");
          $stmt->execute([$document_id, $lead_id]);
          $document = $stmt->fetch(PDO::FETCH_ASSOC);
          if ($document && file_exists($document['file_path'])) {
              unlink($document['file_path']);
          }
          $stmt = $this->pdo->prepare("DELETE FROM documents WHERE id = ? AND lead_id = ?");
          return $stmt->execute([$document_id, $lead_id]);
      }
  }