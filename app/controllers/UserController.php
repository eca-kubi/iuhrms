<?php

use JetBrains\PhpStorm\NoReturn;

class UserController extends Controller
{

    public function __construct()
    {
        parent::__construct();
    }

    #[NoReturn] public function index(): void
    {
        // Redirect to SPA login page if user is not logged in else redirect to dashboard
        if (!Helpers::is_logged_in()) {
            Helpers::redirectTo(URL_ROOT . '/#/user/login');
        } else {
            Helpers::redirectTo(URL_ROOT . '/#/user/dashboard');
        }
    }

    public function dashboard(): void
    {
        // Redirect to SPA login page if user is not logged in else render dashboard view
        if (!Helpers::is_logged_in()) {
            Helpers::redirectTo(URL_ROOT . '/#/user/login');
        } else {
            $viewModel = $this->loadViewModel();
            $viewModel->title = APP_NAME;
            $viewModel->page = 'dashboard';
            //Todo: Add user to view model. Create a dummy user for now
            $user = UserModel::factory([
                UserModelSchema::ID => 1,
                UserModelSchema::EMAIL => 'jdoe@iu.org',
                UserModelSchema::FIRST_NAME => 'John',
                UserModelSchema::LAST_NAME => 'Doe',
                UserModelSchema::IS_ADMIN => true,
            ]);
            $viewModel->user = $user;
            $this->view('user/dashboard', $viewModel);
        }
    }

    public function login(): void
    {
      /*  // If user is already logged in, and request is not ajax, redirect to dashboard using the SPA route
        if (Helpers::is_logged_in() && !Helpers::is_ajax()) {
            Helpers::redirectTo(URLROOT . '/#/user/dashboard');
        }

        // If user is not logged in, and request is not ajax, redirect to home
        if (!Helpers::is_logged_in() && !Helpers::is_ajax()) {
            Helpers::redirectTo(URLROOT . '/#/user/login');
        }*/


        // This is an AJAX request. No view is loaded.

        // Redirect to home page if request is not AJAX
        if (!Helpers::is_ajax()) {
            Helpers::redirectTo(URL_ROOT . '/#/user/login');
        }

        header('Content-Type: application/json');
        try {
            // Get recipient email from POST data
            $email = Helpers::fetch_post_data('email');
            // Call login helper to request OTP
            Helpers::login($email, verify_otp: false);

        } catch (Exception $e) {
            echo json_encode(array('success' => false, 'message' => 'An error occurred!'));
        }
    }

    #[NoReturn] public function logout(): void
    {
        // If user is not logged in, redirect to login page
        if (!Helpers::is_logged_in()) {
            Helpers::redirectTo(URL_ROOT . '/#/user/login');
        }

        try {
            // Destroy session
            Helpers::destroy_session();
            // Redirect to home page
            Helpers::redirectTo(URL_ROOT . '/#/user/login');
        } catch (Exception $e) {
            // log error
            Helpers::log_error($e);
            // Redirect to home page
            Helpers::redirectTo(URL_ROOT . '/#/user/login');
        }
    }

    public function submitOTP(): void
    {
        // This is an AJAX endpoint. No view is loaded.
        // if user is already logged in, redirect to dashboard
       /* if (Helpers::is_logged_in() && !Helpers::is_ajax()) {
            Helpers::redirectTo(URLROOT . '/#/user/dashboard');
        }

        // if user is not logged in,and request is not ajax, redirect to login page
        if (!Helpers::is_logged_in() && !Helpers::is_ajax()) {
            Helpers::redirectTo(URLROOT . '/#/user/login');
        }*/

        // Redirect to home page if request is not AJAX
        if (!Helpers::is_ajax()) {
            Helpers::redirectTo(URL_ROOT . '/#');
        }

        // This is an AJAX request. No view is loaded.
        header('Content-Type: application/json');
        try {
            // Get email from session
            $email = Helpers::fetch_from_session('email');
            // Call login helper to verify OTP and log user in
            Helpers::login($email, verify_otp: true);
        } catch (Exception $e) {
            echo json_encode(array('success' => false, 'message' => 'An error occurred!'));
        }
    }

    public function isAdmin(): void
    {
        // This is an AJAX endpoint. No view is loaded.
        // Redirect to errors/index/404 page if request is not AJAX
        if (!Helpers::is_ajax()) {
            Helpers::redirectTo(URL_ROOT . '/errors/index/404');
        }
        try {
            // Check if current user is admin. The user should be logged in
            if (Helpers::is_logged_in()) {
                $email = Helpers::fetch_from_session('email');
                $user = UserModel::getUserByEmail($email);
                if ($user->role == 'admin') {
                    echo json_encode(array('success' => true, 'message' => 'User is admin'));
                } else {
                    echo json_encode(array('success' => false, 'message' => 'User is not admin'));
                }
            } else {
                echo json_encode(array('success' => false, 'message' => 'User is not logged in'));
            }
            return;
        } catch (Exception $e) {
            echo json_encode(array('success' => false, 'message' => 'An error occurred!'));
        }
    }

    public function isLoggedIn(): void
    {
        // This is an AJAX endpoint. No view is loaded.
        // Redirect to errors/index/404 page if request is not AJAX
        if (!Helpers::is_ajax()) {
            Helpers::redirectTo(URL_ROOT . '/errors/index/404');
        }
        try {
            // Check if current user is logged in
            if (Helpers::is_logged_in()) {
                echo json_encode(array('success' => true, 'message' => 'User is logged in'));
            } else {
                echo json_encode(array('success' => false, 'message' => 'User is not logged in'));
            }
            return;
        } catch (Exception $e) {
            echo json_encode(array('success' => false, 'message' => 'An error occurred!'));
        }
    }
    protected function loadViewModel(): UserDashboardViewModel
    {
        return new UserDashboardViewModel();
    }
}