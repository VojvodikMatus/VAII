<?php

namespace App\Models;

use Exception;
use Framework\Core\Model;

class Clinic extends Model
{
    protected ?int $id = null;
    protected ?string $name = null;
    protected ?string $street = null;
    protected ?string $city = null;
    protected ?string $postalCode = null;
    protected ?string $country = null;
    protected ?string $createdAt = null;
    protected ?string $updatedAt = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(?string $name): void
    {
        $this->name = $name;
    }

    public function getStreet(): ?string
    {
        return $this->street;
    }

    public function setStreet(?string $street): void
    {
        $this->street = $street;
    }

    public function getCity(): ?string
    {
        return $this->city;
    }

    public function setCity(?string $city): void
    {
        $this->city = $city;
    }

    public function getPostalCode(): ?string
    {
        return $this->postalCode;
    }

    public function setPostalCode(?string $postalCode): void
    {
        $this->postalCode = $postalCode;
    }

    public function getCountry(): ?string
    {
        return $this->country;
    }

    public function setCountry(?string $country): void
    {
        $this->country = $country;
    }

    public function getCreatedAt(): ?string
    {
        return $this->createdAt;
    }

    public function getUpdatedAt(): ?string
    {
        return $this->updatedAt;
    }

    public function getFullAddress(): ?string
    {
        return trim($this->name . ', ' . $this->street . ', ' . $this->city);
    }

    /**
     * Lekári, ktorí na klinike ordinujú, vzťah 1:N cez doctors.clinic_id.
     *
     * @return Doctor[]
     * @throws Exception
     */
    public function getDoctors(): array
    {
        return $this->getAllRelated(Doctor::class);
    }

    public function save(): void
    {
        $now = date('Y-m-d H:i:s');
        $this->createdAt ??= $now;
        $this->updatedAt = $now;
        parent::save();
    }
}