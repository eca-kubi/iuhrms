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
           // Get the list of users
            $users = UserModel::getAll();
            // Print the list of users
            foreach ($users as $user) {
                echo $user->getFullName() . PHP_EOL;
            }
        }catch (Exception $e) {
            echo $e->getMessage();
        }
    }
}