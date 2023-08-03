<?php
declare(strict_types=1);
class EmailModel extends Model
{
    public int $id;
    public string $recipient_address;
    public string $subject;
    public string $body;
    public bool $sent;

    public static function getPrimaryKeyFieldName(): string
    {
        return EmailModelSchema::ID;
    }

    public static function getTableName(): string
    {
        return EmailModelSchema::TABLE_NAME;
    }

    public function hydrate(int $id): void
    {
        // TODO: Implement hydrate() method.
    }
}
