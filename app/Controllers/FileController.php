<?php
// DO NOT ALTER WITHOUT APPROVAL — Security Module 3
// Last modified: 2026-05-04
// Part of: SPED LMS — Encrypted File Controller

require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../Helpers/FileEncryptionHelper.php';

class FileController {
    /**
     * Serve encrypted file (decrypt and output)
     */
    public function serve($filePath) {
        // Check authentication
        if (!isset($_SESSION['user_id'])) {
            http_response_code(401);
            echo "Unauthorized";
            exit;
        }

        // Decode file path
        $filePath = base64_decode($filePath);
        
        if (empty($filePath)) {
            http_response_code(400);
            echo "Invalid file path";
            exit;
        }

        // Check if file is encrypted
        if (!FileEncryptionHelper::isEncrypted($filePath)) {
            // Serve unencrypted file directly
            $fullPath = __DIR__ . '/../../public/' . $filePath;
            
            if (!file_exists($fullPath)) {
                http_response_code(404);
                echo "File not found";
                exit;
            }

            $mimeType = mime_content_type($fullPath);
            $filename = basename($filePath);

            header('Content-Type: ' . $mimeType);
            header('Content-Length: ' . filesize($fullPath));
            header('Content-Disposition: inline; filename="' . $filename . '"');
            
            readfile($fullPath);
            exit;
        }

        // Get original filename from database
        $originalName = $this->getOriginalFilename($filePath);
        
        // Serve decrypted file
        FileEncryptionHelper::serveDecryptedFile($filePath, $originalName, true);
    }

    /**
     * Download encrypted file
     */
    public function download($filePath) {
        // Check authentication
        if (!isset($_SESSION['user_id'])) {
            http_response_code(401);
            echo "Unauthorized";
            exit;
        }

        // Decode file path
        $filePath = base64_decode($filePath);
        
        if (empty($filePath)) {
            http_response_code(400);
            echo "Invalid file path";
            exit;
        }

        // Get original filename
        $originalName = $this->getOriginalFilename($filePath);
        
        // Serve decrypted file as download
        FileEncryptionHelper::serveDecryptedFile($filePath, $originalName, false);
    }

    /**
     * Get thumbnail for encrypted image
     */
    public function thumbnail($filePath) {
        // Check authentication
        if (!isset($_SESSION['user_id'])) {
            http_response_code(401);
            echo "Unauthorized";
            exit;
        }

        // Decode file path
        $filePath = base64_decode($filePath);
        
        if (empty($filePath)) {
            http_response_code(400);
            echo "Invalid file path";
            exit;
        }

        // Generate thumbnail
        $thumbnail = FileEncryptionHelper::getThumbnail($filePath, 200, 200);
        
        if ($thumbnail === false) {
            http_response_code(404);
            echo "Thumbnail generation failed";
            exit;
        }

        // Output thumbnail
        header('Content-Type: image/jpeg');
        echo base64_decode(str_replace('data:image/jpeg;base64,', '', $thumbnail));
        exit;
    }

    /**
     * Get original filename from database
     */
    private function getOriginalFilename($filePath) {
        try {
            $db = Database::getInstance()->getConnection();
            
            // Try enrollment_documents table
            $stmt = $db->prepare("
                SELECT document_type FROM enrollment_documents 
                WHERE file_path = :file_path 
                LIMIT 1
            ");
            $stmt->execute(['file_path' => $filePath]);
            $doc = $stmt->fetch();
            
            if ($doc) {
                return ucwords(str_replace('_', ' ', $doc['document_type'])) . '.' . pathinfo($filePath, PATHINFO_EXTENSION);
            }

            // Try role_documents table
            $stmt = $db->prepare("
                SELECT file_type FROM role_documents 
                WHERE file_path = :file_path 
                LIMIT 1
            ");
            $stmt->execute(['file_path' => $filePath]);
            $doc = $stmt->fetch();
            
            if ($doc) {
                return $doc['file_type'] ?? 'document.' . pathinfo($filePath, PATHINFO_EXTENSION);
            }

            // Fallback to filename
            return basename($filePath);

        } catch (Exception $e) {
            error_log('Failed to get original filename: ' . $e->getMessage());
            return basename($filePath);
        }
    }
}
