<?php

class ReservationsAPIController extends BaseAPIController
{
    public function handleGetRequest(?int $id): void
    {
        try {
            if ($id !== null) {
                $reservation = ReservationModel::getOneById($id);
                if ($reservation !== null) {
                    if (!Helpers::is_admin() || $reservation->user->id !== Helpers::get_logged_in_user()->id) {
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
            $errors = $this->validateModel($reservation, true);
            if (!empty($errors)) {
                $this->sendResponse(400, ['error' => 'Invalid POST data', 'code' => 400, 'success' => false, 'errors' => $errors]);
            }
            // Set user id to the logged-in user's id, except if the logged-in user is an admin
            if (!Helpers::is_admin()) {
                $reservation->user_id = Helpers::get_logged_in_user()->id;
            }
            // Check if user is eligible for reservation
            if (!ReservationModel::isUserEligibleForReservation($reservation->user_id)) {
                $this->sendResponse(403, ['error' => 'User has an active, pending or non-expired reservation', 'code' => 403, 'success' => false]);
            }
            // Status id is always 1 (pending) when creating a new reservation
            $reservation->status_id = ReservationStatusModel::getStatusIdByName(ReservationStatusModel::PENDING);
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
            $this->validateId(ReservationModelSchema::ID, new ReservationModel([ReservationModelSchema::ID => $id]), true);
            $data['id'] = $id;
            $reservation = new ReservationModel($data);
            $existingReservation = ReservationModel::getOneById($id);
            $errors = $this->validateModel($reservation, !$isPatch);
            if (!empty($errors)) {
                $this->sendResponse(400, ['error' => 'Invalid data', 'code' => 400, 'success' => false, 'errors' => $errors]);
            }

            // Set user id to the logged-in user's id, except if the logged-in user is an admin
            if (!Helpers::is_admin()) {
                $reservation->user_id = Helpers::get_logged_in_user()->id;
            }

            // A non-admin user can not change status to confirmed. Check against the existing reservation.
            if (!$this->canConfirmReservation($reservation, $existingReservation)) {
                $this->sendResponse(403, ['error' => 'You can not change the status of this reservation to confirmed', 'code' => 403, 'success' => false]);
            }

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

    public function handleDeleteRequest(int $id): void
    {
        try {
            $this->validateId(ReservationModelSchema::ID, new ReservationModel([ReservationModelSchema::ID => $id]), true);
            $reservation = ReservationModel::getOneById($id);

            // A non-admin user can not delete a reservation that is not theirs neither can they delete a confirmed reservation
            if (!$this->isReservationOwner($reservation) || $reservation->status_id === ReservationStatusModel::getStatusIdByName(ReservationStatusModel::CONFIRMED)) {
                $this->sendResponse(403, ['error' => 'You can not delete this reservation!', 'code' => 403, 'success' => false]);
            }
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

    private function isReservationOwner(ReservationModel $reservation): bool
    {
        return $reservation->user_id === Helpers::get_logged_in_user()->id;
    }

    /**
     * @throws Exception
     */
    private function canConfirmReservation(ReservationModel $reservation, ReservationModel $existing): bool
    {
        $confirmedStatusId = ReservationStatusModel::getStatusIdByName(ReservationStatusModel::CONFIRMED);
        if ($reservation->status_id === $confirmedStatusId && $existing->status_id !== $confirmedStatusId && !Helpers::is_admin()) {
            return false;
        }
        return true;
    }
}