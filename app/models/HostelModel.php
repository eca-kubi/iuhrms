<?php

declare(strict_types=1);

class HostelModel extends Model
{
    public int $id;
    public string $name;
    public string $description;
    public string $location;
    public int $total_rooms;
    public int $occupied_rooms;
    public datetime|string $created_at; // It can be datetime or date string
    public datetime|string $updated_at; // It can be datetime or date string

    public static function getPrimaryKeyFieldName(): string
    {
        return HostelModelSchema::ID;
    }

}
