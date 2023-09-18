<?php

class UserModelValidator extends BaseModelValidator
{

    public function validate(bool $isRequired = true): bool
    {
       // Todo: Implement validation
    }

    public function validateId(bool $isRequired = true): BaseModelValidator
    {
        return $this->validateModelId(
            $this->model->id ?? null,
            UserModelSchema::ID,
            [UserModel::class, 'exists'],
            $isRequired
        );
    }
}