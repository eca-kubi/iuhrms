<?php

class UserDashboardViewModel extends ViewModel
{

    /**
     * @var stdClass[]
     */
    public array $reservation_requests;

    public UserModel $user;

    public function __construct(string $title = 'Dashboard' . ' | ' . APP_NAME , string $page = 'dashboard')
    {
        parent::__construct();
        $this->title = $title;
        $this->page = $page;
    }
}