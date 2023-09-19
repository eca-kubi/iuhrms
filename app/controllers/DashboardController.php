<?php

use JetBrains\PhpStorm\NoReturn;

class DashboardController extends Controller
{
    public function __construct()
    {
        parent::__construct();
    }
    #[NoReturn]
    public function index(): void
    {
        // Is user logged in?
        if (!Helpers::is_logged_in()) {
            Helpers::redirect_to(URL_ROOT . '/');
        } else {
            // if user is an admin, redirect to admin dashboard
            if (Helpers::is_admin()) {
                Helpers::redirect_to(URL_ROOT . '/dashboard/admin');
            } else {
                Helpers::redirect_to(URL_ROOT . '/dashboard/user');
            }
        }
    }

    public function user(): void
    {
        // Is user logged in?
        if (!Helpers::is_logged_in()) {
            Helpers::redirect_to(URL_ROOT . '/');
        } else {
            $viewModel = $this->loadUserDashboardViewModel();
            // If user is an admin, redirect to admin dashboard
            if ($viewModel->user->isAdmin()) {
                Helpers::redirect_to(URL_ROOT . '/dashboard/admin');
            }
            $this->view('dashboard/user', $viewModel);
        }
    }

    public function admin(): void
    {
        // Is user logged in?
        if (!Helpers::is_logged_in()) {
            Helpers::redirect_to(URL_ROOT . '/');
        } else {
            $viewModel = $this->loadAdminDashboardViewModel();
            // If user is not an admin, redirect to user dashboard
            if (!$viewModel->user->isAdmin()) {
                Helpers::redirect_to(URL_ROOT . '/dashboard/user');
            }
            $this->view('dashboard/admin', $viewModel);
        }
    }

    #[NoReturn]
    public function logout(): void
    {
        // If user is not logged in, redirect to login page
        if (!Helpers::is_logged_in()) {
            Helpers::redirect_to(URL_ROOT);
        }

        try {
            // Destroy session
            Helpers::destroy_session();
            // Redirect to home page
            Helpers::redirect_to(URL_ROOT );
        } catch (Exception $e) {
            // log error
            Helpers::log_error($e);
            // Redirect to home page
            Helpers::redirect_to(URL_ROOT );
        }
    }

    private function loadUserDashboardViewModel(): UserDashboardViewModel
    {
        $viewModel = new UserDashboardViewModel();
        $viewModel->title = APP_NAME;
        $viewModel->page = 'dashboard';
        // Get logged in user
        $viewModel->user = Helpers::get_logged_in_user();

        return $viewModel;
    }

    private function loadAdminDashboardViewModel(): AdminDashboardViewModel
    {
        $viewModel = new AdminDashboardViewModel();
        $viewModel->title = APP_NAME . ' | Admin Dashboard';
        $viewModel->page = 'dashboard';
        // Get logged in user
        $viewModel->user = Helpers::get_logged_in_user();

        return $viewModel;
    }

}