<?php
// Zoznam špecializácií – vytvorené s pomocou AI (Claude), upravené autorom

/** @var \App\Models\Specialization[] $specializations */
/** @var int[] $doctorCounts počet lekárov podľa id špecializácie */
/** @var string|null $flash */
/** @var \Framework\Support\LinkGenerator $link */
?>
    <div class="list-head">
        <h1>Špecializácie</h1>
        <a class="btn btn-primary" href="<?= $link->url('specialization.add') ?>">Pridať špecializáciu</a>
    </div>

<?php if ($flash !== null) : ?>
    <p class="flash" role="status"><?= e($flash) ?></p>
<?php endif ?>

<?php if (count($specializations) === 0) : ?>
    <p class="empty">Zatiaľ tu nie sú žiadne špecializácie.</p>
<?php else : ?>
    <div class="table-wrap">
        <table class="data-table">
            <thead>
            <tr>
                <th scope="col">Názov</th>
                <th scope="col">Popis</th>
                <th scope="col">Lekárov</th>
                <th scope="col">Stav</th>
                <th scope="col"><span class="sr-only">Akcie</span></th>
            </tr>
            </thead>
            <tbody>
            <?php foreach ($specializations as $specialization) : ?>
                <tr>
                    <td data-label="Názov"><strong><?= e($specialization->getName()) ?></strong></td>
                    <td data-label="Popis"><?= e($specialization->getDescription()) ?></td>
                    <td data-label="Lekárov"><?= $doctorCounts[$specialization->getId()] ?></td>
                    <td data-label="Stav">
                        <?php if ($specialization->isActive()) : ?>
                            <span class="badge">Aktívna</span>
                        <?php else : ?>
                            <span class="badge badge-off">Skrytá</span>
                        <?php endif ?>
                    </td>
                    <td>
                        <div class="row-actions">
                            <a class="btn" href="<?= $link->url('specialization.edit', ['id' => $specialization->getId()]) ?>">Upraviť</a>
                            <form method="post" action="<?= $link->url('specialization.delete') ?>"
                                  data-confirm="Naozaj chcete zmazať špecializáciu <?= e($specialization->getName()) ?>?">
                                <input type="hidden" name="id" value="<?= $specialization->getId() ?>">
                                <button type="submit" class="btn btn-danger">Zmazať</button>
                            </form>
                        </div>
                    </td>
                </tr>
            <?php endforeach ?>
            </tbody>
        </table>
    </div>
<?php endif ?>