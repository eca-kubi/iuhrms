<?php

class RoomTypeModel extends Model
{
    public int $id;
    public string $name;
    public float $price;
    public datetime|string $created_at; // It can be datetime or date string
    public datetime|string $updated_at; // It can be datetime or date string

    public static function getPrimaryKeyFieldName(): string
    {
        return RoomTypeModelSchema::ID;
    }
}