<?php
require_once __DIR__ . '/../config/db_connect.php';
require_once __DIR__ . '/../models/Document.php';

class DocumentController {
    private $documentModel;

    public function __construct($pdo) {
        $this->documentModel = new Document($pdo);
    }

    public function uploadDocument($lead_id, $document_type, $file) {
        $target_dir = __DIR__ . "/../../uploads/documents/";
        $target_file = $target_dir . uniqid() . '_' . basename($file["name"]);
        if (move_uploaded_file($file["tmp_name"], $target_file)) {
            return $this->documentModel->createDocument($lead_id, $document_type, $target_file);
        }
        return false;
    }

    public function getDocumentsByLead($lead_id) {
        return $this->documentModel->getDocumentsByLead($lead_id);
    }

    public function deleteDocument($document_id) {
        return $this->documentModel->deleteDocument($document_id);
    }
}