<?php

class ReservationsAPIController extends BaseAPIController
{
    public function handleGetRequest(?int $id): void
    {
        try {
            if ($id !== null) {
                $reservation = ReservationModel::getOneById($id);
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
        if (!Helpers::is_admin()) {
            try {
                // Required fields for creating a new reservation
                $requiredFields = [
                    ReservationModelSchema::USER_ID,
                    ReservationModelSchema::HOSTEL_ID,
                    ReservationModelSchema::ROOM_TYPE_ID,
                    ReservationModelSchema::SEMESTER_ID,
                ];

                $data = [
                    ReservationModelSchema::USER_ID => Helpers::get_logged_in_user()->id,
                    ReservationModelSchema::HOSTEL_ID => $data[ReservationModelSchema::HOSTEL_ID] ?? null,
                    ReservationModelSchema::ROOM_TYPE_ID => $data[ReservationModelSchema::ROOM_TYPE_ID] ?? null,
                    ReservationModelSchema::SEMESTER_ID => $data[ReservationModelSchema::SEMESTER_ID] ?? null,
                    ReservationModelSchema::STATUS_ID => ReservationStatusModel::getStatusIdByName(ReservationStatusModel::PENDING),
                ];

                $reservation = new ReservationModel($data);
                // Validate the data
                $errors = $this->validateModel($reservation, $requiredFields);
                if (!empty($errors)) {
                    $this->sendResponse(400, ['message' => 'Invalid POST data', 'code' => 400, 'success' => false, 'errors' => $errors]);
                }

                // Save the reservation
                $insertedId = $reservation->save();
                if ($insertedId) {
                    $reservationData = ReservationModel::getOneById($insertedId);
                    // Email the user
                    Helpers::send_booking_email($reservationData);
                    $this->sendResponse(201, ['reservation' => $reservationData, 'success' => true]);
                } else {
                    $this->sendResponse(500, ['message' => 'Internal Server Error', 'code' => 500, 'success' => false]);
                }

            } catch (Exception $e) {
                Helpers::log_error($e->getMessage());
                $this->sendResponse(400, ['message' => 'Invalid POST data', 'code' => 400, 'success' => false]);
            }
        } else {
            // todo: Admins can create a reservation for any user
        }
    }

    public function handlePutOrPatchRequest(int $id, array $data, bool $isPatch = false): void
    {

        if (!Helpers::is_admin()) {
            try {
                // Validate the id
                $this->validateId(ReservationModelSchema::ID, new ReservationModel([ReservationModelSchema::ID => $id]), true);
                $existingReservation = ReservationModel::getOneById($id);

                // Non Admin can only update a pending reservation that is theirs
                if (!$this->isReservationOwner($existingReservation) || $existingReservation->status_id !== ReservationStatusModel::getStatusIdByName(ReservationStatusModel::PENDING)) {
                    $this->sendResponse(403, ['error' => 'You can not update this reservation!', 'code' => 403, 'success' => false]);
                }

                // Validate the data
                // Required fields for updating a reservation
                $requiredFields = [
                    ReservationModelSchema::ID,
                    ReservationModelSchema::USER_ID,
                    ReservationModelSchema::HOSTEL_ID,
                    ReservationModelSchema::ROOM_TYPE_ID,
                    ReservationModelSchema::SEMESTER_ID,
                ];

                $data = [
                    ReservationModelSchema::ID => $id,
                    ReservationModelSchema::USER_ID => $data[ReservationModelSchema::USER_ID] ?? $existingReservation->user_id,
                    ReservationModelSchema::HOSTEL_ID => $data[ReservationModelSchema::HOSTEL_ID] ?? $existingReservation->hostel_id,
                    ReservationModelSchema::ROOM_TYPE_ID => $data[ReservationModelSchema::ROOM_TYPE_ID] ?? $existingReservation->room_type_id,
                    ReservationModelSchema::SEMESTER_ID => $data[ReservationModelSchema::SEMESTER_ID] ?? $existingReservation->semester_id,
                ];

                $reservation = new ReservationModel($data);
                // Validate the reservation data
                $errors = $this->validateModel($reservation, $requiredFields);
                if (!empty($errors)) {
                    $this->sendResponse(400, ['error' => 'Invalid PUT or PATCH data', 'code' => 400, 'success' => false, 'errors' => $errors]);
                }

                // Save the reservation
                $success = $reservation->save();
                if ($success) {
                    $reservationData = ReservationModel::getOneById($id);
                    $this->sendResponse(200, ['reservation' => $reservationData, 'success' => true]);
                } else {
                    $this->sendResponse(500, ['error' => 'Internal Server Error', 'code' => 500, 'success' => false]);
                }

            } catch (Exception $e) {
                Helpers::log_error($e->getMessage());
                $this->sendResponse(400, ['error' => 'Invalid data', 'code' => 400, 'success' => false]);
            }
        } else {
            // todo: Admins can update any reservation. They can also confirm or reject a reservation.

        }
    }

    public function handleDeleteRequest(int $id): void
    {
        try {
            $this->validateId(ReservationModelSchema::ID, new ReservationModel([ReservationModelSchema::ID => $id]), true);
            $reservation = ReservationModel::getOneById($id);

            // A non-admin user can not delete a reservation that is not theirs neither can they delete a confirmed reservation
            if (!Helpers::is_admin()) {
                if (!$this->isReservationOwner($reservation) || $reservation->status_id === ReservationStatusModel::getStatusIdByName(ReservationStatusModel::CONFIRMED)) {
                    $this->sendResponse(403, ['error' => 'You can not delete this reservation!', 'code' => 403, 'success' => false]);
                }
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

}