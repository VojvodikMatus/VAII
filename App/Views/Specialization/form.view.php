<?php
// Formulár na pridanie a úpravu špecializácie

/** @var \App\Models\Specialization $specialization */
/** @var string[] $errors */
/** @var \Framework\Support\LinkGenerator $link */
?>
<h1><?= $specialization->getId() ? 'Upraviť špecializáciu' : 'Pridať špecializáciu' ?></h1>

<?php if (count($errors) > 0) : ?>
    <div class="form-errors" role="alert">
        <ul>
            <?php foreach ($errors as $error) : ?>
                <li><?= e($error) ?></li>
            <?php endforeach ?>
        </ul>
    </div>
<?php endif ?>

<form class="data-form" method="post" action="<?= $link->url('specialization.save') ?>">
    <input type="hidden" name="id" value="<?= $specialization->getId() ?>">

    <div class="field">
        <label for="name">Názov</label>
        <input type="text" id="name" name="name" maxlength="100" required
               placeholder="Napríklad Kardiológ" value="<?= e($specialization->getName()) ?>">
    </div>
    <div class="field">
        <label for="description">Popis</label>
        <textarea id="description" name="description" rows="4" maxlength="1000"
                  placeholder="Čím sa lekár s touto špecializáciou zaoberá"><?= e($specialization->getDescription()) ?></textarea>
    </div>
    <div class="checkbox">
        <input type="checkbox" id="isActive" name="isActive" value="1" <?= $specialization->isActive() ? 'checked' : '' ?>>
        <label for="isActive">Aktívna (zobrazuje sa vo vyhľadávaní lekárov)</label>
    </div>

    <div class="form-actions">
        <button type="submit" class="btn btn-primary">Uložiť</button>
        <a class="btn" href="<?= $link->url('specialization.index') ?>">Späť</a>
    </div>
</form>