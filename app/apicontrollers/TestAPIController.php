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

        try {// Create connection
            $conn = new mysqli(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);// Check connection
            if ($conn->connect_error) {
                // Output connection parameters
                echo "DB_HOST: " . DB_HOST . PHP_EOL;
                echo "DB_USER: " . DB_USER . PHP_EOL;
                echo "DB_PASSWORD: " . DB_PASSWORD . PHP_EOL;
                echo "DB_NAME: " . DB_NAME . PHP_EOL;
                die("Connection failed: " . $conn->connect_error);
            } else {
                echo "Connected successfully" . PHP_EOL;
            }
        } catch (Exception $e) {
            // Output connection parameters
            echo "DB_HOST: " . DB_HOST . PHP_EOL;
            echo "DB_USER: " . DB_USER . PHP_EOL;
            echo "DB_PASSWORD: " . DB_PASSWORD . PHP_EOL;
            echo "DB_NAME: " . DB_NAME . PHP_EOL;
            echo "Error: " . $e->getMessage();
        }
    }
}