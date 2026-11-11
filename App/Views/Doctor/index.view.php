<?php
// Zoznam lekárov načítaný z databázy – vytvorené s pomocou AI (Claude), upravené autorom

/** @var array<array{doctor: \App\Models\Doctor, user: \App\Models\User, clinic: \App\Models\Clinic, specializations: \App\Models\Specialization[]}> $doctors */
/** @var \App\Models\Specialization[] $specializations */
/** @var \Framework\Support\LinkGenerator $link */
?>
<h1>Nájdite lekára a objednajte sa</h1>

<form class="search" action="#" role="search">
    <div class="field">
        <label for="meno">Meno lekára</label>
        <input type="search" id="meno" name="meno" placeholder="Napríklad Nováková">
    </div>
    <div class="field">
        <label for="specializacia">Špecializácia</label>
        <select id="specializacia" name="specializacia">
            <option value="">Všetky špecializácie</option>
            <?php foreach ($specializations as $specialization) : ?>
                <option value="<?= $specialization->getId() ?>"><?= e($specialization->getName()) ?></option>
            <?php endforeach ?>
        </select>
    </div>
    <div class="field">
        <label for="mesto">Mesto</label>
        <select id="mesto" name="mesto">
            <option value="">Všetky mestá</option>
            <option>Žilina</option>
            <option>Bratislava</option>
            <option>Košice</option>
        </select>
    </div>
    <button type="button" class="btn btn-primary">Hľadať</button>
</form>

<div class="filters" role="group" aria-label="Doplnkové filtre">
    <label class="sr-only" for="dostupnost">Dostupnosť</label>
    <select id="dostupnost"><option>Dostupnosť: tento týždeň</option><option>Dostupnosť: budúci týždeň</option></select>
    <label class="sr-only" for="zoradenie">Zoradenie</label>
    <select id="zoradenie"><option>Zoradiť: najbližší termín</option><option>Zoradiť: podľa mena</option></select>
    <label class="sr-only" for="volne">Voľné termíny</label>
    <select id="volne"><option>Len s voľným termínom</option><option>Všetci lekári</option></select>
</div>

<p class="results-count">Nájdených lekárov: <?= count($doctors) ?></p>

<?php if (count($doctors) === 0) : ?>
    <p class="empty">Zatiaľ tu nie sú žiadni lekári.</p>
<?php endif ?>

<ul class="doctor-list">
    <?php foreach ($doctors as $item) : ?>
        <li>
            <article class="doctor" aria-labelledby="lekar-<?= $item['doctor']->getId() ?>">
                <div class="doctor-info">
                    <span class="avatar" aria-hidden="true"><?= e($item['user']->getInitials()) ?></span>
                    <div>
                        <h2 id="lekar-<?= $item['doctor']->getId() ?>"><?= e($item['user']->getFullName()) ?></h2>
                        <p><?= e(implode(', ', array_map(fn($s) => $s->getName(), $item['specializations']))) ?></p>
                        <p><?= e($item['clinic']->getFullAddress()) ?></p>
                    </div>
                </div>
                <div class="week">
                    <p class="no-slots">Rozpis voľných termínov bude doplnený neskôr.</p>
                </div>
                <div class="doctor-action"><a class="btn" href="<?= $link->url('doctor.show', ['id' => $item['doctor']->getId()]) ?>">Zobraziť profil</a></div>
            </article>
        </li>
    <?php endforeach ?>
</ul>

<nav class="pagination" aria-label="Stránkovanie zoznamu lekárov">
    <ul>
        <li><a href="#" aria-label="Predchádzajúca strana">←</a></li>
        <li><a href="#" aria-current="page">1</a></li>
        <li><a href="#">2</a></li>
        <li><a href="#">3</a></li>
        <li><a href="#">4</a></li>
        <li><a href="#">5</a></li>
        <li><a href="#" aria-label="Nasledujúca strana">→</a></li>
    </ul>
</nav>