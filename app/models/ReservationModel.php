<?php

declare(strict_types=1);

class ReservationModel extends Model
{
public int $id;
    public int $user_id;
    public int $hostel_id;
    public string $room_type;
    public string $reservation_date;
    public string $semester;
    public string $status;
    public datetime|string $created_at; // It can be datetime or date string
    public datetime|string $updated_at; // It can be datetime or date string

    public function __construct()
    {
        parent::__construct();
    }

    public static function getPrimaryKeyFieldName(): string
    {
        return ReservationModelSchema::ID;
    }
}
