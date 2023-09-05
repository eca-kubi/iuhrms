<?php

abstract class BaseModelValidator
{
    protected Model $model;
    protected array $errors = [];

    public function __construct(Model $model)
    {
        $this->model = $model;
    }

    abstract public function validate(bool $isRequired = true): bool;
    abstract public function validateId(bool $isRequired = true): self;
    protected function validateModelId($id, string $field, callable $existsCallback, bool $isRequired = true): self
    {
        if (!$isRequired && is_null($id)) {
            return $this;
        }

        if (empty($id)) {
            $this->addError($field, ValidationErrorTypeNames::REQUIRED, "$field is required.");
            return $this;
        }

        if (!is_numeric($id)) {
            $this->addError($field, ValidationErrorTypeNames::INVALID_TYPE, "$field must be a number");
            return $this;
        }

        try {
            if (!$existsCallback($id)) {
                $this->addError($field, ValidationErrorTypeNames::DOES_NOT_EXIST,"$field does not exist");
            }
        } catch (Exception $e) {
            $this->addError($field,  ValidationErrorTypeNames::DOES_NOT_EXIST,"$field does not exist");
            Helpers::log_error($e->getMessage());
        }

        return $this;
    }

    protected function addError(string $field, string $type, string $message): void
    {
        if (!isset($this->errors[$field])) {
            $this->errors[$field] = [];
        }

        if (!isset($this->errors[$field][$type])) {
            $this->errors[$field][$type] = $message;
        }
    }


    public function getErrors(string $field = null): array
    {
        if ($field) {
            return $this->errors[$field] ?? [];
        }

        return $this->errors;
    }
}
