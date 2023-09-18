<?php

use JetBrains\PhpStorm\NoReturn;

class HostelsAPIController extends BaseAPIController
{
    public function handleGetRequest(?int $id): void
    {
        try {
            if ($id !== null) {
                $hostel = HostelModel::getOneById($id);
                if ($hostel !== null) {
                    $this->sendResponse(200, ['hostel' => $hostel, 'success' => true]);
                } else {
                    $this->sendResponse(404, ['error' => 'Hostel not found', 'code' => 404, 'success' => false]);
                }
            } else {
                $hostels = HostelModel::getAll();
                $this->sendResponse(200, ['hostels' => $hostels, 'success' => true]);
            }
        } catch (Exception $e) {
            Helpers::log_error($e->getMessage());
            $this->sendResponse(500, ['error' => 'Internal Server Error', 'code' => 500, 'success' => false]);
        }
    }

    #[NoReturn]
    public function handlePostRequest(array $data): void
    {
        // Not implemented
        // Send appropriate response
        $this->sendResponse(501, ['error' => 'Not Implemented', 'code' => 501, 'success' => false]);
    }

    #[NoReturn]
    public function handlePutOrPatchRequest(int $id, array $data, bool $isPatch): void
    {
        // Not implemented
        // Send appropriate response
        $this->sendResponse(501, ['error' => 'Not Implemented', 'code' => 501, 'success' => false]);
    }

    #[NoReturn]
    public function handleDeleteRequest(int $id): void
    {
        $this->sendResponse(501, ['error' => 'Not Implemented', 'code' => 501, 'success' => false]);
    }
}