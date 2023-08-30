<?php

use JetBrains\PhpStorm\NoReturn;
 class APIController extends Controller
{
    public function reservations(?int $id = null): void
    {
        $reservationController = new ReservationAPIController();
        $reservationController->handleRequest($id);
    }
}