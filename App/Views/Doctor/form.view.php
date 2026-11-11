<?php
// Formulár na pridanie a úpravu lekára – vytvorené s pomocou AI (Claude)

/** @var \App\Models\Doctor $doctor */
/** @var \App\Models\User $doctorUser používateľský účet lekára (nie $user – ten je rezervovaný pre prihláseného) */
/** @var \App\Models\Clinic[] $clinics */
/** @var \App\Models\Specialization[] $specializations */
/** @var int[] $selectedIds id zaškrtnutých špecializácií */
/** @var string[] $errors */
/** @var \Framework\Support\LinkGenerator $link */
?>
<h1><?= $doctor->getId() ? 'Upraviť lekára' : 'Pridať lekára' ?></h1>

<?php if (count($errors) > 0) : ?>
    <div class="form-errors" role="alert">
        <ul>
            <?php foreach ($errors as $error) : ?>
                <li><?= e($error) ?></li>
            <?php endforeach ?>
        </ul>
    </div>
<?php endif ?>

<form class="data-form" method="post" action="<?= $link->url('doctor.save') ?>">
    <input type="hidden" name="id" value="<?= $doctor->getId() ?>">

    <div class="form-row">
        <div class="field">
            <label for="firstName">Meno</label>
            <input type="text" id="firstName" name="firstName" maxlength="50" required value="<?= e($doctorUser->getFirstName()) ?>">
        </div>
        <div class="field">
            <label for="lastName">Priezvisko</label>
            <input type="text" id="lastName" name="lastName" maxlength="50" required value="<?= e($doctorUser->getLastName()) ?>">
        </div>
    </div>
    <div class="form-row">
        <div class="field">
            <label for="email">E-mail</label>
            <input type="email" id="email" name="email" maxlength="100" required value="<?= e($doctorUser->getEmail()) ?>">
        </div>
        <div class="field">
            <label for="phone">Telefón</label>
            <input type="tel" id="phone" name="phone" maxlength="20" placeholder="+421 900 123 456" value="<?= e($doctorUser->getPhone()) ?>">
        </div>
    </div>
    <div class="field">
        <label for="password">Heslo</label>
        <?php // heslo sa nikdy nevypisuje späť do formulára ?>
        <input type="password" id="password" name="password" minlength="8" autocomplete="new-password"
            <?= $doctorUser->getId() ? 'placeholder="Ponechajte prázdne, ak sa nemá meniť"' : 'required' ?>>
    </div>

    <div class="form-row">
        <div class="field">
            <label for="clinicId">Klinika</label>
            <select id="clinicId" name="clinicId" required>
                <option value="">Vyberte kliniku</option>
                <?php foreach ($clinics as $clinic) : ?>
                    <option value="<?= $clinic->getId() ?>" <?= $clinic->getId() === $doctor->getClinicId() ? 'selected' : '' ?>>
                        <?= e($clinic->getName()) ?>, <?= e($clinic->getCity()) ?>
                    </option>
                <?php endforeach ?>
            </select>
        </div>
        <div class="field">
            <label for="appointmentLength">Dĺžka termínu (minúty)</label>
            <input type="number" id="appointmentLength" name="appointmentLength" min="5" max="120" step="5" required
                   value="<?= $doctor->getAppointmentLength() ?>">
        </div>
    </div>

    <fieldset class="field">
        <legend>Špecializácie</legend>
        <div class="checkbox-list">
            <?php foreach ($specializations as $specialization) : ?>
                <div class="checkbox">
                    <?php // name končí [] – prehliadač odošle všetky zaškrtnuté hodnoty a post('specializations') vráti pole ?>
                    <input type="checkbox" id="spec-<?= $specialization->getId() ?>" name="specializations[]"
                           value="<?= $specialization->getId() ?>" <?= in_array($specialization->getId(), $selectedIds, true) ? 'checked' : '' ?>>
                    <label for="spec-<?= $specialization->getId() ?>">
                        <?= e($specialization->getName()) ?><?= $specialization->isActive() ? '' : ' (skrytá)' ?>
                    </label>
                </div>
            <?php endforeach ?>
        </div>
    </fieldset>

    <div class="field">
        <label for="bio">Krátky popis</label>
        <textarea id="bio" name="bio" rows="4" maxlength="1000"
                  placeholder="Napríklad prax, zameranie, jazyky"><?= e($doctor->getBio()) ?></textarea>
    </div>

    <div class="form-actions">
        <button type="submit" class="btn btn-primary">Uložiť</button>
        <a class="btn" href="<?= $link->url('doctor.manage') ?>">Späť</a>
    </div>
</form>