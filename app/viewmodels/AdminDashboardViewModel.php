<?php

class AdminDashboardViewModel extends ViewModel
{
    public int $pending_reservation_requests_count;

    public int $overstayed_occupants_count;

    /**
     * @var stdClass[]
     */
    public array $hostels;
    /**
     * @var stdClass[]
     */
    public array $recent_reservation_requests;
    /**
     * @var stdClass[]
     */
    public array $room_availability;
    /**
     * @var object[]
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