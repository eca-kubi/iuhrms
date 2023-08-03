<?php

use JetBrains\PhpStorm\NoReturn;

class AdminController extends Controller
{
    public function __construct()
    {
        parent::__construct();
    }

    #[NoReturn] public function index(): void
    {
        // Redirect to SPA login page if user is not logged in else redirect to dashboard
        if (!Helpers::is_logged_in()) {
            Helpers::redirectTo(URL_ROOT . '/#/admin/login');
        } else {
            Helpers::redirectTo(URL_ROOT . '/admin/dashboard');
        }
    }

    public function dashboard(): void
    {
        // Redirect to SPA login page if user is not logged in else render dashboard view
        if (!Helpers::is_logged_in()) {
            Helpers::redirectTo(URL_ROOT . '/#/admin/login');
        } else {
            $viewModel = $this->loadViewModel();
            $viewModel->title = APP_NAME;
            $viewModel->page = 'dashboard';
            // Todo: Add user to view model. Create a dummy user for now
            $user = UserModel::factory([
                UserModelSchema::ID => 1,
                UserModelSchema::EMAIL => 'jdoe@iu.org',
                UserModelSchema::FIRST_NAME => 'John',
                UserModelSchema::LAST_NAME => 'Doe',
                UserModelSchema::IS_ADMIN => true,
            ]);
            $viewModel->user = $user;
            $this->view('admin/dashboard', $viewModel);
        }

    }

    public function login(): void
    {

        // This is an AJAX endpoint. No view is loaded.

        // Redirect to home page if request is not AJAX
        if (!Helpers::is_ajax()) {
            Helpers::redirectTo(URL_ROOT . '/#/admin/login');
        }

        header('Content-Type: application/json');
        try {
            // Get recipient email from POST data
            $email = Helpers::fetch_post_data('email');

            // Todo: Check if user is admin
            /*if (!UserModel::isAdmin($email)) {
                echo json_encode(array('success' => false, 'message' => 'You are not authorized to access this page!'));
                return;
            }*/

            // Call login helper to request OTP
            Helpers::login(email: $email, verify_otp: false);

        } catch (Exception $e) {
            echo json_encode(array('success' => false, 'message' => 'An error occurred!'));
        }
    }

    #[NoReturn] public function logout(): void
    {
        // If user is not logged in, redirect to login page
        if (!Helpers::is_logged_in()) {
            Helpers::redirectTo(URL_ROOT . '/#/admin/login');
        }

        try {
            // Destroy session
            Helpers::destroy_session();
            // Redirect to home page
            Helpers::redirectTo(URL_ROOT . '/#/admin/login');
        } catch (Exception $e) {
            // log error
            Helpers::log_error($e);
            // Redirect to home page
            Helpers::redirectTo(URL_ROOT . '/#/admin/login');
        }
    }

    public function submitOTP(): void
    {
        // This is an AJAX endpoint. No view is loaded.

        // Redirect to home page if request is not AJAX
        if (!Helpers::is_ajax()) {
            Helpers::redirectTo(URL_ROOT . '/#');
        }

        header('Content-Type: application/json');

        try {
            $email = Helpers::fetch_from_session('email');
            // Call login helper to verify OTP and log user in
            Helpers::login(email: $email, verify_otp: true);
        } catch (Exception $e) {
            echo json_encode(array('success' => false, 'message' => 'An error occurred!'));
        }
    }

