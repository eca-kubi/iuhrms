<?php
declare(strict_types=1);

class UserModelSchema
{
    public const ID = 'id';
    public const FIRST_NAME = 'first_name';
    public const LAST_NAME = 'last_name';
    public const EMAIL = 'email';
    public const IS_ADMIN = 'is_admin';
    public const CREATED_AT = 'created_at';
    public const UPDATED_AT = 'updated_at';
    const TABLE_NAME = 'users';
}
