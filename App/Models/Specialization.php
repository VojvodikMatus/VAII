<?php

namespace App\Models;

use Framework\Core\Model;

class Specialization extends Model
{
    protected ?int $id = null;
    protected ?string $name = null;
    protected ?string $description = null;
    protected ?int $isActive = 1;          // stĺpec is_active, TINYINT(1): 1 = aktívna, 0 = skrytá
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

    public function setName(string $name): void
    {
        $this->name = $name;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): void
    {
        $this->description = $description;
    }

    public function isActive(): bool
    {
        return $this->isActive === 1;
    }

    public function setIsActive(bool $isActive): void
    {
        $this->isActive = $isActive ? 1 : 0;
    }

    /**
     * Počet lekárov s touto špecializáciou (riadky v doctor_specializations).
     */
    public function getDoctorCount(): int
    {
        return count($this->getAllRelated(DoctorSpecialization::class));
    }

    public function getCreatedAt(): ?string
    {
        return $this->createdAt;
    }

    public function getUpdatedAt(): ?string
    {
        return $this->updatedAt;
    }

    public function save(): void
    {
        $now = date('Y-m-d H:i:s');
        $this->createdAt ??= $now;
        $this->updatedAt = $now;
        parent::save();
    }
}