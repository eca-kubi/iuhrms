<?php
declare(strict_types=1);

abstract class Model extends AbstractModel
{
    protected static ?MysqliDb $db = null;

    public function __construct()
    {
        static::$db = Database::getDbh(); // MysqliDb
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
     * This is a factory method that will create a new instance of the class, and set the properties values from the record passed in.
     * @param array $record
     * @return static
     */
    public static function factory(array $record): static
    {
        $myObj = new static();
        foreach ($record as $key => $value) {
            // If the property exists, set it
            if (property_exists($myObj, $key)) {
                $myObj->{$key} = $value;
            }
        }
        return $myObj;
    }

    /**
     * This method inserts a record into the database.
     * @throws Exception
     */
    public static function insert(array $record): bool
    {
        $db = Database::getDbh();
        return $db->insert(static::getTableName(), $record);
    }

    /**
     * @throws ReflectionException
     * @throws Exception
     */
    public function save(): bool
    {
        $db = Database::getDbh();
        $data = [];
        foreach (static::getFields() as $field) {
            $data[$field] = $this->{$field};
        }
        // if the primary key is set, update the record
        if ($this->{static::getPrimaryKeyFieldName()}) {
            return $db->update(static::getTableName(), $data, static::getPrimaryKeyFieldName());
        }
        // otherwise, insert a new record
        return $db->insert(static::getTableName(), $data);
    }

    /**
     * This method returns all the fields in the table schema.
     * @throws ReflectionException
     */
    protected static function getFields(): array
    {
        $reflection = new ReflectionClass(static::class . 'Schema');
        return $reflection->getConstants();
    }

    /**
     * @throws Exception
     */
    public static function getSingleWhere(string $whereProp, mixed $whereValue = 'DBNULL', string $operator = '=', string $cond = 'AND'): static
    {
        $db = Database::getDbh();
        $record = $db->where($whereProp, $whereValue, $operator, $cond)->getOne(static::getTableName());
        return static::factory($record);
    }

    /**
     * @return static[]
     * @throws Exception
     */
    public static function getMultiWhere(string $whereProp, mixed $whereValue = 'DBNULL', string $operator = '=', string $cond = 'AND'): array
    {
        $db = Database::getDbh();
        $records = $db->where($whereProp, $whereValue, $operator, $cond)->get(static::getTableName());
        return array_map(fn($record) => static::factory($record), $records);
    }

    /**
     * @throws Exception
     */
    public static function getSingle(): static
    {
        $db = Database::getDbh();
        return static::factory($db->getOne(static::getTableName()));
    }

    /**
     * @return static[]
     * @throws Exception
     */
    public static function getMulti(): array
    {
        $db = Database::getDbh();
        $records = $db->get(static::getTableName());
        return array_map(fn($record) => static::factory($record), $records);
    }

    /**
     * @throws Exception
     */
    public static function updateWhere(array $tableData, string $whereProp, mixed $whereValue = 'DBNULL', string $operator = '=', string $cond = 'AND', ?int $numRows = null): bool
    {
        $db = Database::getDbh();
        return $db->where($whereProp, $whereValue, $operator, $cond)->update(static::getTableName(), $tableData, $numRows);
    }


    /**
     * @throws ReflectionException
     */
/*    public function toArray(): array
    {
        $fields = static::getFields();
        $arr = [];
        foreach ($fields as $field) {
            $arr[$field] = $this->$field;
        }
        return $arr;
    }*/

    /**
     * This method takes a primary key id and hydrates the model with the data from the database
     * @param int $id
     * @return void
     * @throws Exception
     */
    public function hydrate(int $id): void {
        $db = Database::getDbh();
        $record = $db->where(static::getPrimaryKeyFieldName(), $id)->getOne(static::getTableName());
        foreach ($record as $key => $value) {
            // If the property exists, set it
            if (property_exists($this, $key)) {
                $this->{$key} = $value;
            }
        }
    }
}

