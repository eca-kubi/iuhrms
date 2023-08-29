<?php

class SemesterModel extends Model
{
    public int|null $id;
    public string $name;
    public datetime|string $start_date;
    public datetime|string $end_date;
    protected datetime|string $created_at; // It can be datetime or date string
    protected datetime|string $updated_at; // It can be datetime or date string

    public function __construct(array $data)
    {
        parent::__construct($data);
        // Set the ID if it exists. ID is read-only and can't be set from outside the constructor
        if (isset($data[SemesterModelSchema::ID])) {
            // Initialize readonly ID property
            if (isset($data[SemesterModelSchema::ID])) {
                $this->id = $data[SemesterModelSchema::ID] !== null ? (int)$data[SemesterModelSchema::ID] : null;
            }
        }
        // Call the createFromData method to set the properties
        $this->createFromData($data);
    }

    protected function createFromData(array $data): static
    {
        // Hydrate the object with data. Exclude the readonly ID property
        foreach ($data as $key => $value) {
            if ($key !== SemesterModelSchema::ID && property_exists($this, $key)) {
                if ($key === SemesterModelSchema::START_DATE || $key === SemesterModelSchema::END_DATE) {
                    // Convert date strings to datetime objects
                    $value = date_create($value);
                }
                $this->{$key} = $value;
            }
        }
        return $this;
    }

}