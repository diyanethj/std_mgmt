<?php
class Document {
    private $pdo;

    public function __construct($pdo) {
        $this->pdo = $pdo;
    }

    public function createDocument($lead_id, $document_type, $file_path) {
        $stmt = $this->pdo->prepare("INSERT INTO documents (lead_id, document_type, file_path) VALUES (?, ?, ?)");
        return $stmt->execute([$lead_id, $document_type, $file_path]);
    }

    public function getDocumentsByLead($lead_id) {
        $stmt = $this->pdo->prepare("SELECT * FROM documents WHERE lead_id = ?");
        $stmt->execute([$lead_id]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function deleteDocument($document_id) {
        $stmt = $this->pdo->prepare("DELETE FROM documents WHERE id = ?");
        return $stmt->execute([$document_id]);
    }
}