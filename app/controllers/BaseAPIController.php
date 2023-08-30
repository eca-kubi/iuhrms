<?php

use JetBrains\PhpStorm\NoReturn;

abstract class BaseAPIController
{
    abstract public function handleGetRequest(?int $id);

    abstract public function handlePostRequest(array $data);

    abstract public function handlePutOrPatchRequest(int $id, array $data, bool $isPatch);

    abstract public function handleDeleteRequest(int $id);


    public function handleRequest(?int $id): void
    {
        // Is user logged in?
        if (!Helpers::is_logged_in()) {
            // Send a 401 response
            $this->sendResponse(401, ['error' => 'Unauthorized', 'code' => 401, 'success' => false]);
        }

        // If request is not AJAX, redirect to home page
        if (!Helpers::is_ajax()) {
            Helpers::redirect_to(URL_ROOT . '/');
        }

        if (Helpers::is_get()) {
            $this->handleGetRequest($id);
        } else if (Helpers::is_post()) {
            $this->handlePostRequest($this->getPostBody());
        } else if ((Helpers::is_put() || Helpers::is_patch()) && $id !== null) {
            $this->handlePutOrPatchRequest($id, $this->getPostBody(), Helpers::is_patch());
        } else if (Helpers::is_delete()) {
            $this->handleDeleteRequest($id);
        } else {
            $this->sendResponse(400, ['error' => 'Invalid request method', 'code' => 400, 'success' => false]);
        }
    }

    #[NoReturn]
    protected function sendResponse(int $statusCode, array $data): void
    {
        $message = Helpers::json_encode($data);
        Helpers::http_response_code($statusCode, $message);
        exit;
    }

    protected function getPostBody(): array
    {
        $data = [];
        if (Helpers::get_content_type() === 'application/json') {
            // Get new Reservation data from POST data
            try {
                $data = Helpers::get_json_data();
            } catch (Exception $e) {
                // Return 400
                $message = Helpers::json_encode(['error' => 'Invalid JSON', 'code' => 400, 'success' => false]);
                Helpers::http_response_code(400, $message);
                Helpers::log_error($e->getMessage());
            }
        } else {
            // Get new Reservation data from POST data
            $data = Helpers::get_post_data();
        }
        return $data;
    }

}