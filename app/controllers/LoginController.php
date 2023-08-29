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
        // This is an AJAX request. No view is loaded.

        // Redirect to spa get-otp page if request is not AJAX
        if (!Helpers::is_ajax()) {
            Helpers::redirect_to(URL_ROOT . '#/login/get-otp');
        }

        header('Content-Type: application/json');
        try {
            // Get recipient email from POST data
            $email = Helpers::fetch_post_data('email');
            // Call login helper to request OTP
            Helpers::login($email, verify_otp: false);

        } catch (Exception $e) {
            Helpers::log_error($e->getMessage());
            echo json_encode(array('success' => false, 'message' => 'An error occurred!'));
        }
    }

    public function submitOTP(): void
    {
        // This is an AJAX endpoint. No view is loaded.

        // Redirect to spa home page if request is not AJAX
        if (!Helpers::is_ajax()) {
            Helpers::redirect_to(URL_ROOT . '/#');
        }

        // This is an AJAX request. No view is loaded.
        header('Content-Type: application/json');
        try {
            // Get recipient email from POST data
            $email = Helpers::fetch_post_data('email');

            // Call login helper to verify OTP and log user in
            Helpers::login($email, verify_otp: true);
        } catch (Exception $e) {
            echo json_encode(array('success' => false, 'message' => 'An error occurred!'));
        }
    }

    protected function loadViewModel(): UserDashboardViewModel
    {
        return new UserDashboardViewModel();
    }
}