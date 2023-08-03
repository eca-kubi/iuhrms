<?php

declare(strict_types=1);

class UserModel extends Model
{
    public int $id;
    public string $first_name;
    public string $last_name;
    public string $email;
    public  bool $is_admin;

    public function __construct()
    {
        parent::__construct();
    }
    public static function getPrimaryKeyFieldName(): string
    {
        return UserModelSchema::ID;
    }

    public static function getTableName(): string
    {
        return 'users';
    }

    public function hydrate(int $id): void
    {
        // TODO: Implement hydrate() method.
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
