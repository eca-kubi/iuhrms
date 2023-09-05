<?php

class RoomTypeModel extends Model
{
    public readonly int|null $id;
    public string $type;
    public float $price;
    protected datetime|string $created_at; // It can be datetime or date string
    protected datetime|string $updated_at; // It can be datetime or date string

    public function __construct(array $data = [])
    {
        parent::__construct($data);
        // Set the ID if it exists. ID is read-only and cannot be set from outside the constructor
        if (isset($data[RoomTypeModelSchema::ID])) {
            $this->id = $data[RoomTypeModelSchema::ID] !== null ? (int)$data[RoomTypeModelSchema::ID] : null;
        }
        // Call the createFromData method to hydrate the object with data
        $this->createFromData($data);
    }

    public static function getPrimaryKeyFieldName(): string
    {
        return RoomTypeModelSchema::ID;
    }

    /**
     * @param array $data
     * @return $this
     */
    protected function createFromData(array $data): static
    {
        // Set the properties from the data array
        foreach ($data as $key => $value) {
            if ($key !== RoomTypeModelSchema::ID && property_exists($this, $key)) {
                $this->$key = $value;
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