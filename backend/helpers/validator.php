<?php
/**
 * Input Validation Helpers
 */

class Validator {
    private $errors = [];
    private $data = [];

    public function __construct($data) {
        $this->data = $data;
    }

    public function required($field, $label = null) {
        $label = $label ?? $field;
        if (empty($this->data[$field])) {
            $this->errors[$field] = "$label is required";
        }
        return $this;
    }

    public function email($field) {
        if (!empty($this->data[$field]) && !validateEmail($this->data[$field])) {
            $this->errors[$field] = "Invalid email format";
        }
        return $this;
    }

    public function phone($field) {
        if (!empty($this->data[$field]) && !validatePhone($this->data[$field])) {
            $this->errors[$field] = "Invalid phone number";
        }
        return $this;
    }

    public function min($field, $length, $label = null) {
        $label = $label ?? $field;
        if (!empty($this->data[$field]) && strlen($this->data[$field]) < $length) {
            $this->errors[$field] = "$label must be at least $length characters";
        }
        return $this;
    }

    public function max($field, $length, $label = null) {
        $label = $label ?? $field;
        if (!empty($this->data[$field]) && strlen($this->data[$field]) > $length) {
            $this->errors[$field] = "$label must be at most $length characters";
        }
        return $this;
    }

    public function match($field1, $field2, $label = 'Fields') {
        if ($this->data[$field1] !== $this->data[$field2]) {
            $this->errors[$field1] = "$label do not match";
        }
        return $this;
    }

    public function numeric($field, $label = null) {
        $label = $label ?? $field;
        if (!empty($this->data[$field]) && !is_numeric($this->data[$field])) {
            $this->errors[$field] = "$label must be a number";
        }
        return $this;
    }

    public function minValue($field, $value, $label = null) {
        $label = $label ?? $field;
        if (isset($this->data[$field]) && $this->data[$field] < $value) {
            $this->errors[$field] = "$label must be at least $value";
        }
        return $this;
    }

    public function in($field, $values, $label = null) {
        $label = $label ?? $field;
        if (!empty($this->data[$field]) && !in_array($this->data[$field], $values)) {
            $this->errors[$field] = "Invalid $label";
        }
        return $this;
    }

    public function passes() {
        return empty($this->errors);
    }

    public function fails() {
        return !empty($this->errors);
    }

    public function getErrors() {
        return $this->errors;
    }

    public function firstError() {
        return !empty($this->errors) ? array_values($this->errors)[0] : null;
    }
}