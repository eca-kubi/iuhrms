<?php

declare(strict_types=1);

class ReservationModel extends Model
{
    public readonly int|null $id;
    public readonly datetime|string|null $reservation_date;
    public int $user_id;
    public int $hostel_id;
    public int $room_type_id;
    public int $semester_id;
    public int $status_id;
    public UserModel $user;
    public HostelModel $hostel;
    public RoomTypeModel $room_type;
    public SemesterModel $semester;
    public ReservationStatusModel $status;
    protected datetime|string $created_at; // It can be datetime or date string
    protected datetime|string $updated_at; // It can be datetime or date string


    public function __construct(array $data)
    {
        parent::__construct($data);

        // Set the ID if it exists. ID is read-only and cannot be set from outside the constructor
        $this->id = $data['id'] ?? null;

        // Set the reservation_date if it exists
        $this->reservation_date = $data[ReservationModelSchema::RESERVATION_DATE] ?? date('Y-m-d'); // Default to today's date

        // Call the createFromData method to hydrate the object with data
        $this->createFromData($data);
    }

    public static function getPrimaryKeyFieldName(): string
    {
        return ReservationModelSchema::ID;
    }

    /**
     * Returns reservation requests by user id
     * @param int $user_id
     * @return ReservationModel[]
     * @throws Exception
     */
    public static function getReservationsByUserId(int $user_id): array
    {
        $db = Database::getDbh();
        $db->where(ReservationModelSchema::USER_ID, $user_id);
        $db->orderBy(ReservationModelSchema::CREATED_AT, 'DESC');
        $reservations = $db->get(ReservationModel::getTableName());
        return self::getRelatedModels($reservations);
    }

    /**
     * @param array $data
     * @return $this
     */
    protected function createFromData(array $data): static
    {
        // Set the properties except the id
        foreach ($data as $key => $value) {
            if ($key === 'user' && is_array($value)) {
                $this->user = new UserModel($value);
            } elseif ($key === 'hostel' && is_array($value)) {
                $this->hostel = new HostelModel($value);
            } elseif ($key === 'room_type' && is_array($value)) {
                $this->room_type = new RoomTypeModel($value);
            } elseif ($key === 'semester' && is_array($value)) {
                $this->semester = new SemesterModel($value);
            } elseif ($key === 'status' && is_array($value)) {
                $this->status = new ReservationStatusModel($value);
            } elseif ($key !== ReservationModelSchema::ID && $key !== ReservationModelSchema::RESERVATION_DATE && property_exists($this, $key) ) {
                $this->{$key} = $value;
            }
        }
        return $this;
    }

    /**
     * Returns all reservations
     * @return ReservationModel[]
     * @throws Exception
     */
    public static function getAll(): array
    {
        $db = Database::getDbh();
        $db->orderBy(ReservationModelSchema::CREATED_AT, 'DESC');
        $reservations = $db->get(ReservationModel::getTableName());
        return self::getRelatedModels($reservations);
    }

    /**
     * Returns one reservation by id
     * @param int $id
     * @return ReservationModel|null
     * @throws Exception
     */
    public static function getOneById(int $id): ReservationModel|null
    {
        $db = Database::getDbh();
        $db->where(ReservationModelSchema::ID, $id);
        $reservation = $db->getOne(ReservationModel::getTableName());
        if ($reservation) {
            return self::getRelatedModels([$reservation])[0];
        }
        return null;
    }

    /**
     * Returns all reservations by status id
     * @return ReservationModel[]
     * @throws Exception
     */
    protected static function getRelatedModels(array $reservations): array
    {
        $reservation_requests = [];
        foreach ($reservations as $key => $reservation) {
            $reservation_requests[] = new ReservationModel($reservation);
            $reservation_requests[$key]->user = UserModel::getOneById($reservation[ReservationModelSchema::USER_ID]);
            $reservation_requests[$key]->hostel = HostelModel::getOneById($reservation[ReservationModelSchema::HOSTEL_ID]);
            $reservation_requests[$key]->room_type = RoomTypeModel::getOneById($reservation[ReservationModelSchema::ROOM_TYPE_ID]);
            $reservation_requests[$key]->semester = SemesterModel::getOneById($reservation[ReservationModelSchema::SEMESTER_ID]);
            $reservation_requests[$key]->status = ReservationStatusModel::getOneById($reservation[ReservationModelSchema::STATUS_ID]);
        }
        return $reservation_requests;
    }

