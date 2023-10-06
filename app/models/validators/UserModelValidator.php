<?php

class UserModelValidator extends BaseModelValidator
{

    public function validate(array $requiredFields=[]):bool
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