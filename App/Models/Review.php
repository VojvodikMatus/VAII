<?php

namespace App\Models;

use Framework\Core\Model;

class Review extends Model
{
    protected ?int $id = null;
    protected ?int $appointmentId = null;
    protected ?int $rating = null;
    protected ?string $comment = null;
    protected ?string $createdAt = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getAppointmentId(): ?int
    {
        return $this->appointmentId;
    }

    public function setAppointmentId(int $appointmentId): void
    {
        $this->appointmentId = $appointmentId;
    }

    public function getRating(): ?int
    {
        return $this->rating;
    }

    public function setRating(int $rating): void
    {
        $this->rating = $rating;
    }

    public function getComment(): ?string
    {
        return $this->comment;
    }

    public function setComment(?string $comment): void
    {
        $this->comment = $comment;
    }

    public function getCreatedAt(): ?string
    {
        return $this->createdAt;
    }

    /**
     * Návšteva, ku ktorej hodnotenie patrí
     */
    public function getAppointment(): ?Appointment
    {
        return $this->getOneRelated(Appointment::class);
    }

    public function save(): void
    {
        $this->createdAt ??= date('Y-m-d H:i:s');
        parent::save();
    }
}