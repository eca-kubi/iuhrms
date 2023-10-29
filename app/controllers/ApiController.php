<?php /** @noinspection PhpUnused */

class ApiController extends Controller
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

    public function roomtypes(?int $id = null): void
    {
        $roomTypeController = new RoomTypesAPIController();
        $roomTypeController->handleRequest($id);
    }

    public function test(): void
    {
        $testController = new TestAPIController();
        try {
            $testController->handleRequest(null);
        } catch (Exception $e) {
            echo $e->getMessage();
        }

    }
}