<?php

class UserDashboardViewModel extends ViewModel
{

    /**
     * @var ReservationModel[]
     */
    public array $reservations = [];

    public UserModel $user;
    /**
     * @var HostelModel[]
     */
    public array $hostels = [];

    /**
     * @var RoomTypeModel[]
     */
    public array $room_types = [];

    public function __construct(string $title = 'Dashboard' . ' | ' . APP_NAME , string $page = 'dashboard')
    {
        parent::__construct();
        $this->title = $title;
        $this->page = $page;
        $this->user = Helpers::get_logged_in_user();
        try {
            $this->reservations = ReservationModel::getReservationsByUserId($this->user->id);
            $this->hostels = HostelModel::getAll();
            $this->room_types = RoomTypeModel::getAll();
        } catch (Exception $e) {
            Helpers::log_error($e->getMessage());
        }
    }
}