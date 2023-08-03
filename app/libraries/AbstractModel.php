<?php
declare(strict_types=1);

abstract class AbstractModel
{
    abstract public static function factory(array $record): static;

    /**
     * @throws Exception
     */
    abstract public static function has($column, $value): bool;

    /**
     * @throws Exception
     */
    abstract public static function hasWhere(array $columnValues): bool;

    /**
     * @throws ReflectionException
     */
    abstract protected static function getFields(): array;

    /**
     * @throws ReflectionException
     * @throws Exception
     */
    abstract public static function add(array $fieldValues): bool;


    /**
     * @throws Exception
     */
    abstract public static function getSingleWhere(string $whereProp, mixed $whereValue = 'DBNULL', string $operator = '=', string $cond = 'AND'): static;

    /**
     * @return static[]
     * @throws Exception
     */
    abstract public static function getMultiWhere(string $whereProp, mixed $whereValue = 'DBNULL', string $operator = '=', string $cond = 'AND'): array;

    /**
     * @throws Exception
     */
    abstract public static function getSingle(): static;

    /**
     * @return static[]
     * @throws Exception
     */
    abstract public static function getMulti(): array;

    /**
     * @throws Exception
     */
    abstract public static function updateWhere(array $tableData, string $whereProp, mixed $whereValue = 'DBNULL', string $operator = '=', string $cond = 'AND', ?int $numRows = null): bool;

    /**
     * @throws Exception
     */
    abstract public static function updateModel(Model $model): bool;

    abstract public static function getPrimaryKeyFieldName(): string;

    abstract public static function getTableName(): string;
}