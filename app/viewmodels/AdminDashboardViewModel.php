<?php

class AdminDashboardViewModel extends ViewModel
{
    public UserModel $user;

    public int $overstayed_occupants_count;

    public function __construct(string $title = 'Dashboard' . ' | ' . APP_NAME , string $page = 'dashboard')
    {
        parent::__construct();
        $this->title = $title;
        $this->page = $page;
        $this->user = Helpers::get_logged_in_user();
        $this->overstayed_occupants_count = ReservationModel::getOverstayedOccupantsCount();
    }
}