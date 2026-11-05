<?php

namespace App\Models;

use Framework\Core\Model;

class Appointment extends Model
{
    public const STATUS_SCHEDULED = 'scheduled';
    public const STATUS_COMPLETED = 'completed';
    public const STATUS_CANCELLED_PATIENT = 'cancelled_patient';
    public const STATUS_CANCELLED_DOCTOR = 'cancelled_doctor';
    public const STATUS_PATIENT_DIDNT_COME = 'patient_didnt_come';

    protected ?int $id = null;
    protected ?int $doctorId = null;
    protected ?int $patientId = null;           // používateľ s rolou patient
    protected ?int $slotTaken = 1;              // 1 = čas obsadený, null = zrušený (mení sa len cez setStatus)
    protected ?string $appointmentDate = null;
    protected ?string $startTime = null;
    protected ?string $endTime = null;
    protected ?string $status = self::STATUS_SCHEDULED;

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

    public function getPatientId(): ?int
    {
        return $this->patientId;
    }

    public function setPatientId(int $patientId): void
    {
        $this->patientId = $patientId;
    }

    public function getAppointmentDate(): ?string
    {
        return $this->appointmentDate;
    }

    public function setAppointmentDate(string $appointmentDate): void
    {
        $this->appointmentDate = $appointmentDate;
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

    public function getStatus(): ?string
    {
        return $this->status;
    }

    public function isCancelled(): bool
    {
        return in_array($this->status, [self::STATUS_CANCELLED_PATIENT, self::STATUS_CANCELLED_DOCTOR], true);
    }

    public function setStatus(string $status): void
    {
        $this->status = $status;
        $this->slotTaken = $this->isCancelled() ? null : 1;
    }

    public function getDoctor(): ?Doctor
    {
        return $this->getOneRelated(Doctor::class);
    }

    public function getPatient(): ?User
    {
        return $this->getOneRelated(User::class, 'patient_id');
    }
}