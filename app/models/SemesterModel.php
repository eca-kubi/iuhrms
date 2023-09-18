<?php

class SemesterModel extends Model
{
    public int|null $id;
    public string $name;
    public datetime|string $semester_start;
    public datetime|string $semester_end;
    protected datetime|string $created_at; // It can be datetime or date string
    protected datetime|string $updated_at; // It can be datetime or date string

    public function __construct(array $data)
    {
        parent::__construct($data);
        // Set the ID if it exists. ID is read-only and can't be set from outside the constructor
        $this->id = $data[SemesterModelSchema::ID] ?? null;

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