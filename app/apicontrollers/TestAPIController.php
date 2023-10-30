<?php

class TestAPIController extends BaseAPIController
{

    /**
     * @param int|null $id
     * @return void
     */
    public function handleGetRequest(?int $id): void
    {
        echo "Hello World!" . PHP_EOL . "This is a test API controller.";
    }

    public function handleRequest(?int $id): void
    {
        // Test the database connection

        // Create connection
        $conn = new mysqli(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);

        // Check connection
        if ($conn->connect_error) {
            die("Connection failed: " . $conn->connect_error);
        }


    }
}