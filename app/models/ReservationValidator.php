<?php

class ReservationValidator
{
    private ReservationModel $reservation;
    private array $errors = [];

    public function __construct(ReservationModel $reservation)
    {
        $this->reservation = $reservation;
    }

    public function validate(bool $isRequired = true): bool
    {
        $this->validateUserId($isRequired);
        $this->validateHostelId($isRequired);
        $this->validateRoomTypeId($isRequired);
        $this->validateSemesterId($isRequired);
        $this->validateStatusId($isRequired);
        return empty($this->errors);
    }

    public function validateUserId(bool $isRequired = true): ReservationValidator
    {
        $userId = $this->reservation->user_id ?? null;

        if (!$isRequired && is_null($userId)) {
            return $this;
        }

        if (empty($userId)) {
            $this->addError(ReservationModelSchema::USER_ID, 'User id is required.');
            return $this;
        }

        if (!is_numeric($userId)) {
            $this->addError(ReservationModelSchema::USER_ID, 'User id must be a number');
            return $this;
        }

        try {
            if (!UserModel::exists($userId)) {
                $this->addError(ReservationModelSchema::USER_ID, 'User does not exist');
            }
        } catch (Exception $e) {
            $this->addError(ReservationModelSchema::USER_ID, 'User does not exist');
            Helpers::log_error($e->getMessage());
        }

        return $this;
    }


    public function validateHostelId(bool $isRequired = true): ReservationValidator
    {
        $hostelId = $this->reservation->hostel_id ?? null;

        if (!$isRequired && is_null($hostelId)) {
            return $this;
        }

        if (empty($hostelId)) {
            $this->addError(ReservationModelSchema::HOSTEL_ID, 'Hostel id is required.');
            return $this;
        }

        if (!is_numeric($hostelId)) {
            $this->addError(ReservationModelSchema::HOSTEL_ID, 'Hostel id must be a number');
            return $this;
        }

        try {
            if (!HostelModel::exists($hostelId)) {
                $this->addError(ReservationModelSchema::HOSTEL_ID, 'Hostel does not exist');
            }
        } catch (Exception $e) {
            $this->addError(ReservationModelSchema::HOSTEL_ID, 'Hostel does not exist');
            Helpers::log_error($e->getMessage());
        }

        return $this;
    }

    public function validateRoomTypeId(bool $isRequired = true): ReservationValidator
    {

        $roomTypeId = $this->reservation->room_type_id ?? null;
        if (!$isRequired && is_null($roomTypeId)) {
            return $this;
        }
        if (empty($roomTypeId)) {
            $this->addError(ReservationModelSchema::ROOM_TYPE_ID, 'Room type id is required.');
            return $this;
        }
        if (!is_numeric($roomTypeId)) {
            $this->addError(ReservationModelSchema::ROOM_TYPE_ID, 'Room type id must be a number');
            return $this;
        }
        try {
            if (!RoomTypeModel::exists($roomTypeId)) {
                $this->addError(ReservationModelSchema::ROOM_TYPE_ID, 'Room type does not exist');
            }
        } catch (Exception $e) {
            $this->addError(ReservationModelSchema::ROOM_TYPE_ID, 'Room type does not exist');
            Helpers::log_error($e->getMessage());
        }

        return $this;
    }

    public function validateSemesterId(bool $isRequired = true): ReservationValidator
    {

        $semesterId = $this->reservation->semester_id ?? null;

        if (!$isRequired && is_null($semesterId)) {
            return $this;
        }

        if (empty($semesterId)) {
            $this->addError(ReservationModelSchema::SEMESTER_ID, 'Semester id is required.');
            return $this;
        }

        if (!is_numeric($semesterId)) {
            $this->addError(ReservationModelSchema::SEMESTER_ID, 'Semester id must be a number');
            return $this;
        }

        try {
            if (!SemesterModel::exists($semesterId)) {
                $this->addError(ReservationModelSchema::SEMESTER_ID, 'Semester does not exist');
            }
        } catch (Exception $e) {
            $this->addError(ReservationModelSchema::SEMESTER_ID, 'Semester does not exist');
            Helpers::log_error($e->getMessage());
        }
        return $this;
    }

    public function validateStatusId(bool $isRequired = true): ReservationValidator
    {
        $statusId = $this->reservation->status_id ?? null;

        if (!$isRequired && is_null($statusId)) {
            return $this;
        }

        if (empty($statusId)) {
            $this->addError(ReservationModelSchema::STATUS_ID, 'Status id is required.');
            return $this;
        }

        if (!is_numeric($statusId)) {
            $this->addError(ReservationModelSchema::STATUS_ID, 'Status id must be a number');
            return $this;
        }

        try {
            if (!ReservationStatusModel::exists($statusId)) {
                $this->addError(ReservationModelSchema::STATUS_ID, 'Status does not exist');
            }
        } catch (Exception $e) {
            $this->addError(ReservationModelSchema::STATUS_ID, 'Status does not exist');
            Helpers::log_error($e->getMessage());
        }
        return $this;
    }

    private function addError(string $field, string $message): void
    {
        if (!isset($this->errors[$field])) {
            $this->errors[$field] = [];
        }

        $this->errors[$field][] = $message;
    }

    public function getErrors(string $field = null): array
    {
        if ($field) {
            return $this->errors[$field] ?? [];
        }

        return $this->errors;
    }
}