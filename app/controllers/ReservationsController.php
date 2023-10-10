<?php

class ReservationsController extends Controller
{
    // Approve reservation
    public function approve(?int $reservation_id): void
    {
        // User must be an admin to approve reservation
        if (!Helpers::is_admin()) {
            // Send 403 Forbidden response
            $this->sendJSONResponse(403, ['error' => 'Forbidden', 'code' => 403, 'success' => false]);
        }
        // Approve reservation
        try {
            $reservation = ReservationModel::getOneById($reservation_id);
            if ($reservation === null) {
                // Send 404 Not Found response
                $this->sendJSONResponse(404, ['error' => 'Reservation not found', 'code' => 404, 'success' => false]);
            }
            $reservation->status_id = ReservationStatusModel::getStatusIdByName(ReservationStatusModel::CONFIRMED);
            if($reservation->save()) {
                // Send 200 OK response
                $this->sendJSONResponse(200, ['reservation' => $reservation, 'success' => true]);
            } else {
                // Send 500 Internal Server Error response
                $this->sendJSONResponse(500, ['error' => 'Internal Server Error', 'code' => 500, 'success' => false]);
            }
        } catch (Exception $e) {
            // log error
            Helpers::log_error($e);
            // Send 500 Internal Server Error response
            $this->sendJSONResponse(500, ['error' => 'Internal Server Error', 'code' => 500, 'success' => false]);
        }
    }

    public function reject(?int $reservation_id): void {
        // User must be an admin to reject reservation
        if (!Helpers::is_admin()) {
            // Send 403 Forbidden response
            $this->sendJSONResponse(403, ['error' => 'Forbidden', 'code' => 403, 'success' => false]);
        }
        // Reject reservation
        try {
            $reservation = ReservationModel::getOneById($reservation_id);
            if ($reservation === null) {
                // Send 404 Not Found response
                $this->sendJSONResponse(404, ['error' => 'Reservation not found', 'code' => 404, 'success' => false]);
            }
            $reservation->status_id = ReservationStatusModel::getStatusIdByName(ReservationStatusModel::REJECTED);
            if($reservation->save()) {
                // Get reservation data
                $reservation = ReservationModel::getOneById($reservation_id);
                // Send 200 OK response
                $this->sendJSONResponse(200, ['reservation' => $reservation, 'success' => true]);
            } else {
                // Send 500 Internal Server Error response
                $this->sendJSONResponse(500, ['error' => 'Internal Server Error', 'code' => 500, 'success' => false]);
            }
        } catch (Exception $e) {
            // log error
            Helpers::log_error($e);
            // Send 500 Internal Server Error response
            $this->sendJSONResponse(500, ['error' => 'Internal Server Error', 'code' => 500, 'success' => false]);
        }
    }

}