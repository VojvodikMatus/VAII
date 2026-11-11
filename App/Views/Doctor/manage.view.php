<?php
// Správa lekárov – vytvorené s pomocou AI (Claude)

/** @var array<array{doctor: \App\Models\Doctor, user: \App\Models\User, clinic: \App\Models\Clinic, specializations: \App\Models\Specialization[]}> $doctors */
/** @var string|null $flash */
/** @var string|null $flashError */
/** @var \Framework\Support\LinkGenerator $link */
?>
<div class="list-head">
    <h1>Správa lekárov</h1>
    <a class="btn btn-primary" href="<?= $link->url('doctor.add') ?>">Pridať lekára</a>
</div>

<?php if ($flash !== null) : ?>
    <p class="flash" role="status"><?= e($flash) ?></p>
<?php endif ?>
<?php if ($flashError !== null) : ?>
    <p class="flash flash-error" role="alert"><?= e($flashError) ?></p>
<?php endif ?>

<?php if (count($doctors) === 0) : ?>
    <p class="empty">Zatiaľ tu nie sú žiadni lekári.</p>
<?php else : ?>
    <div class="table-wrap">
        <table class="data-table">
            <thead>
            <tr>
                <th scope="col">Meno</th>
                <th scope="col">Špecializácie</th>
                <th scope="col">Klinika</th>
                <th scope="col">E-mail</th>
                <th scope="col"><span class="sr-only">Akcie</span></th>
            </tr>
            </thead>
            <tbody>
            <?php foreach ($doctors as $item) : ?>
                <tr>
                    <td data-label="Meno">
                        <a href="<?= $link->url('doctor.show', ['id' => $item['doctor']->getId()]) ?>"><strong><?= e($item['user']->getFullName()) ?></strong></a>
                    </td>
                    <td data-label="Špecializácie">
                        <?php foreach ($item['specializations'] as $specialization) : ?>
                            <span class="badge"><?= e($specialization->getName()) ?></span>
                        <?php endforeach ?>
                    </td>
                    <td data-label="Klinika"><?= e($item['clinic']->getName()) ?>, <?= e($item['clinic']->getCity()) ?></td>
                    <td data-label="E-mail"><?= e($item['user']->getEmail()) ?></td>
                    <td>
                        <div class="row-actions">
                            <a class="btn" href="<?= $link->url('doctor.edit', ['id' => $item['doctor']->getId()]) ?>">Upraviť</a>
                            <form method="post" action="<?= $link->url('doctor.delete') ?>"
                                  data-confirm="Naozaj chcete zmazať lekára <?= e($item['user']->getFullName()) ?>?">
                                <input type="hidden" name="id" value="<?= $item['doctor']->getId() ?>">
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
