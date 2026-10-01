<?php
/**
 * File Upload Helper
 */

class FileUpload {
    private $allowedTypes = [];
    private $maxSize = 5242880; // 5MB
    private $uploadPath = '';
    private $errors = [];

    public function __construct($uploadPath = UPLOAD_PATH) {
        $this->uploadPath = rtrim($uploadPath, '/') . '/';
        $this->allowedTypes = ALLOWED_IMAGE_TYPES;
        $this->maxSize = MAX_IMAGE_SIZE;
    }

    public function setAllowedTypes($types) {
        $this->allowedTypes = $types;
        return $this;
    }

    public function setMaxSize($bytes) {
        $this->maxSize = $bytes;
        return $this;
    }

    public function upload($file, $prefix = 'img') {
        // Check errors
        if ($file['error'] !== UPLOAD_ERR_OK) {
            $this->errors[] = "Upload error: " . $this->getUploadError($file['error']);
            return false;
        }

        // Check size
        if ($file['size'] > $this->maxSize) {
            $this->errors[] = "File too large (max " . ($this->maxSize / 1024 / 1024) . "MB)";
            return false;
        }

        // Check type
        $mime = mime_content_type($file['tmp_name']);
        if (!in_array($mime, $this->allowedTypes)) {
            $this->errors[] = "Invalid file type: $mime";
            return false;
        }

        // Create directory
        if (!is_dir($this->uploadPath)) {
            mkdir($this->uploadPath, 0755, true);
        }

        // Generate filename
        $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
        $filename = $prefix . '_' . time() . '_' . bin2hex(random_bytes(4)) . '.' . strtolower($ext);
        $destination = $this->uploadPath . $filename;

        // Move file
        if (!move_uploaded_file($file['tmp_name'], $destination)) {
            $this->errors[] = "Failed to save file";
            return false;
        }

        return $filename;
    }

    public function uploadMultiple($files, $prefix = 'img') {
        $uploaded = [];
        $count = count($files['name']);

        for ($i = 0; $i < $count; $i++) {
            $file = [
                'name'     => $files['name'][$i],
                'type'     => $files['type'][$i],
                'tmp_name' => $files['tmp_name'][$i],
                'error'    => $files['error'][$i],
                'size'     => $files['size'][$i]
            ];

            $result = $this->upload($file, $prefix . "_$i");
            if ($result) {
                $uploaded[] = $result;
            }
        }

        return $uploaded;
    }

    private function getUploadError($code) {
        return match($code) {
            UPLOAD_ERR_INI_SIZE   => 'File exceeds server limit',
            UPLOAD_ERR_FORM_SIZE  => 'File exceeds form limit',
            UPLOAD_ERR_PARTIAL    => 'File only partially uploaded',
            UPLOAD_ERR_NO_FILE    => 'No file uploaded',
            UPLOAD_ERR_NO_TMP_DIR => 'No temp folder',
            UPLOAD_ERR_CANT_WRITE => 'Cannot write to disk',
            UPLOAD_ERR_EXTENSION  => 'Upload stopped by extension',
            default               => 'Unknown error'
        };
    }

    public function getErrors() {
        return $this->errors;
    }

    public function firstError() {
        return $this->errors[0] ?? null;
    }
}