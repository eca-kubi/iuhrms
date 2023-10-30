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

    public function test(string $dbhost): void
    {
        // Test the database connection

        try {
            // Create connection
            $conn = new mysqli($dbhost, DB_USER, DB_PASSWORD, DB_NAME);// Check connection
            if ($conn->connect_error) {
                die("Connection failed: " . $conn->connect_error);
            } else {
                echo "Connected successfully" . PHP_EOL;
            }
        } catch (Exception $e) {
            echo "Error: " . $e->getMessage();
        }

    }
}