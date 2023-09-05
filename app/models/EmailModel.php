<?php
declare(strict_types=1);

class EmailModel extends Model
{
    public readonly int|null $id;
    public string $recipient_address;
    public string $subject;
    public string $body;
    public bool|int $sent; // It can be 1 0r 0 for true or false respectively
    protected datetime|string $created_at; // It can be datetime or date string
    protected datetime|string $updated_at; // It can be datetime or date string

    public function __construct(array $data)
    {
        parent::__construct($data);
        // Set the id if it exists
        if (isset($data[EmailModelSchema::ID])) {
            $this->id = (int)$data[EmailModelSchema::ID];
        } else {
            $this->id = null;
        }
        // Call the createFromData method to hydrate the object with data
        $this->createFromData($data);
    }

    /**
     * @param array $data
     * @return $this
     */
    protected function createFromData(array $data): static
    {
        // Hydrate the object with the data passed in except for the id
        foreach ($data as $key => $value) {
            if ($key !== EmailModelSchema::ID && property_exists($this, $key)) {
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
