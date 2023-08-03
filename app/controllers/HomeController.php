<?php

use JetBrains\PhpStorm\NoReturn;

class HomeController extends Controller
{
    public function __construct() {
        parent::__construct();
    }

    /**
     * Start Page
     * @return void
     */
    public function index(): void
    {
        $payload = new HomeViewModel();
        $payload->title = 'Welcome to ' . APP_NAME . '!';
        $this->view('home/index', $payload);
    }

    protected function loadViewModel(): ViewModel
    {
        // TODO: Implement loadViewModel() method.
    }
}
