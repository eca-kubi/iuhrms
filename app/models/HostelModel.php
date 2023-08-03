<?php

declare(strict_types=1);

class HostelModel extends Model
{
    public static function getPrimaryKeyFieldName(): string
    {
        return HostelModelFields::ID;
    }

    public static function getTableName(): string
    {
        return 'hostels';
    }

    public static function create(array $data): ?HostelModel
    {
        $db = Database::getDbh();
        $id = $db->insert(HostelModel::getTableName(), $data);
        if ($id !== false) {
            return HostelModel::getHostelById($db->getInsertId());
        } else {
            return null;
        }
    }

    public static function read(int $id): ?HostelModel
    {
        return HostelModel::getHostelById($id);
    }

    public static function update(int $id, array $data): bool
    {
        $db = Database::getDbh();
        $db->where(HostelModelFields::ID, $id);
        return $db->update(HostelModel::getTableName(), $data) !== false;
    }

    public function delete(): bool
    {
        $db = Database::getDbh();
        $db->where(HostelModelFields::ID, $this->getId());
        return $db->delete(HostelModel::getTableName()) !== false;
    }

    public static function getHostelById(int $id): ?HostelModel
    {
        $db = Database::getDbh();
        $db->where(HostelModelFields::ID, $id);
        $result = $db->getOne(HostelModel::getTableName());
        if ($result !== null) {
            return HostelModel::factory($result);
        } else {
            return null;
        }
    }

    public static function getHostelByName(string $hostelName): ?HostelModel
    {
        $db = Database::getDbh();
        $db->where(HostelModelFields::NAME, $hostelName);
        $result = $db->getOne(HostelModel::getTableName());
        if ($result !== null) {
            return HostelModel::factory($result);
        } else {
            return null;
        }
    }

    /**
     * @return HostelModel[]
     */
    public static function getAllHostels(): array
    {
        $db = Database::getDbh();
        $results = $db->get(HostelModel::getTableName());
        $hostels = [];
        foreach ($results as $result) {
            $hostels[] = HostelModel::factory($result);
        }
        return $hostels;
    }


    public function save(): void
    {
        $db = Database::getDbh();
        $data = [
            HostelModelFields::NAME => $this->getHostelName(),
            HostelModelFields::TOTAL_ROOMS => $this->getTotalRooms(),
            HostelModelFields::ADDRESS => $this->getAddress(),
        ];
        if ($this->getId() === null) {
            $id = $db->insert(HostelModel::getTableName(), $data);
            if ($id !== false) {
                $this->setId($db->getInsertId());
            }
        } else {
            $db->where(HostelModelFields::ID, $this->getId());
            $db->update(HostelModel::getTableName(), $data);
        }
    }

    public function hydrate(int $id): void
    {
        $hostel = HostelModel::getHostelById($id);
        if ($hostel !== null) {
            $this->setId($hostel->getId());
            $this->setHostelName($hostel->getHostelName());
            $this->setTotalRooms($hostel->getTotalRooms());
            $this->setAddress($hostel->getAddress());
        }
    }

    public function getId(): ?int
    {
        return $this->{HostelModelFields::ID};
    }

    private function setId(int $id): void
    {
        $this->{HostelModelFields::ID} = $id;
    }

    public function getHostelName(): string
    {
        return $this->{HostelModelFields::NAME};
    }

    public function setHostelName(string $hostelName): void
    {
        $this->{HostelModelFields::NAME} = $hostelName;
    }

    public function getTotalRooms(): int
    {
        return $this->{HostelModelFields::TOTAL_ROOMS};
    }

    public function setTotalRooms(int $totalRooms): void
    {
        $this->{HostelModelFields::TOTAL_ROOMS} = $totalRooms;
    }

    public function getAddress(): string
    {
        return $this->{HostelModelFields::ADDRESS};
    }

    public function setAddress(string $address): void
    {
        $this->{HostelModelFields::ADDRESS} = $address;
    }

    public static function factory(array $record): static
    {
        return parent::factory($record);
    }

}
