<?php
// Profil lekára s voľnými termínmi – vytvorené s pomocou AI (Claude)

/** @var \App\Models\Doctor $doctor */
/** @var \App\Models\User $doctorUser používateľský účet lekára */
/** @var \App\Models\Clinic $clinic */
/** @var \App\Models\Specialization[] $specializations */
/** @var \App\Models\WorkingHour[] $workingHours */
/** @var array<array{date: DateTimeImmutable, dayName: string, absence: ?string, slots: string[]}> $days */
/** @var int $week */
/** @var int $maxWeek */
/** @var \Framework\Support\LinkGenerator $link */

$firstDay = $days[0]['date'];
$lastDay = $days[6]['date'];
?>
<p class="back-link"><a href="<?= $link->url('doctor.index') ?>">← Späť na zoznam lekárov</a></p>

<section class="profile-head">
    <span class="avatar avatar-lg" aria-hidden="true"><?= e($doctorUser->getInitials()) ?></span>
    <div>
        <h1><?= e($doctorUser->getFullName()) ?></h1>
        <p class="profile-specs"><?= e(implode(', ', array_map(fn($s) => $s->getName(), $specializations))) ?></p>
        <?php if ($doctor->getBio() !== null) : ?>
            <p><?= e($doctor->getBio()) ?></p>
        <?php endif ?>
    </div>
</section>

<div class="profile-grid">
    <aside class="profile-card">
        <h2>Ambulancia</h2>
        <address>
            <strong><?= e($clinic->getName()) ?></strong><br>
            <?= e($clinic->getStreet()) ?><br>
            <?= e($clinic->getPostalCode()) ?> <?= e($clinic->getCity()) ?>
        </address>

        <h2>Ordinačné hodiny</h2>
        <?php if (count($workingHours) === 0) : ?>
            <p class="muted">Lekár zatiaľ nemá zadané ordinačné hodiny.</p>
        <?php else : ?>
            <dl class="hours">
                <?php foreach ($workingHours as $hours) : ?>
                    <dt><?= e($hours->getDayOfWeek()) ?></dt>
                    <dd><?= substr($hours->getStartTime(), 0, 5) ?> – <?= substr($hours->getEndTime(), 0, 5) ?></dd>
                <?php endforeach ?>
            </dl>
        <?php endif ?>
        <p class="muted">Dĺžka termínu: <?= $doctor->getAppointmentLength() ?> minút</p>
    </aside>

    <section class="profile-card">
        <div class="week-head">
            <h2>Voľné termíny</h2>
            <nav class="week-nav" aria-label="Výber týždňa">
                <?php if ($week > 0) : ?>
                    <a href="<?= $link->url('doctor.show', ['id' => $doctor->getId(), 'week' => $week - 1]) ?>" aria-label="Predchádzajúci týždeň">←</a>
                <?php else : ?>
                    <span aria-hidden="true">←</span>
                <?php endif ?>
                <span class="week-range"><?= $firstDay->format('j. n.') ?> – <?= $lastDay->format('j. n. Y') ?></span>
                <?php if ($week < $maxWeek) : ?>
                    <a href="<?= $link->url('doctor.show', ['id' => $doctor->getId(), 'week' => $week + 1]) ?>" aria-label="Nasledujúci týždeň">→</a>
                <?php else : ?>
                    <span aria-hidden="true">→</span>
                <?php endif ?>
            </nav>
        </div>

        <ul class="days days-week">
            <?php foreach ($days as $day) : ?>
                <li class="day">
                    <span class="day-name" title="<?= e($day['dayName']) ?>"><?= e(mb_substr($day['dayName'], 0, 2)) ?></span>
                    <span class="day-date"><?= $day['date']->format('j. n.') ?></span>
                    <?php if ($day['absence'] !== null) : ?>
                        <span class="none"><?= e($day['absence']) ?></span>
                    <?php elseif (count($day['slots']) === 0) : ?>
                        <span class="none">—</span>
                    <?php else : ?>
                        <?php foreach ($day['slots'] as $slot) : ?>
                            <span class="slot"><?= $slot ?></span>
                        <?php endforeach ?>
                    <?php endif ?>
                </li>
            <?php endforeach ?>
        </ul>
        <p class="muted">Rezervácia termínu bude dostupná po prihlásení.</p>
    </section>
</div>
