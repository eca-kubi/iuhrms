<?php
declare(strict_types=1);

abstract class Model
{
    protected static ?MysqliDb $db = null;
    protected array $errors = [];

    public function __construct(array $data = [])
    {
        // Initialize the database connection
        if (static::$db === null) {
            static::$db = Database::getDbh();
        }
    }

    /**
     * @throws Exception
     */
    protected static function getTableName(): string
    {
        $schemaClass = static::class . 'Schema';
        if (!class_exists($schemaClass)) {
            throw new Exception("Schema class $schemaClass does not exist");
        }
        return $schemaClass::TABLE_NAME;
    }

    /**
     * This method returns all the fields in the table schema.
     * @throws ReflectionException
     */
    protected static function getFields(): array
    {
        $reflection = new ReflectionClass(static::class . 'Schema');
        // Get fields from the schema class excluding the table name
        return array_filter($reflection->getConstants(), fn($key) => $key !== 'TABLE_NAME', ARRAY_FILTER_USE_KEY);
    }

    /**
     * @return static[]
     * @throws Exception
     */
    public static function getAll(): array
    {
        $db = Database::getDbh();
        $records = $db->get(static::getTableName());
        return array_map(fn($record) => new static($record), $records);
    }

    /**++++
     *
     * -+
     * Returns the record as an array.
     * @throws ReflectionException
     */
    public function toArray(): array
    {
        $fields = static::getFields();
        $arr = [];
        foreach ($fields as $field) {
            $arr[$field] = $this->$field;
        }
        return $arr;
    }

    /**
     * This method returns the record with the given id.
     * @param int $id
     * @return static|null
     * @throws Exception
     */
    public static function getOneById(int $id): self|null {
        $db = Database::getDbh();
        $record = $db->where(static::getPrimaryKeyFieldName(), $id)->getOne(static::getTableName());
        if (!$record) {
            throw new Exception("No record found with id $id");
        }
        return new static($record);
    }

    public static function getPrimaryKeyFieldName(): string
    {
        $schemaClass = static::class . 'Schema';
        return $schemaClass::ID;
    }

    public function addError(string $field, string $message): void
    {
        if (!isset($this->errors[$field])) {
            $this->errors[$field] = [];
        }

        $this->errors[$field][] = $message;
    }

    public function getErrors(string $field = null): array
    {
        if ($field) {
            return $this->errors[$field] ?? [];
        }

        return $this->errors;
    }

    /**
     * @throws ReflectionException
     * @throws Exception
     */
    public function save(): bool|int
    {
        $db = Database::getDbh();
        $data = [];
        $reflectionClass = new ReflectionClass($this);
        $id = null;
        if (isset($this->{static::getPrimaryKeyFieldName()})) {
            $id = $this->{static::getPrimaryKeyFieldName()};
        }

        foreach (static::getFields() as $field) {
            $property = $reflectionClass->getProperty($field);
            if (!$property->isPublic() || $property->isStatic()) {
                continue; // Exclude non-public or static properties
            }

            // PHP 8.1 and later: Check for readonly property
            if (PHP_VERSION_ID >= 80100 && $property->isReadOnly()) {
                continue; // Exclude readonly properties
            }
            // If the field is initialized, add it to the data array
            if (isset($this->{$field})) {
                $data[$field] = $this->{$field};
            }
        }
        if (!empty($id)) {
           return $db->where(static::getPrimaryKeyFieldName(), $id)->update(static::getTableName(), $data);
        }
        return $db->insert(static::getTableName(), $data);
    }

    /**
     * @throws Exception
     */
    public static function getInsertId(): int|string
    {
        $db = Database::getDbh();
        return $db->getInsertId();
    }

    /**
     * Checks if a record with the given id exists.
     * @throws Exception
     */
    public static function exists(int $id): bool
    {
        $db = Database::getDbh();
        return $db->where(static::getPrimaryKeyFieldName(), $id)->has(static::getTableName());
    }

    protected abstract function createFromData(array $data): static;
}

