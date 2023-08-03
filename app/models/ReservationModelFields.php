<?php
declare(strict_types=1);


final class ReservationModelFields
{
    public const ID = 'id';
    public const USER_ID = 'user_id';
    public const HOSTEL_ID = 'hostel_id';

    public const ROOM_ID = 'room_id';
    public const BOOKING_DATE = 'booking_date';
    public const CHECK_IN = 'check_in_date';
    public const CHECK_OUT = 'check_out_date';
    public const STATUS = 'status';

    public const TABLE_NAME = 'reservations';
    private function __construct() {}
}