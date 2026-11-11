<?php
// Výpočet voľných termínov lekára – vytvorené s pomocou AI (Claude)

namespace App\Helpers;

use App\Models\Appointment;
use App\Models\Doctor;
use App\Models\WorkingHour;
use DateTimeImmutable;
use DateTimeZone;

/**
 * Z ordinačných hodín, neprítomností a už rezervovaných termínov vypočíta voľné časy lekára.
 * Samostatná trieda preto, lebo výpočet bude potrebovať aj rezervácia (overenie, že zvolený čas je naozaj voľný).
 */
class DoctorSchedule
{
    /** Časové pásmo ordinácií – Docker kontajner beží v UTC, "teraz" preto počítame v slovenskom čase. */
    public const TIMEZONE = 'Europe/Bratislava';

    /**
     * Dnešný dátum (o polnoci) v slovenskom čase.
     */
    public static function today(): DateTimeImmutable
    {
        return new DateTimeImmutable('today', new DateTimeZone(self::TIMEZONE));
    }

    /**
     * Pondelok týždňa, ktorý je $weekOffset týždňov od aktuálneho (0 = tento týždeň, 1 = budúci …).
     */
    public static function mondayOfWeek(int $weekOffset): DateTimeImmutable
    {
        $today = self::today();
        // format('N') vráti 1 = pondelok … 7 = nedeľa, takže sa vrátime o (N - 1) dní
        $monday = $today->modify('-' . ((int)$today->format('N') - 1) . ' days');
        return $monday->modify('+' . (7 * $weekOffset) . ' days');
    }

    /**
     * Voľné termíny lekára na 7 dní od zadaného pondelka.
     *
     * @return array<array{date: DateTimeImmutable, dayName: string, absence: ?string, slots: string[]}>
     */
    public static function getWeek(Doctor $doctor, DateTimeImmutable $monday): array
    {
        $sunday = $monday->modify('+6 days');
        $now = new DateTimeImmutable('now', new DateTimeZone(self::TIMEZONE));
        $length = $doctor->getAppointmentLength();

        // Všetky potrebné dáta načítame naraz, nie zvlášť pre každý deň
        $workingHours = $doctor->getWorkingHours();
        $absences = $doctor->getAbsences();
        // Len platné termíny – zrušené majú slot_taken = NULL a ich čas je opäť voľný
        $appointments = Appointment::getAll(
            '`doctor_id` = ? AND `appointment_date` BETWEEN ? AND ? AND `slot_taken` = 1',
            [$doctor->getId(), $monday->format('Y-m-d'), $sunday->format('Y-m-d')]
        );

        $days = [];
        for ($i = 0; $i < 7; $i++) {
            $date = $monday->modify("+$i days");
            $dateText = $date->format('Y-m-d');
            $dayName = WorkingHour::DAYS[(int)$date->format('N')];
            $day = ['date' => $date, 'dayName' => $dayName, 'absence' => null, 'slots' => []];

            // Minulé dni nemajú voľné termíny
            if ($date < self::today()) {
                $days[] = $day;
                continue;
            }

            // Dovolenka, PN… – v ten deň lekár neordinuje
            foreach ($absences as $absence) {
                if ($absence->covers($dateText)) {
                    $day['absence'] = $absence->getReason() ?? 'Neprítomný';
                }
            }
            if ($day['absence'] !== null) {
                $days[] = $day;
                continue;
            }

            // Obsadené časy v tento deň ako dvojice [začiatok, koniec] v minútach od polnoci
            $taken = [];
            foreach ($appointments as $appointment) {
                if ($appointment->getAppointmentDate() === $dateText) {
                    $taken[] = [self::toMinutes($appointment->getStartTime()), self::toMinutes($appointment->getEndTime())];
                }
            }

            // Ordinačné hodiny v tento deň rozsekáme na termíny dĺžky $length
            foreach ($workingHours as $hours) {
                if ($hours->getDayOfWeek() !== $dayName) {
                    continue;
                }
                $end = self::toMinutes($hours->getEndTime());
                for ($start = self::toMinutes($hours->getStartTime()); $start + $length <= $end; $start += $length) {
                    $slotStart = $date->setTime(intdiv($start, 60), $start % 60);
                    if ($slotStart <= $now) {
                        continue; // dnešný čas, ktorý už prešiel
                    }
                    if (!self::overlaps($start, $start + $length, $taken)) {
                        $day['slots'][] = $slotStart->format('H:i');
                    }
                }
            }
            sort($day['slots']); // ak má lekár v jeden deň viac blokov ordinačných hodín
            $days[] = $day;
        }

        return $days;
    }

    /**
     * Čas "07:30:00" (alebo "07:30") prevedie na počet minút od polnoci, napr. 450.
     */
    private static function toMinutes(string $time): int
    {
        [$hours, $minutes] = explode(':', $time);
        return (int)$hours * 60 + (int)$minutes;
    }

    /**
     * Či sa čas [start, end) prekrýva s niektorým obsadeným časom.
     * Dva intervaly sa prekrývajú, ak jeden začína skôr, než druhý skončí, a naopak.
     */
    private static function overlaps(int $start, int $end, array $taken): bool
    {
        foreach ($taken as [$takenStart, $takenEnd]) {
            if ($start < $takenEnd && $takenStart < $end) {
                return true;
            }
        }
        return false;
    }
}