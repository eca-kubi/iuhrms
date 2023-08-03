<?php

class HomeViewModel extends \ViewModel
{
    public UserModel|bool $currentUser;
    public string $redirectUrl;
    public string $title;
    public string $currentRole;
}