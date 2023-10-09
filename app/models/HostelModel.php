<?php

declare(strict_types=1);

class HostelModel extends Model
{
    public readonly int|null $id;
    public string $name;
    public string $description;
    public string $location;
    public int $total_rooms;
    public int $occupied_rooms;
    /**
     * @var RoomTypeModel[]
     */
    public array $room_types = [];
    protected datetime|string $created_at; // It can be datetime or date string
    protected datetime|string $updated_at; // It can be datetime or date string

    public function __construct(array $data = [])
    {
        parent::__construct($data);
        // Set the ID if it exists. ID is read-only and cannot be set from outside the constructor
        if (isset($data[HostelModelSchema::ID])) {
            // if id is not null, cast it to int
            if ($data[HostelModelSchema::ID] !== null) {
                $this->id = (int)$data[HostelModelSchema::ID];
            } else {
                $this->id = null;
            }
        }
        // Call the createFromData method to hydrate the object with data
        $this->createFromData($data);
    }

    public static function getPrimaryKeyFieldName(): string
    {
        return HostelModelSchema::ID;
    }

    /**
     * @param array $data
     * @return $this
     */
    protected function createFromData(array $data): static
    {
        // Set the properties from the data array
        foreach ($data as $key => $value) {
            if ($key === 'room_types' && is_array($value)) {
                // If the key is 'room_types', iterate through the array and create RoomTypeModel objects
                foreach ($value as $roomTypeData) {
                    $this->room_types[] = new RoomTypeModel($roomTypeData);
                }
            } elseif ($key !== HostelModelSchema::ID && property_exists($this, $key)) {
                $this->$key = $value;
            }
        }
        return $this;
    }

    /**
     * Returns all hostels
     * @return HostelModel[]
     * @throws Exception
     */
    public static function getAll(): array {
        $db = Database::getDbh();
        // We need to join the room_types table using the hostel_room_types table to get the room types for each hostel
        $db->join(HostelRoomTypeModel::getTableName(), HostelRoomTypeModel::getTableName() . '.' . HostelRoomTypeModelSchema::HOSTEL_ID . ' = ' . HostelModel::getTableName() . '.' . HostelModelSchema::ID);
        $db->join(RoomTypeModel::getTableName(), RoomTypeModel::getTableName() . '.' . RoomTypeModelSchema::ID . ' = ' . HostelRoomTypeModel::getTableName() . '.' . HostelRoomTypeModelSchema::ROOM_TYPE_ID);
        $db->groupBy(HostelModel::getTableName() . '.' . HostelModelSchema::ID);
        $hostels = $db->get(HostelModel::getTableName(), null, [
            HostelModel::getTableName() . '.' . HostelModelSchema::ID,
            HostelModel::getTableName() . '.' . HostelModelSchema::NAME,
            HostelModel::getTableName() . '.' . HostelModelSchema::DESCRIPTION,
            HostelModel::getTableName() . '.' . HostelModelSchema::LOCATION,
            HostelModel::getTableName() . '.' . HostelModelSchema::TOTAL_ROOMS,
            HostelModel::getTableName() . '.' . HostelModelSchema::OCCUPIED_ROOMS,
            'Group_concat('.  RoomTypeModel::getTableName() . '.' . RoomTypeModelSchema::ID .  ') AS room_types_ids',
        ]);
        $hostelModels = [];
        $roomTypes = RoomTypeModel::getAll();
        foreach ($hostels as $key => $hostel) {
            $hostelModels[] = new HostelModel($hostel);
            // Get the room types for the hostel
            $roomTypeIds = explode(',', $hostel['room_types_ids']);

            // array_filter will return an array with the same keys as the original array, so we need to reset the keys
            $hostelModels[$key]->room_types = array_values(array_filter($roomTypes, function ($roomType) use ($roomTypeIds) {
                return in_array($roomType->id, $roomTypeIds);
            }));
        }
        return $hostelModels;
    }

    /**
     * Returns a hostel by ID
     * @param int $id
     * @return HostelModel|null
     * @throws Exception
     */
    public static function getOneById(int $id): HostelModel|null
    {
        $db = Database::getDbh();
        // We need to join the room_types table using the hostel_room_types table to get the room types for each hostel
        $db->join(HostelRoomTypeModel::getTableName(), HostelRoomTypeModel::getTableName() . '.' . HostelRoomTypeModelSchema::HOSTEL_ID . ' = ' . HostelModel::getTableName() . '.' . HostelModelSchema::ID);
        $db->join(RoomTypeModel::getTableName(), RoomTypeModel::getTableName() . '.' . RoomTypeModelSchema::ID . ' = ' . HostelRoomTypeModel::getTableName() . '.' . HostelRoomTypeModelSchema::ROOM_TYPE_ID);
        $db->where(HostelModel::getTableName() . '.' . HostelModelSchema::ID, $id);
        $db->groupBy(HostelModel::getTableName() . '.' . HostelModelSchema::ID);
        $hostel = $db->getOne(HostelModel::getTableName(),  [
            HostelModel::getTableName() . '.' . HostelModelSchema::ID,
            HostelModel::getTableName() . '.' . HostelModelSchema::NAME,
            HostelModel::getTableName() . '.' . HostelModelSchema::DESCRIPTION,
            HostelModel::getTableName() . '.' . HostelModelSchema::LOCATION,
            HostelModel::getTableName() . '.' . HostelModelSchema::TOTAL_ROOMS,
            HostelModel::getTableName() . '.' . HostelModelSchema::OCCUPIED_ROOMS,
            'Group_concat(' . RoomTypeModel::getTableName() . '.' . RoomTypeModelSchema::ID . ') AS room_types_ids',
        ]);
        if ($hostel) {
            $hostelModel = new HostelModel($hostel);
            // Get the room types for the hostel
            $roomTypeIds = explode(',', $hostel['room_types_ids']);
            $roomTypes = RoomTypeModel::getAll();
            $hostelModel->room_types = array_values(array_filter($roomTypes, function ($roomType) use ($roomTypeIds) {
                return in_array($roomType->id, $roomTypeIds);
            }));
            return $hostelModel;
        }
        return null;
    }

    // Increase the occupied rooms by 1
    public function increaseOccupiedRooms(): void
    {
        $this->occupied_rooms++;
    }


    /**
     * @return HostelModelValidator
     */
    public function getValidator(): HostelModelValidator
    {
        return new HostelModelValidator($this);
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
