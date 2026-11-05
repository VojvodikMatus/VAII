<?php

namespace App\Models;

use Framework\Core\Model;

class WorkingHour extends Model
{
    protected static ?string $tableName = 'working_hours';

    protected ?int $id = null;
    protected ?int $doctorId = null;
    protected ?string $dayOfWeek = null;
    protected ?string $startTime = null;
    protected ?string $endTime = null;

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

    public function getDayOfWeek(): ?string
    {
        return $this->dayOfWeek;
    }

    public function setDayOfWeek(string $dayOfWeek): void
    {
        $this->dayOfWeek = $dayOfWeek;
    }

    public function getStartTime(): ?string
    {
        return $this->startTime;
    }

    public function setStartTime(string $startTime): void
    {
        $this->startTime = $startTime;
    }

    public function getEndTime(): ?string
    {
        return $this->endTime;
    }

    public function setEndTime(string $endTime): void
    {
        $this->endTime = $endTime;
    }
}