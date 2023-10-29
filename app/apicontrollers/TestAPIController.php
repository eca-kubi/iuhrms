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
        try {
            $db = Database::getDbh();
            echo "Database connection successful!" . PHP_EOL;
        }catch (Exception $e) {
            echo $e->getMessage();
        }
    }
}