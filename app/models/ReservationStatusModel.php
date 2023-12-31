<?php

class ReservationStatusModel extends Model
{
    public const PENDING = 'Pending'; // This is the default status when a new reservation is created
    public const CONFIRMED = 'Confirmed';
    public const REJECTED = 'Rejected';
    public const CANCELLED = 'Cancelled';

    public readonly int|null $id;
    public string $name;
    protected datetime|string $created_at; // It can be datetime or date string
    protected datetime|string $updated_at; // It can be datetime or date string

    public function __construct(array $data)
    {
        parent::__construct($data);
        // Set the ID if it exists. ID is read-only and can't be set from outside the constructor
        if (isset($data[ReservationStatusModelSchema::ID])) {
            $this->id = $data[ReservationStatusModelSchema::ID] !== null ? (int)$data[ReservationStatusModelSchema::ID] : null;
        }
        // Call the createFromData method to set the properties
        $this->createFromData($data);
    }

    protected function createFromData(array $data): static
    {
        // Hydrate the object with data. Exclude the readonly ID property
        foreach ($data as $key => $value) {
            if ($key !== ReservationStatusModelSchema::ID && property_exists($this, $key)) {
                $this->{$key} = $value;
            }
        }
        return $this;
    }

    /**
     * @throws Exception
     */
    public static function getStatusIdByName(string $name): int|null
    {
        $status = self::getOneByFieldName(ReservationStatusModelSchema::NAME, $name);
        return $status?->id;
    }

    /**
     * @return BaseModelValidator
     */
    public function getValidator(): BaseModelValidator
    {
        // TODO: Implement getValidator() method.
    }

    /**
     * @param array $data
     * @return void
     */
    protected function validateData(array $data): void
    {
        // TODO: Implement validateData() method.
    }
}