<?php

class HostelRoomTypeModel extends Model
{

    public readonly int|null $id;
    public int $hostel_id;
    public int $room_type_id;

    public function __construct(array $data)
    {
        parent::__construct($data);
        // Set the id if it exists
        if (isset($data[HostelRoomTypeModelSchema::ID])) {
            $this->id = (int)$data[HostelRoomTypeModelSchema::ID];
        } else {
            $this->id = null;
        }
        // Call the createFromData method to hydrate the object with data
        $this->createFromData($data);
    }

    /**
     * Return the primary key field name.
     * @return string
     */
    public static function getPrimaryKeyFieldName(): string
    {
        return HostelRoomTypeModelSchema::ID;
    }

    /**
     * Create a new HostelRoomTypeModel instance from the given data.
     * @param array $data
     * @return $this
     */
    protected function createFromData(array $data): static
    {
        // Hydrate the object with the data passed in except for the id
        foreach ($data as $key => $value) {
            if ($key !== HostelRoomTypeModelSchema::ID && property_exists($this, $key)) {
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