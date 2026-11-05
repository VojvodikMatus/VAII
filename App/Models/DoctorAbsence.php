<?php

namespace App\Models;

use Framework\Core\Model;

class DoctorAbsence extends Model
{
    protected static ?string $tableName = 'doctor_absences';

    protected ?int $id = null;
    protected ?int $doctorId = null;
    protected ?string $dateFrom = null;
    protected ?string $dateTo = null;
    protected ?string $reason = null;

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

    public function getDateFrom(): ?string
    {
        return $this->dateFrom;
    }

    public function setDateFrom(string $dateFrom): void
    {
        $this->dateFrom = $dateFrom;
    }

    public function getDateTo(): ?string
    {
        return $this->dateTo;
    }

    public function setDateTo(string $dateTo): void
    {
        $this->dateTo = $dateTo;
    }

    public function getReason(): ?string
    {
        return $this->reason;
    }

    public function setReason(?string $reason): void
    {
        $this->reason = $reason;
    }

    /**
     * Či lekár v daný deň (formát "Y-m-d") neordinuje.
     */
    public function covers(string $date): bool
    {
        return $date >= $this->dateFrom && $date <= $this->dateTo;
    }
}