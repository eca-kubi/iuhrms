<?php

declare(strict_types=1);

class ReservationModel extends Model
{
    public readonly int|null $id;
    public datetime|string|null $reservation_date;
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

        // Set the reservation date to today's date if it is a new reservation
        if (!isset($data['id'])) {
            $this->reservation_date = date('Y-m-d');
        }

        // Call the createFromData method to hydrate the object with data
        $this->createFromData($data);
    }

    /**
     * @throws Exception
     */
    public static function hasActiveReservation(int $user_id): bool
    {
        // The user has an active reservation if they have a confirmed or pending reservation that is not expired
        $db = Database::getDbh();
        $pendingStatusId = ReservationStatusModel::getStatusIdByName(ReservationStatusModel::PENDING);
        $confirmedStatusId = ReservationStatusModel::getStatusIdByName(ReservationStatusModel::CONFIRMED);
        $db->where(ReservationModelSchema::USER_ID, $user_id);
        $db->where(ReservationModelSchema::STATUS_ID, $pendingStatusId);
        $db->orWhere(ReservationModelSchema::STATUS_ID, $confirmedStatusId);
        $db->orderBy(ReservationModelSchema::CREATED_AT, 'DESC');
        $reservations = $db->get(ReservationModel::getTableName());
        $reservations = self::getRelatedModels($reservations);
        foreach ($reservations as $reservation) {
            if (!$reservation->isExpired()) {
                return true;
            }
        }



        return false;
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
            } elseif ($key !== ReservationModelSchema::ID && property_exists($this, $key)) {
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
        return array_values(array_filter(self::getRelatedModels($reservations), function ($reservation) {
            return $reservation->status->name === ReservationStatusModel::CONFIRMED;
        }));
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
        return array_values(array_filter($reservations, function ($reservation) {
            return $reservation->status->name === ReservationStatusModel::PENDING;
        }));
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
        return array_values(array_filter(self::getRelatedModels($reservations), function ($reservation) {
            return !$reservation->isExpired();
        }));
    }

    /**
     * @throws Exception
     */
    public function calculateExpiryDate(): DateTime
    {
        // Retrieve the start and end dates for the selected semester
        $semester = SemesterModel::getOneById($this->semester_id);
        return $semester->semester_end;
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
     * Return the number of overstayed occupants
     * @return int
     */
    public static function getOverstayedOccupantsCount(): int
    {
        // Return a hardcoded value for now
        return 0;
    }


    /**
     * Deletes a reservation
     * @throws Exception
     */
    public function delete(): bool
    {

        // Start a database transaction
        $db = Database::getDbh();
        $db->startTransaction();
        try {
            // Get the reservation
            $reservation = ReservationModel::getOneById($this->id);
            // Delete the reservation
            $result = $db->where(ReservationModelSchema::ID, $this->id)->delete(ReservationModel::getTableName());
            // Update the hostel occupied rooms count
            $hostel = HostelModel::getOneById($reservation->hostel_id);
            $hostel->occupied_rooms = $hostel->occupied_rooms - 1;
            $hostel->save();
            // Commit the transaction
            $db->commit();
            return $result;
        } catch (Exception $e) {
            // Rollback the transaction
            $db->rollback();
            throw $e;
        }
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

    // override the save method to use db transaction
    public function save(): bool|int
    {
        $db = Database::getDbh();
        try {
            // Get the $id using the primary key field name
            $db->startTransaction();
            $id = $this->{static::getPrimaryKeyFieldName()};
            $data = $this->toArray();
            // If the id is set, then we are updating an existing record
            if ($id) {
                // We need to get the old reservation and the new reservation to set the difference in room availability
                $oldReservation = ReservationModel::getOneById($id);
                $currentReservation = $this;
                // Update the record
                $result = $db->where(static::getPrimaryKeyFieldName(), $id)->update(static::getTableName(), $data);

                // The rooms occupied count needs to be updated.
                $oldHostel = HostelModel::getOneById($oldReservation->hostel_id);
                $currentHostel = HostelModel::getOneById($currentReservation->hostel_id);
                // Are the hostel or semester the same? If not, then we need to update the rooms occupied count
                if ($oldHostel->id !== $currentHostel->id) {
                    // The oldHostel will have a reduced occupied rooms count
                    $oldHostel->occupied_rooms = $oldHostel->occupied_rooms - 1;
                    $oldHostel->save();
                    // The currentHostel will have an increased occupied rooms count
                    $currentHostel->occupied_rooms = $currentHostel->occupied_rooms + 1;
                    $currentHostel->save();
                }
            } else {
                // If the id is not set, then we are creating a new record
                $result = $db->insert(static::getTableName(), $data);
                // The rooms occupied count needs to be updated
                $currentHostel = HostelModel::getOneById($this->hostel_id);
                $currentHostel->occupied_rooms = $currentHostel->occupied_rooms + 1;
                $currentHostel->save();
            }
            $db->commit();
            return $result;
        } catch (Exception $e) {
            $db->rollback();
            throw $e;
        }
    }
}
