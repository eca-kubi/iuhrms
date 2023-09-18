<?php

class HostelModelValidator extends BaseModelValidator
{
    public function __construct(HostelModel $hostel)
    {
        parent::__construct($hostel);
    }

    public function validate(bool $isRequired = true): bool
    {
        $this->validateName($isRequired);
        $this->validateDescription($isRequired);
        $this->validateLocation($isRequired);
        $this->validateTotalRooms($isRequired);
        $this->validateOccupiedRooms($isRequired);
        return empty($this->errors);
    }

    public function validateName(bool $isRequired = true): self
    {
        if (!$isRequired && empty($this->model->name)) {
            return $this;
        }

        if (empty($this->model->name)) {
            $this->addError(HostelModelSchema::NAME, ValidationErrorTypeNames::REQUIRED, "Name is required.");
            return $this;
        }

        if (strlen($this->model->name) > 255) {
            $this->addError(HostelModelSchema::NAME, ValidationErrorTypeNames::INVALID_LENGTH, "Name cannot be longer than 255 characters.");
        }

        return $this;
    }

    public function validateDescription(bool $isRequired = true): self
    {
        if (!$isRequired && empty($this->model->description)) {
            return $this;
        }

        if (empty($this->model->description)) {
            $this->addError(HostelModelSchema::DESCRIPTION, ValidationErrorTypeNames::REQUIRED, "Description is required.");
            return $this;
        }

        if (strlen($this->model->description) > 255) {
            $this->addError(HostelModelSchema::DESCRIPTION, ValidationErrorTypeNames::INVALID_LENGTH, "Description cannot be longer than 255 characters.");
        }

        return $this;
    }

    public function validateLocation(bool $isRequired = true): self
    {
        if (!$isRequired && empty($this->model->location)) {
            return $this;
        }

        if (empty($this->model->location)) {
            $this->addError(HostelModelSchema::LOCATION, ValidationErrorTypeNames::REQUIRED, "Location is required.");
            return $this;
        }

        if (strlen($this->model->location) > 255) {
            $this->addError(HostelModelSchema::LOCATION, ValidationErrorTypeNames::INVALID_LENGTH, "Location cannot be longer than 255 characters.");
        }

        return $this;
    }

    public function validateTotalRooms(bool $isRequired = true): self
    {
        if (!$isRequired && empty($this->model->total_rooms)) {
            return $this;
        }

        if (empty($this->model->total_rooms)) {
            $this->addError(HostelModelSchema::TOTAL_ROOMS, ValidationErrorTypeNames::REQUIRED, "Total rooms is required.");
            return $this;
        }

        if (!is_numeric($this->model->total_rooms)) {
            $this->addError(HostelModelSchema::TOTAL_ROOMS, ValidationErrorTypeNames::INVALID_TYPE,"Total rooms must be a number");
            return $this;
        }

        if ($this->model->total_rooms < 0) {
            $this->addError(HostelModelSchema::TOTAL_ROOMS,  ValidationErrorTypeNames::MIN_VALUE,"Total rooms cannot be less than 0");
        }

        return $this;
    }

    public function validateOccupiedRooms(bool $isRequired = true): self
    {
        if (!$isRequired && empty($this->model->occupied_rooms)) {
            return $this;
        }

        if (empty($this->model->occupied_rooms)) {
            $this->addError(HostelModelSchema::OCCUPIED_ROOMS, ValidationErrorTypeNames::REQUIRED, "Occupied rooms is required.");
            return $this;
        }

        if (!is_numeric($this->model->occupied_rooms)) {
            $this->addError(HostelModelSchema::OCCUPIED_ROOMS, ValidationErrorTypeNames::INVALID_TYPE,"Occupied rooms must be a number");
            return $this;
        }

        if ($this->model->occupied_rooms < 0) {
            $this->addError(HostelModelSchema::OCCUPIED_ROOMS, ValidationErrorTypeNames::MIN_VALUE, "Occupied rooms cannot be less than 0");
        }

        return $this;
    }

    /**
     * @param bool $isRequired
     * @return self
     */
    public function validateId(bool $isRequired = true): BaseModelValidator
    {
        return $this->validateModelId(
            $this->model->id ?? null,
            HostelModelSchema::ID,
            [HostelModel::class, 'exists'],
            $isRequired
        );
    }
}