<?php
declare(strict_types=1);

abstract class AbstractModel
{
    abstract public static function factory(array $record): static;


    /**
     * @throws ReflectionException
     */
    abstract protected static function getFields(): array;

    /**
     * @throws ReflectionException
     * @throws Exception
     */
    abstract public static function insert(array $record): bool;


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

    abstract public static function getPrimaryKeyFieldName(): string;

    abstract protected static function getTableName(): string;
}