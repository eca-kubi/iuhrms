<?php

class HostelAPIController extends BaseAPIController
{

    public function handleGetRequest(?int $id): void
    {
        try {
            if ($id !== null) {
                $hostel = HostelModel::getOneById((int)$id);
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

    public function handlePostRequest(array $data): void
    {
        try {
            $hostel = new HostelModel($data);
            $errors = $this->validateModel($hostel, true);
            if (!empty($errors)) {
                $this->sendResponse(400, ['error' => 'Invalid POST data', 'code' => 400, 'success' => false, 'errors' => $errors]);
            }
            if ($hostel->save()) {
                $insert_id = HostelModel::getInsertId();
                $hostelData = HostelModel::getOneById($insert_id);
                $this->sendResponse(201, ['hostel' => $hostelData, 'success' => true]);
            } else {
                $this->sendResponse(500, ['error' => 'Internal Server Error', 'code' => 500, 'success' => false]);
            }
        } catch (Exception $e) {
            Helpers::log_error($e->getMessage());
            $this->sendResponse(400, ['error' => 'Invalid POST data', 'code' => 400, 'success' => false]);
        }
    }

    public function handlePutOrPatchRequest(int $id, array $data, bool $isPatch): void
    {
        try {
            $this->validateHostelId($id);
            $data['id'] = $id;
            $hostel = new HostelModel($data);
            $errors = $this->validateModel($hostel, $isPatch);
            if (!empty($errors)) {
                $this->sendResponse(400, ['error' => 'Invalid PUT data', 'code' => 400, 'success' => false, 'errors' => $errors]);
            }
            if ($hostel->save()) {
                $hostelData = HostelModel::getOneById($id);
                $this->sendResponse(200, ['hostel' => $hostelData, 'success' => true]);
            } else {
                $this->sendResponse(500, ['error' => 'Internal Server Error', 'code' => 500, 'success' => false]);
            }
        } catch (Exception $e) {
            Helpers::log_error($e->getMessage());
            $this->sendResponse(400, ['error' => 'Invalid PUT data', 'code' => 400, 'success' => false]);
        }
    }

    public function handleDeleteRequest(int $id)
    {
        // TODO: Implement handleDeleteRequest() method.
    }
}