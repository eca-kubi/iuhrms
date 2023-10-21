<?php

use JetBrains\PhpStorm\NoReturn;

class LoginController extends Controller
{

    public function __construct()
    {
        parent::__construct();
    }

    #[NoReturn] public function index(): void
    {
        // Redirect to SPA login page if user is not logged in else redirect to dashboard
        if (!Helpers::is_logged_in()) {
            Helpers::redirect_to(URL_ROOT . '/#/user/login');
        } else {
            Helpers::redirect_to(URL_ROOT . '/#/user/dashboard');
        }
    }

    public function dashboard(): void
    {
        // Redirect to SPA login page if user is not logged in else render dashboard view
        if (!Helpers::is_logged_in()) {
            Helpers::redirect_to(URL_ROOT . '/#/user/login');
        } else {
            $viewModel = $this->loadViewModel();
            $viewModel->title = APP_NAME;
            $viewModel->page = 'dashboard';
            // Get logged in user
            $viewModel->user = Helpers::get_logged_in_user();
            // If user is an admin, redirect to admin dashboard
            if ($viewModel->user->isAdmin()) {
                Helpers::redirect_to(URL_ROOT . '/admin/dashboard');
            }

            $this->view('user/dashboard', $viewModel);
        }
    }

    public function getOTP(): void
    {

        // Redirect to spa get-otp page if request is not AJAX
        if (!Helpers::is_ajax()) {
            Helpers::redirect_to(URL_ROOT . '#/login/get-otp');
        }

        try {
            // Get recipient email from POST data
            $email = Helpers::fetch_post_data('email');

            // Check if email exists in database
            if (!UserModel::emailExists($email)) {
                Helpers::sendJsonResponse(200, ['success' => false, 'message' => 'Email does not exist!']);
            }

            // Add email to session
            Helpers::add_to_session(SessionKeys::EMAIL, $email);

            // Generate a new OTP
            $otp = Helpers::generate_otp();

            // Send otp to email
            Helpers::send_otp_to_email($email, $otp);

            // Add encrypted otp to session
            Helpers::add_to_session(SessionKeys::OTP, Helpers::encrypt_otp($otp));

            // Add otp expiry to session. OTP expires in 5 minutes
            Helpers::add_to_session(SessionKeys::OTP_EXPIRY, time() + 5 * 60);

            // Send response to client

            Helpers::sendJsonResponse(200, ['success' => true, 'message' => 'OTP sent to email!']);
        } catch (Exception $e) {
            Helpers::log_error($e->getMessage());
            Helpers::sendJsonResponse(500, ['message' => 'A server side error has occurred', 'code' => 500, 'success' => false]);
        }
    }

    public function submitOTP(): void
    {

        try {
            // Get recipient email from POST data
            $email = Helpers::fetch_post_data('email');

            // Get email from session
            $email_from_session = Helpers::fetch_session_data(SessionKeys::EMAIL);

            // Check if email from session is same as email from POST data
            if ($email_from_session !== $email) {
                // Send an error response to client. Don't send 200
                Helpers::sendJsonResponse(400, ['success' => false, 'message' => 'Invalid email!']);
            }

            // Verify user submitted OTP
            // Get OTP from POST data
            $otp = Helpers::fetch_post_data('otp');

            // Check if OTP is valid
            if (!Helpers::verify_otp($otp)) {
                // Send an error response to client. Don't send 200
                Helpers::sendJsonResponse(400, ['success' => false, 'message' => 'Invalid OTP!']);
            }

            // Get user details from database
            $user = UserModel::getUserByEmail($email);

            // Set is logged in to true
            Helpers::add_to_session(SessionKeys::LOGGED_IN_USER, $user);

            // Clear otp from session
            Helpers::remove_from_session(SessionKeys::OTP);

            // Clear otp expiry from session
            Helpers::remove_from_session(SessionKeys::OTP_EXPIRY);

            // Send response to client
            Helpers::sendJsonResponse(200, ['success' => true, 'message' => 'OTP verified!']);
        } catch (Exception $e) {
            Helpers::log_error($e->getMessage());
            Helpers::sendJsonResponse(500, ['message' => 'A server side error has occurred', 'code' => 500, 'success' => false]);
        }
    }


    protected function loadViewModel(): UserDashboardViewModel
    {
        return new UserDashboardViewModel();
    }
}