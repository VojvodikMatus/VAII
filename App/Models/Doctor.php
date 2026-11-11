<?php

namespace App\Models;

use Framework\Core\Model;

class Doctor extends Model
{
    protected ?int $id = null;
    protected ?int $userId = null;
    protected ?int $clinicId = null;
    protected ?string $bio = null;
    protected ?int $appointmentLength = 20;
    protected ?string $createdAt = null;
    protected ?string $updatedAt = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUserId(): ?int
    {
        return $this->userId;
    }

    public function setUserId(?int $userId): void
    {
        $this->userId = $userId;
    }

    public function getClinicId(): ?int
    {
        return $this->clinicId;
    }

    public function setClinicId(?int $clinicId): void
    {
        $this->clinicId = $clinicId;
    }

    public function getBio(): ?string
    {
        return $this->bio;
    }

    public function setBio(?string $bio): void
    {
        $this->bio = $bio;
    }

    public function getAppointmentLength(): ?int
    {
        return $this->appointmentLength;
    }

    public function setAppointmentLength(int $appointmentLength): void
    {
        $this->appointmentLength = $appointmentLength;
    }

    public function getCreatedAt(): ?string
    {
        return $this->createdAt;
    }

    public function getUpdatedAt(): ?string
    {
        return $this->updatedAt;
    }

    /**
     * Používateľský účet lekára (meno, e-mail…) cez stĺpec user_id.
     */
    public function getUser(): ?User
    {
        return $this->getOneRelated(User::class);
    }

    /**
     * Klinika, kde lekár ordinuje, cez stĺpec clinic_id.
     */
    public function getClinic(): ?Clinic
    {
        return $this->getOneRelated(Clinic::class);
    }

    /**
     * Špecializácie lekára – vzťah M:N cez tabuľku doctor_specializations
     *
     * @return Specialization[]
     */
    public function getSpecializations(): array
    {
        $specializations = [];
        foreach ($this->getAllRelated(DoctorSpecialization::class) as $link) {
            $specializations[] = $link->getOneRelated(Specialization::class);
        }
        return $specializations;
    }

    /**
     * @return WorkingHour[]
     */
    public function getWorkingHours(): array
    {
        return $this->getAllRelated(WorkingHour::class);
    }

    /**
     * @return DoctorAbsence[]
     */
    public function getAbsences(): array
    {
        return $this->getAllRelated(DoctorAbsence::class);
    }

    public function save(): void
    {
        $now = date('Y-m-d H:i:s');
        $this->createdAt ??= $now;
        $this->updatedAt = $now;
        parent::save();
    }
}