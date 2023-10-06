<?php

abstract class BaseModelValidator
{
    protected Model $model;
    protected array $errors = [];

    public function __construct(Model $model)
    {
        $this->model = $model;
    }

    abstract public function validate(array $requiredFields = []): bool;
    abstract public function validateId(bool $isRequired = true): self;

    protected function validateRequiredFields(array $requiredFields): self
    {
        foreach ($requiredFields as $field) {
           if (empty($this->model->$field)) {
               $this->addError($field, ValidationErrorTypeNames::REQUIRED, "$field is required.");
           }
        }
        return $this;
    }

    protected function validateModelId(?int $id, string $field, callable $existsCallback, bool $isRequired): self
    {
        If ($isRequired && empty($id)) {
            $this->addError($field, ValidationErrorTypeNames::REQUIRED, "$field is required.");
            return $this;
        }

        if ($isRequired && !is_numeric($id)) {
            $this->addError($field, ValidationErrorTypeNames::INVALID_TYPE, "$field must be a number");
            return $this;
        }

        try {
            if (!is_null($id) && !$existsCallback($id)) {
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
