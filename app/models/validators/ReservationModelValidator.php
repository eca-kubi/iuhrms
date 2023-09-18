<?php

class ReservationModelValidator extends BaseModelValidator
{
    public function __construct(ReservationModel $reservation)
    {
        parent::__construct($reservation);
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

    public function validateUserId(bool $isRequired = true): self
    {
        return $this->validateModelId(
            $this->model->user_id ?? null,
            ReservationModelSchema::USER_ID,
            [UserModel::class, 'exists'],
            $isRequired
        );
    }

    public function validateHostelId(bool $isRequired = true): self
    {
        return $this->validateModelId(
            $this->model->hostel_id ?? null,
            ReservationModelSchema::HOSTEL_ID,
            [HostelModel::class, 'exists'],
            $isRequired
        );
    }

    public function validateRoomTypeId(bool $isRequired = true): self
    {

        return $this->validateModelId(
            $this->model->room_type_id ?? null,
            ReservationModelSchema::ROOM_TYPE_ID,
            [RoomTypeModel::class, 'exists'],
            $isRequired
        );
    }

    public function validateSemesterId(bool $isRequired = true): self
    {

        return $this->validateModelId(
            $this->model->semester_id ?? null,
            ReservationModelSchema::SEMESTER_ID,
            [SemesterModel::class, 'exists'],
            $isRequired
        );
    }

    public function validateStatusId(bool $isRequired = true): self
    {
           return $this->validateModelId(
            $this->model->status_id ?? null,
            ReservationModelSchema::STATUS_ID,
            [ReservationStatusModel::class, 'exists'],
            $isRequired
        );
    }

    /**
     * @param bool $isRequired
     * @return self
     */
    public function validateId(bool $isRequired = true): BaseModelValidator
    {
        return $this->validateModelId(
            $this->model->id ?? null,
            ReservationModelSchema::ID,
            [ReservationModel::class, 'exists'],
            $isRequired
        );
    }
}