    /**
     * @param int|null $user_id
     * @return ReservationModel[]
     * @throws Exception
     */
    public static function getAllByUserId(?int $user_id): array
    {
        $db = Database::getDbh();
        $db->where(ReservationModelSchema::USER_ID, $user_id);
        $db->orderBy(ReservationModelSchema::CREATED_AT, 'DESC');
        $reservations = $db->get(ReservationModel::getTableName());
        return self::getRelatedModels($reservations);
    }

    /**
     * Get all active reservations by user id
     * @param int $user_id
     * @return ReservationModel[]
     * @throws Exception
     */
    public static function getAllConfirmedByUserId(int $user_id): array
    {
        $db = Database::getDbh();
        $db->where(ReservationModelSchema::USER_ID, $user_id);
        $db->orderBy(ReservationModelSchema::CREATED_AT, 'DESC');
        $reservations = $db->get(ReservationModel::getTableName());
        return array_filter(self::getRelatedModels($reservations), function ($reservation) {
            return $reservation->status->name === ReservationStatusModel::STATUS_CONFIRMED;
        });
    }

    /**
     * Get all pending reservations by user id
     * @throws Exception
     */
    public static function getAllPendingByUserId(int $user_id): array
    {
        $db = Database::getDbh();
        $db->where(ReservationModelSchema::USER_ID, $user_id);
        $db->orderBy(ReservationModelSchema::CREATED_AT, 'DESC');
        $reservations = self::getRelatedModels($db->get(ReservationModel::getTableName()));
        return array_filter($reservations, function ($reservation) {
            return $reservation->status->name === ReservationStatusModel::STATUS_PENDING;
        });
    }

    /**
     * Get all non-expired reservations by user id using the semester end date
     * @throws Exception
     */
    public static function getAllNonExpiredByUserId(int $user_id): array
    {
        // Use isExpired() method to check if reservation is expired
        $db = Database::getDbh();
        $db->where(ReservationModelSchema::USER_ID, $user_id);
        $db->orderBy(ReservationModelSchema::CREATED_AT, 'DESC');
        $reservations = $db->get(ReservationModel::getTableName());
        return array_filter(self::getRelatedModels($reservations), function ($reservation) {
            return !$reservation->isExpired() && $reservation->status->name === ReservationStatusModel::STATUS_CONFIRMED;
        });
    }

    /**
     * @throws Exception
     */
    public function calculateExpiryDate(): DateTime
    {
        // Retrieve the start and end dates for the selected semester
        $semester = SemesterModel::getOneById($this->semester_id);
        return $semester->end_date;
    }

    /**
     * @throws Exception
     */
    public function isExpired(): bool
    {
        $expiryDate = $this->calculateExpiryDate();
        return $expiryDate < new DateTime();
    }

    /**
     * Checks if user is eligible for reservation
     * @throws Exception
     */
    public static function isUserEligibleForReservation(int $user_id): bool
    {
        // Check if user has a confirmed reservation
        $confirmed_reservations = ReservationModel::getAllConfirmedByUserId($user_id);
        if (!empty($confirmed_reservations)) {
            return false;
        }
        // Check if user has a pending reservation
        $pending_reservations = ReservationModel::getAllPendingByUserId($user_id);
        if (!empty($pending_reservations)) {
            return false;
        }
        // Check if user has a non-expired reservation
        $non_expired_reservations = ReservationModel::getAllNonExpiredByUserId($user_id);
        if (!empty($non_expired_reservations)) {
            return false;
        }
        return true;
    }

    /**
     * Checks if reservation exists
     * @param int $id
     * @return bool
     * @throws Exception
     */
    public static function exists(int $id): bool
    {
        $db = Database::getDbh();
        $db->where(ReservationModelSchema::ID, $id);
        return $db->has(ReservationModel::getTableName());
    }



    /**
     * Deletes a reservation
     * @throws Exception
     */
    public function delete(): bool
    {
        $db = Database::getDbh();
        $db->where(ReservationModelSchema::ID, $this->id);
        return $db->delete(ReservationModel::getTableName());
    }

    /**
     * @return ReservationModelValidator
     */
    public function getValidator(): ReservationModelValidator
    {
        return new ReservationModelValidator($this);
    }

    /**
     * @param array $data
     * @return void
     */
    protected function validateData(array $data): void
    {
        $integerFields = [
            ReservationModelSchema::USER_ID,
            ReservationModelSchema::HOSTEL_ID,
            ReservationModelSchema::ROOM_TYPE_ID,
            ReservationModelSchema::SEMESTER_ID,
            ReservationModelSchema::STATUS_ID
        ];

        foreach ($integerFields as $field) {
            if (isset($data[$field]) && !is_int($data[$field])) {
                throw new InvalidArgumentException("Invalid data type for $field. Expected integer.");
            }
        }
        // Add more validation for other types as needed
    }
}
