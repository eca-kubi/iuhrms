<?php
declare(strict_types=1);

abstract class Model extends AbstractModel
{
    public static string $table = '';
    protected static ?MysqliDb $db = null;

    public function __construct()
    {
        static::$db = Database::getDbh(); // MysqliDb
    }

    public static function factory(array $record): static
    {
        $myObj = new static();
        foreach ($record as $key => $value) {
            $myObj->{$key} = $value;
        }
        return $myObj;
    }

    /**
     * @throws Exception
     */
    public static function has($column, $value): bool
    {
        $db = Database::getDbh();
        return $db->where($column, $value)->has(static::$table);
    }

    /**
     * @throws Exception
     */
    public static function hasWhere(array $columnValues): bool
    {
        $db = Database::getDbh();
        foreach ($columnValues as $col => $value) {
            $db->where($col, $value);
        }
        return $db->has(static::$table);
    }

    /**
     * @throws ReflectionException
     */
    protected static function getFields(): array
    {
        $reflection = new ReflectionClass(static::class . 'Schema');
        return $reflection->getConstants();
    }

    /**
     * @throws ReflectionException
     * @throws Exception
     */
    public static function add(array $fieldValues): bool
    {
        $db = Database::getDbh();
        $fields = static::getFields();
        $data = [];
        foreach ($fieldValues as $field => $value) {
            if (in_array($field, $fields)) {
                $data[$field] = $value;
            }
        }
        return $db->insert(static::$table, insertData: $data);
    }

    /**
     * @throws Exception
     */
    public static function getSingleWhere(string $whereProp, mixed $whereValue = 'DBNULL', string $operator = '=', string $cond = 'AND'): static
    {
        $db = Database::getDbh();
        $record = $db->where($whereProp, $whereValue, $operator, $cond)->getOne(static::$table);
        return static::factory($record);
    }

    /**
     * @return static[]
     * @throws Exception
     */
    public static function getMultiWhere(string $whereProp, mixed $whereValue = 'DBNULL', string $operator = '=', string $cond = 'AND'): array
    {
        $db = Database::getDbh();
        $records = $db->where($whereProp, $whereValue, $operator, $cond)->get(static::$table);
        return array_map(fn($record) => static::factory($record), $records);
    }

    /**
     * @throws Exception
     */
    public static function getSingle(): static
    {
        $db = Database::getDbh();
        return static::factory($db->getOne(static::$table));
    }

    /**
     * @return static[]
     * @throws Exception
     */
    public static function getMulti(): array
    {
        $db = Database::getDbh();
        $records = $db->get(static::$table);
        return array_map(fn($record) => static::factory($record), $records);
    }

    /**
     * @throws Exception
     */
    public static function updateWhere(array $tableData, string $whereProp, mixed $whereValue = 'DBNULL', string $operator = '=', string $cond = 'AND', ?int $numRows = null): bool
    {
        $db = Database::getDbh();
        return $db->where($whereProp, $whereValue, $operator, $cond)->update(static::$table, $tableData, $numRows);
    }

    /**
     * @throws Exception
     */
    public static function updateModel(self $model): bool
    {
        return static::updateWhere((array)$model, static::getPrimaryKeyFieldName(), $model->{static::getPrimaryKeyFieldName()});
    }

    /**
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

    abstract public function hydrate(int $id): void;
}