    protected function loadViewModel(): AdminDashboardViewModel
    {
        $viewmodel = new AdminDashboardViewModel();
        // Hostels

        // Hostels
        $viewmodel->hostels = [
            (object)[
                'id' => 1,
                'name' => 'Hostel 1',
                'available_rooms' => 1,
                'occupied_rooms' => 14,
                'total_rooms' => 15,
            ],
            (object)[
                'id' => 2,
                'name' => 'Hostel 2',
                'available_rooms' => 15,
                'occupied_rooms' => 0,
                'total_rooms' => 15,
            ],
            (object)[
                'id' => 3,
                'name' => 'Hostel 3',
                'available_rooms' => 10,
                'occupied_rooms' => 5,
                'total_rooms' => 15,
            ],
            (object)[
                'id' => 4,
                'name' => 'Hostel 4',
                'available_rooms' => 10,
                'occupied_rooms' => 5,
                'total_rooms' => 15,
            ],
        ];

        // Recent reservation requests
        $viewmodel->recent_reservation_requests = [
            (object)[
                'id' => 1,
                'hostel' => (object)[
                    'id' => 1,
                ],
                'room_type' => 'Single',
                'requester_name' => 'Requester 1',
                'check_in_date' => '2021-01-01',
                'check_out_date' => '2021-01-01',
                'status' => 'Pending',
                'reservation_request_date' => '2021-01-01 00:00:00',
            ],
            (object)[
                'id' => 2,
                'hostel' => (object)[
                    'id' => 2,
                ],
                'room_type' => 'Single',
                'requester_name' => 'Requester 2',
                'check_in_date' => '2021-01-01',
                'check_out_date' => '2021-01-01',
                'status' => 'Pending',
                'reservation_request_date' => '2021-01-01 00:00:00',
            ],
            (object)[
                'id' => 3,
                'hostel' => (object)[
                    'id' => 3,
                ],
                'room_type' => 'Single',
                'requester_name' => 'Requester 3',
                'check_in_date' => '2021-01-01',
                'check_out_date' => '2021-01-01',
                'status' => 'Pending',
                'reservation_request_date' => '2021-01-01 00:00:00',
            ],
            (object)[
                'id' => 4,
                'hostel' => (object)[
                    'id' => 4,
                ],
                'room_type' => 'Single',
                'requester_name' => 'Requester 4',
                'check_in_date' => '2021-01-01',
                'check_out_date' => '2021-01-01',
                'status' => 'Pending',
                'reservation_request_date' => '2021-01-01 00:00:00',
            ],
            (object)[
                'id' => 5,
                'hostel' => (object)[
                    'id' => 1,
                ],
                'room_type' => 'Single',
                'requester_name' => 'Requester 5',
                'check_in_date' => '2021-01-01',
                'check_out_date' => '2021-01-01',
                'status' => 'Pending',
                'reservation_request_date' => '2021-01-01 00:00:00',
            ],

        ];

        // All Reservation Requests
        $viewmodel->reservation_requests = [
            (object)[
                'id' => 1,
                'hostel' => (object)[
                    'id' => 1,
                ],
                'room_type' => 'Single',
                'requester_name' => 'Requester 1',
                'check_in_date' => '2021-01-01',
                'check_out_date' => '2021-01-01',
                'status' => 'Pending',
                'reservation_request_date' => '2021-02-01 00:00:00',
            ],
            (object)[
                'id' => 2,
                'hostel' => (object)[
                    'id' => 2,
                ],
                'room_type' => 'Single',
                'requester_name' => 'Requester 2',
                'check_in_date' => '2021-01-01',
                'check_out_date' => '2021-01-01',
                'status' => 'Pending',
                'reservation_request_date' => '2021-01-01 00:00:00',
            ],
            (object)[
                'id' => 3,
                'hostel' => (object)[
                    'id' => 3,
                ],
                'room_type' => 'Single',
                'requester_name' => 'Requester 3',
                'check_in_date' => '2021-01-01',
                'check_out_date' => '2021-01-01',
                'status' => 'Approved',
                'reservation_request_date' => '2021-03-01 00:00:00',
            ],
            (object)[
                'id' => 4,
                'hostel' => (object)[
                    'id' => 4,
                ],
                'room_type' => 'Single',
                'requester_name' => 'Requester 4',
                'check_in_date' => '2021-01-01',
                'check_out_date' => '2021-01-01',
                'status' => 'Approved',
                'reservation_request_date' => '2021-01-01 00:00:00',
            ],
            (object)[
                'id' => 5,
                'hostel' => (object)[
                    'id' => 1,
                ],
                'room_type' => 'Single',
                'requester_name' => 'Requester 5',
                'check_in_date' => '2021-01-01',
                'check_out_date' => '2021-01-01',
                'status' => 'Approved',
                'reservation_request_date' => '2021-01-01 00:00:00',
            ],
        ];

        // Pending reservation requests count
        $viewmodel->pending_reservation_requests_count = 10;

        // Overstayed occupants count
        $viewmodel->overstayed_occupants_count = 0;

        return $viewmodel;
    }
}