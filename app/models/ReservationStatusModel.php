<?php

class ReservationStatusModel extends Model
{
    public const STATUS_CONFIRMED = 'Confirmed';
    public const STATUS_CANCELLED = 'Cancelled';
    public const STATUS_PENDING = 'Pending';

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
}