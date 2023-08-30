<?php

class ReservationAPIController extends BaseAPIController
{

    public function handleGetRequest(?int $id): void
    {
        try {
            if ($id !== null) {
                $reservation = ReservationModel::getOneById((int)$id);
                if ($reservation !== null) {
                    if (!Helpers::is_admin() && $reservation->user->id !== Helpers::get_logged_in_user()->id) {
                        $this->sendResponse(403, ['error' => 'Forbidden', 'code' => 403, 'success' => false]);
                    }
                    $this->sendResponse(200, ['reservation' => $reservation, 'success' => true]);
                } else {
                    $this->sendResponse(404, ['error' => 'Reservation not found', 'code' => 404, 'success' => false]);
                }
            } else {
                $reservations = Helpers::is_admin() ? ReservationModel::getAll() : ReservationModel::getAllByUserId(Helpers::get_logged_in_user()->id);
                $this->sendResponse(200, ['reservations' => $reservations, 'success' => true]);
            }
        } catch (Exception $e) {
            Helpers::log_error($e->getMessage());
            $this->sendResponse(500, ['error' => 'Internal Server Error', 'code' => 500, 'success' => false]);
        }
    }

    public function handlePostRequest(array $data): void
    {

        try {
            $reservation = new ReservationModel($data);
            $errors = $this->validateReservation($reservation, true);
            if (!empty($errors)) {
                $this->sendResponse(400, ['error' => 'Invalid POST data', 'code' => 400, 'success' => false, 'errors' => $errors]);
            }
            if (!ReservationModel::isUserEligibleForReservation($reservation->user_id)) {
                $this->sendResponse(403, ['error' => 'User has an active, pending or non-expired reservation', 'code' => 403, 'success' => false]);
            }
            if ($reservation->save()) {
                $insert_id = ReservationModel::getInsertId();
                $reservationData = ReservationModel::getOneById($insert_id);
                $this->sendResponse(201, ['reservation' => $reservationData, 'success' => true]);
            } else {
                $this->sendResponse(500, ['error' => 'Internal Server Error', 'code' => 500, 'success' => false]);
            }
        } catch (Exception $e) {
            Helpers::log_error($e->getMessage());
            $this->sendResponse(400, ['error' => 'Invalid POST data', 'code' => 400, 'success' => false]);
        }
    }

    public function handlePutOrPatchRequest(int $id, array $data, bool $isPatch = false): void
    {

        try {
            $this->validateReservationId($id);
            $data['id'] = $id;
            $reservation = new ReservationModel($data);
            $errors = $this->validateReservation($reservation, !$isPatch);
            if (!empty($errors)) {
                $this->sendResponse(400, ['error' => 'Invalid data', 'code' => 400, 'success' => false, 'errors' => $errors]);
            }
            $this->validateReservationOwner(ReservationModel::getOneById($id));
            if ($reservation->save()) {
                $reservationData = ReservationModel::getOneById($id);
                $this->sendResponse(200, ['reservation' => $reservationData, 'success' => true]);
            } else {
                $this->sendResponse(500, ['error' => 'Internal Server Error', 'code' => 500, 'success' => false]);
            }
        } catch (Exception $e) {
            Helpers::log_error($e->getMessage());
            $this->sendResponse(400, ['error' => 'Invalid data', 'code' => 400, 'success' => false]);
        }
    }

    public function handleDeleteRequest($id): void
    {
        try {
            $this->validateReservationId($id);
            $reservation = ReservationModel::getOneById($id);
            $this->validateReservationOwner($reservation);

            $success = $reservation->delete();
            if ($success) {
                $this->sendResponse(204, ['success' => true]);
            } else {
                $this->sendResponse(500, ['error' => 'Internal Server Error', 'code' => 500, 'success' => false]);
            }
        } catch (Exception $e) {
            Helpers::log_error($e->getMessage());
            $this->sendResponse(400, ['error' => 'Invalid DELETE data', 'code' => 400, 'success' => false]);
        }
    }

    private function validateReservation(ReservationModel $reservation, bool $required): array
    {
        $validator = new ReservationValidator($reservation);
        $validator->validate($required);
        return $validator->getErrors();
    }

    /**
     * @throws Exception
     */
    private function validateReservationId(?int $id): void
    {
        if ($id === null) {
            // Return 400
            $message = Helpers::json_encode(['error' => 'Reservation id is required.', 'code' => 400, 'success' => false]);
            Helpers::http_response_code(400, $message);
            exit;
        } else if (!is_numeric($id)) {
            // Return 400
            $message = Helpers::json_encode(['error' => 'Reservation id must be a number.', 'code' => 400, 'success' => false]);
            Helpers::http_response_code(400, $message);
            exit;
        } elseif (!ReservationModel::exists($id)) {
            // Return 404
            $message = Helpers::json_encode(['error' => 'Reservation not found', 'code' => 404, 'success' => false]);
            Helpers::http_response_code(404, $message);
            exit;
        }
    }

    private function validateReservationOwner(ReservationModel $reservation): void
    {
        if ($reservation->user->id !== Helpers::get_logged_in_user()->id) {
            $this->sendResponse(403, ['error' => 'Forbidden', 'code' => 403, 'success' => false]);
        }
    }

}