<?php

class ApiController extends Controller
{

    /**
     * @throws ReflectionException
     * @throws Exception
     */
    public function reservations($id = null): void
    {
        // Is user logged in?
        if (!Helpers::is_logged_in()) {
            // Send a 401 response
            $message = Helpers::json_encode(['error' => 'Unauthorized', 'code' => 401, 'success' => false]);
            Helpers::http_response_code(401, $message);
            exit;
        }
        if (Helpers::is_ajax()) {
            if (Helpers::is_get()) {
                if ($id !== null) {
                    // Get reservation by id
                    $reservation = ReservationModel::getOneById((int)$id);
                    if ($reservation !== null) {
                        // Check if user is not admin, check if user is the owner of this reservation
                        if (!Helpers::is_admin() && $reservation->user->id !== Helpers::get_logged_in_user()->id) {
                            // Return 403
                            $message = Helpers::json_encode(['error' => 'Forbidden', 'code' => 403, 'success' => false]);
                            Helpers::http_response_code(403, $message);
                            exit;
                        }
                        // Return reservation as JSON
                        $message = Helpers::json_encode(['reservation' => $reservation, 'success' => true]);
                        Helpers::http_response_code(200, $message);
                    } else {
                        // Return 404
                        $message = Helpers::json_encode(['error' => 'Reservation not found', 'code' => 404, 'success' => false]);
                        Helpers::http_response_code(404, $message);
                        exit;
                    }
                } else {
                    // If user is not admin, get reservations of logged in user
                    if (!Helpers::is_admin()) {
                        // Get reservations of logged in user
                        $reservations = ReservationModel::getAllByUserId(Helpers::get_logged_in_user()->id);
                    } else {
                        // Get all reservations
                        $reservations = ReservationModel::getAll();
                    }
                    $message = Helpers::json_encode(['reservations' => $reservations, 'success' => true]);
                    Helpers::http_response_code(200, $message);
                    exit;
                }
            } else if (Helpers::is_post() || Helpers::is_put() || Helpers::is_patch()) {

                // Get Reservation data from POST data
                $data = $this->getPostBody();

                // If data is empty, return 400
                if (empty($data)) {
                    $message = Helpers::json_encode(['error' => 'Invalid POST data', 'code' => 400, 'success' => false]);
                    Helpers::http_response_code(400, $message);
                    exit;
                }

                // For patch and put requests: Reservation id is required
                //  Validate the reservation id before adding it to the data array
                if (Helpers::is_put() || Helpers::is_patch()) {
                    $this->validateReservationId($id);
                    $data['id'] = $id;
                }

                // The set user must be the owner of the reservation
                if (Helpers::is_put() || Helpers::is_patch()) {
                    if (isset($reservation->user_id)) {
                        $this->validateReservationOwner($reservation);
                    }
                }

                // Create a new Reservation model instance from the data
                $reservation = new ReservationModel($data);

                // Validate all required reservation fields
                $errors = Helpers::is_patch() ? $this->validateReservation($reservation, false) : $this->validateReservation($reservation, true);

                if (!empty($errors)) {
                    // Return 400
                    $message = Helpers::json_encode(['error' => 'Invalid POST data', 'code' => 400, 'success' => false, 'errors' => $errors]);
                    Helpers::http_response_code(400, $message);
                    exit;
                }

                // Is user eligible to make a new reservation?
                if (Helpers::is_post()) {
                    if (!ReservationModel::isUserEligibleForReservation($reservation->user_id)) {
                        // Return 403
                        $message = Helpers::json_encode(['error' => 'User has an active, pending or non-expired reservation', 'code' => 403, 'success' => false]);
                        Helpers::http_response_code(403, $message);
                        exit;
                    }
                }


                // Save the Reservation in the database
                try {
                    $success = $reservation->save();
                    if ($success) {
                        // Add location header to response for the new Reservation
                        if (Helpers::is_post()) {
                            $insert_id = ReservationModel::getInsertId();
                            Helpers::add_location_header(URL_ROOT . '/api/reservations/' . $insert_id);
                            $reservation = ReservationModel::getOneById($insert_id);
                            $message = Helpers::json_encode(['reservation' => $reservation, 'success' => true]);
                            Helpers::http_response_code(201, $message);
                        } else if (Helpers::is_put() || Helpers::is_patch()) {
                            $reservation = ReservationModel::getOneById($id);
                            $message = Helpers::json_encode(['reservation' => $reservation, 'success' => true]);
                            Helpers::http_response_code(200, $message);
                        }
                    } else {
                        // Return 500
                        $message = Helpers::json_encode(['error' => 'Internal Server Error', 'code' => 500, 'success' => false]);
                        Helpers::http_response_code(500, $message);
                    }
                    exit;
                } catch (Exception $e) {
                    // Return 400
                    $message = Helpers::json_encode(['error' => 'Invalid POST data', 'code' => 400, 'success' => false]);
                    Helpers::http_response_code(400, $message);
                    Helpers::log_error($e->getMessage());
                }
            } else if (Helpers::is_delete()) {
                // Reservation id is required for a delete request
                $this->validateReservationId($id);

                // Get the Reservation
                $reservation = ReservationModel::getOneById($id);

                // Is the current user the owner of the reservation?
                $this->validateReservationOwner($reservation);

                // Delete the Reservation
                try {
                    $success = $reservation->delete();
                    if ($success) {
                        // Return 204
                        $message = Helpers::json_encode(['success' => true]);
                        Helpers::http_response_code(204, $message);
                    } else {
                        // Return 500
                        $message = Helpers::json_encode(['error' => 'Internal Server Error', 'code' => 500, 'success' => false]);
                        Helpers::http_response_code(500, $message);
                    }
                    exit;
                } catch (Exception $e) {
                    // Return 400
                    $message = Helpers::json_encode(['error' => 'Invalid POST data', 'code' => 400, 'success' => false]);
                    Helpers::http_response_code(400, $message);
                    Helpers::log_error($e->getMessage());
                }
            } else {
                // Return 405
                $message = Helpers::json_encode(['error' => 'Method Not Allowed', 'code' => 405, 'success' => false]);
                Helpers::http_response_code(405, $message);
                exit;
            }
        } else {
            Helpers::redirect_to(URL_ROOT . '/');
        }
    }

    private function getPostBody(): array
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
            // Return 403
            $message = Helpers::json_encode(['error' => 'Forbidden', 'code' => 403, 'success' => false]);
            Helpers::http_response_code(403, $message);
            exit;
        }
    }
}