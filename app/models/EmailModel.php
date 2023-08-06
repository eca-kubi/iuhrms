<?php
declare(strict_types=1);

class EmailModel extends Model
{
    public int $id;
    public string $recipient_address;
    public string $subject;
    public string $body;
    public bool|int $sent; // It can be 1 0r 0 for true or false respectively
    public datetime|string $created_at; // It can be datetime or date string
    public datetime|string $updated_at; // It can be datetime or date string

    public static function getPrimaryKeyFieldName(): string
    {
        return EmailModelSchema::ID;
    }
}
