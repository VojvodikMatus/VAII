<?php

namespace App\Models;

use Framework\Core\Model;

class DoctorSpecialization extends Model
{
    protected static ?string $tableName = 'doctor_specializations';

    protected ?int $id = null;
    protected ?int $doctorId = null;
    protected ?int $specializationId = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getDoctorId(): ?int
    {
        return $this->doctorId;
    }

    public function setDoctorId(int $doctorId): void
    {
        $this->doctorId = $doctorId;
    }

    public function getSpecializationId(): ?int
    {
        return $this->specializationId;
    }

    public function setSpecializationId(int $specializationId): void
    {
        $this->specializationId = $specializationId;
    }
}