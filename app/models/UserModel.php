<?php

declare(strict_types=1);

class UserModel extends Model
{
    public readonly int|null $id;
    public string $first_name;
    public string $last_name;

    public string $full_name;

    public string $email;
    public bool|int $is_admin; // It can be 1 0r 0 for true or false respectively
    protected datetime|string $created_at; // It can be datetime or date string
    protected datetime|string $updated_at; // It can be datetime or date string

    public function __construct(array $data)
    {
        parent::__construct($data);
        // Set the ID if it exists. ID is read-only and can't be set from outside the constructor
        $this->id = $data[UserModelSchema::ID] ?? null;
        // Call the createFromData method to set the properties
        $this->createFromData($data);
        // Set full name
        $this->full_name = $this->getFullName();
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

    /**
     * @return UserModel[]
     * @throws Exception
     */
    public static function getAllAdmins(): array
    {
        $db = Database::getDbh();
        $db->where(UserModelSchema::IS_ADMIN, 1);
        $users = $db->get(UserModel::getTableName());
        return array_map(fn($user) => new UserModel($user), $users);
    }

    public function getInitials(): string
    {
        return strtoupper($this->first_name[0] . $this->last_name[0]);
    }

    public function getFullName(): string
    {
        return $this->first_name . ' ' . $this->last_name;
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
