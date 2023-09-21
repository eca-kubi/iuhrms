<?php

class RoomTypesAPIController extends BaseAPIController
{

    public function handleGetRequest(?int $id): void
    {
        try {
            if ($id !== null) {
                $roomType = RoomTypeModel::getOneById($id);
                if ($roomType !== null) {
                    $this->sendResponse(200, ['room_types' => $roomType, 'success' => true]);
                } else {
                    $this->sendResponse(404, ['error' => 'Room type not found', 'code' => 404, 'success' => false]);
                }
            } else {
                $roomTypes = RoomTypeModel::getAll();
                $this->sendResponse(200, ['room_types' => $roomTypes, 'success' => true]);
            }
        } catch (Exception $e) {
            Helpers::log_error($e->getMessage());
            $this->sendResponse(500, ['error' => 'Internal Server Error', 'code' => 500, 'success' => false]);
        }
    }
}