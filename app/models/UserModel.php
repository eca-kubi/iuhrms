<?php

declare(strict_types=1);

class UserModel extends Model
{
    public int $id;
    public string $first_name;
    public string $last_name;
    public string $email;
    public bool|int $is_admin; // It can be 1 0r 0 for true or false respectively
    public datetime|string $created_at; // It can be datetime or date string
    public datetime|string $updated_at; // It can be datetime or date string

    public function __construct()
    {
        parent::__construct();
    }

    public static function getPrimaryKeyFieldName(): string
    {
        return UserModelSchema::ID;
    }

    public function getInitials(): string
    {
        return strtoupper($this->first_name[0] . $this->last_name[0]);
    }

    public function getFullName(): string
    {
        return $this->first_name . ' ' . $this->last_name;
    }
}
