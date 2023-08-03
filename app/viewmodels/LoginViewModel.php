<?php

class LoginViewModel extends ViewModel
{

    public string $title = 'Login - ' . SITE_NAME;
    public string $email;

    public function __construct()
    {
    }
}