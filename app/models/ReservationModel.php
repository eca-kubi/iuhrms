<?php

declare(strict_types=1);

class ReservationModel extends Model
{

    /**
     * @throws Exception
     * @return ReservationModel[]
     */
    public static function all(): array
    {
        $db = Database::getDbh();
        $results = $db->get(ReservationModel::getTableName());
        $reservations = [];
        foreach ($results as $result) {
            $reservations[] = ReservationModel::factory($result);
        }
        return $reservations;
    }

    protected static function getFields(): array
    {
        return [
            ReservationModelFields::ID,
            ReservationModelFields::USER_ID,
            ReservationModelFields::ROOM_ID,
            ReservationModelFields::BOOKING_DATE,
            ReservationModelFields::CHECK_IN,
            ReservationModelFields::CHECK_OUT,
            ReservationModelFields::STATUS,
        ];
    }

    public static function getPrimaryKeyFieldName(): string
    {
        return ReservationModelFields::ID;
    }

    public static function getTableName(): string
    {
        return 'reservations';
    }

    public function save(): bool
    {
        $db = Database::getDbh();
        $data = [
            ReservationModelFields::USER_ID => $this->getUserId(),
            ReservationModelFields::ROOM_ID => $this->getRoomId(),
            ReservationModelFields::BOOKING_DATE => $this->getBookingDate(),
            ReservationModelFields::CHECK_IN => $this->getStartDate(),
            ReservationModelFields::CHECK_OUT => $this->getEndDate(),
            ReservationModelFields::STATUS => $this->getStatus(),
        ];
        if ($this->getId() === null) {
            $id = $db->insert(ReservationModel::getTableName(), $data);
            if ($id !== false) {
                $this->setId($db->getInsertId());
                return true;
            } else {
                return false;
            }
        } else {
            $db->where(ReservationModelFields::ID, $this->getId());
            return $db->update(ReservationModel::getTableName(), $data) !== false;
        }
    }

    public static function create(array $data): ?ReservationModel
    {
        $db = Database::getDbh();
        $id = $db->insert(ReservationModel::getTableName(), $data);
        if ($id !== false) {
            return ReservationModel::getReservationById($db->getInsertId());
        } else {
            return null;
        }
    }

    public static function read(int $id): ?ReservationModel
    {
        return ReservationModel::getReservationById($id);
    }

    public static function update(int $id, array $data): bool
    {
        $db = Database::getDbh();
        $db->where(ReservationModelFields::ID, $id);
        return $db->update(ReservationModel::getTableName(), $data) !== false;
    }

    public static function delete(int $id): bool
    {
        $db = Database::getDbh();
        $db->where(ReservationModelFields::ID, $id);
        return $db->delete(ReservationModel::getTableName()) !== false;
    }

    public static function getReservationById(int $id): ?ReservationModel
    {
        $db = Database::getDbh();
        $db->where(ReservationModelFields::ID, $id);
        $result = $db->getOne(ReservationModel::getTableName());
        if ($result !== null) {
            return ReservationModel::factory($result);
        } else {
            return null;
        }
    }

    public static function getReservationsByHostelId(int $hostelId): array
    {
        $db = Database::getDbh();
        $db->where(ReservationModelFields::ID, $hostelId);
        $results = $db->get(ReservationModel::getTableName());
        $reservations = [];
        foreach ($results as $result) {
            $reservations[] = ReservationModel::factory($result);
        }
        return $reservations;
    }

    public function hydrate(int $id): void
    {
        $db = Database::getDbh();
        $db->where(ReservationModelFields::ID, $id);
        $result = $db->getOne(ReservationModel::getTableName());

        if ($result) {
            $this->setId($result[ReservationModelFields::ID]);
            $this->setUserId($result[ReservationModelFields::USER_ID]);
            $this->setRoomId($result[ReservationModelFields::ROOM_ID]);
            $this->setBookingDate($result[ReservationModelFields::BOOKING_DATE]);
            $this->setStartDate($result[ReservationModelFields::CHECK_IN]);
            $this->setEndDate($result[ReservationModelFields::CHECK_OUT]);
            $this->setStatus($result[ReservationModelFields::STATUS]);
        }
    }

    public function getId(): ?int
    {
        return $this->{ReservationModelFields::ID};
    }

    public function setId(int $id): void
    {
        $this->{ReservationModelFields::ID} = $id;
    }

    public function getUserId(): int
    {
        return $this->{ReservationModelFields::USER_ID};
    }

    public function setUserId(int $userId): void
    {
        $this->{ReservationModelFields::USER_ID} = $userId;
    }

    public function getRoomId(): int
    {
        return $this->{ReservationModelFields::ROOM_ID};
    }

    public function setRoomId(int $roomId): void
    {
        $this->{ReservationModelFields::ROOM_ID} = $roomId;
    }

    public function getBookingDate(): string
    {
        return $this->{ReservationModelFields::BOOKING_DATE};
    }

    public function setBookingDate(string $bookingDate): void
    {
        $this->{ReservationModelFields::BOOKING_DATE} = $bookingDate;
    }

    public function getStartDate(): string
    {
        return $this->{ReservationModelFields::CHECK_IN};
    }

    public function setStartDate(string $startDate): void
    {
        $this->{ReservationModelFields::CHECK_IN} = $startDate;
    }

    public function getEndDate(): string
    {
        return $this->{ReservationModelFields::CHECK_OUT};
    }

    public function setEndDate(string $endDate): void
    {
        $this->{ReservationModelFields::CHECK_OUT} = $endDate;
    }

    public function getStatus(): string
    {
        return $this->{ReservationModelFields::STATUS};
    }

    public function setStatus(string $status): void
    {
        $this->{ReservationModelFields::STATUS} = $status;
    }
}
