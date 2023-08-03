<?php

declare(strict_types=1);

/**
 * This is the schema for the emails table. It is used by the EmailModel class.
 */
final class EmailModelSchema
{
    public const ID = 'id';
    public const RECIPIENT_ADDRESS = 'recipient_address';
    public const SUBJECT = 'subject';
    public const BODY = 'body';
    public const SENT = 'sent';
    public const TABLE_NAME = 'emails';
}
