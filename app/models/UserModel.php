<?php

declare(strict_types=1);

class UserModel extends Model
{
    public readonly int|null $id;
    protected string $first_name;
    protected string $last_name;
    public string $email;
    public bool|int $is_admin; // It can be 1 0r 0 for true or false respectively
    protected datetime|string $created_at; // It can be datetime or date string
    protected datetime|string $updated_at; // It can be datetime or date string

    public function __construct(array $data)
    {
        parent::__construct($data);
        // Set the ID if it exists. ID is read-only and can't be set from outside the constructor
        if (isset($data[UserModelSchema::ID])) {
            // Initialize readonly ID property
            if (isset($data[UserModelSchema::ID])) {
                $this->id = $data[UserModelSchema::ID] !== null ? (int)$data[UserModelSchema::ID] : null;
            }
        }
        // Call the createFromData method to set the properties
        $this->createFromData($data);
    }

    /**
     * @throws Exception
     */
    public static function emailExists(string $email): bool
    {
        $db = Database::getDbh();
        $db->where(UserModelSchema::EMAIL, $email);
        return $db->has(UserModel::getTableName());
    }

    /**
     * @throws Exception
     */
    public static function getUserByEmail(string $email): UserModel
    {
        $db = Database::getDbh();
        $db->where(UserModelSchema::EMAIL, $email);
        $user = $db->getOne(UserModel::getTableName());
        if (!$user) {
            throw new Exception('User not found');
        }
        return new UserModel($user);
    }

    public function getInitials(): string
    {
        return strtoupper($this->first_name[0] . $this->last_name[0]);
    }

    public function getFullName(): string
    {
        return $this->first_name . ' ' . $this->last_name;
    }

    public function getFirstName(): string
    {
        return $this->first_name;
    }

    public function getLastName(): string
    {
        return $this->last_name;
    }

    public function setFirstName(string $first_name): void
    {
        if (!empty($first_name)) {
            $this->first_name = $first_name;
        } else {
            throw new InvalidArgumentException('First name cannot be empty');
        }
    }

    public function setLastName(string $last_name): void
    {
        if (!empty($last_name)) {
            $this->last_name = $last_name;
        } else {
            throw new InvalidArgumentException('Last name cannot be empty');
        }
    }

    public function isAdmin(): bool
    {
        return (bool)$this->is_admin;
    }

    /**
     * @param array $data
     * @return $this
     */
    protected function createFromData(array $data): static
    {
        // Hydrate the instance with the data. ID is the primary key and is not settable outside the constructor, thus making it effectively read-only
        foreach ($data as $key => $value) {
            if ($key !== UserModelSchema::ID && property_exists($this, $key)) {
                $this->{$key} = $value;
            }
        }
        return $this;
    }

    /**
     * @return UserModelValidator
     */
    public function getValidator(): UserModelValidator
    {
        return new UserModelValidator($this);
    }

    /**
     * @param array $data
     * @return void
     */
    protected function validateData(array $data): void
    {


    }
}
