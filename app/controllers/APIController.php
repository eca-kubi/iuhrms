<?php

class APIController extends Controller
{
    public function reservations(?int $id = null): void
    {
        $reservationController = new ReservationsAPIController();
        $reservationController->handleRequest($id);
    }

    public function hostels(?int $id = null): void
    {
        $hostelController = new HostelsAPIController();
        $hostelController->handleRequest($id);
    }

    public function semesters(?int $id = null): void
    {
        $roomController = new SemestersAPIController();
        $roomController->handleRequest($id);
    }

}