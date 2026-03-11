<?php helper('form');
$validation = session('validation');
?>

<div id="sousContenu">
    <?php
    echo form_open(site_url('motdepasse/valider'));
    ?>
    <div class="corpsForm">

        <p>
            <?= form_label('Nouveau mdp*', 'nouveauMdp') ?>
            <?= form_password([
                'name' => 'nouveauMdp',
                'id' => 'nouveauMdp',
                'maxlength' => 45,
                'size' => 15
            ]) ?>
            <?php if (isset($validation) && $validation->hasError('nouveauMdp')): ?>
                <span class="erreurSaisie"><?= esc($validation->getError('nouveauMdp')) ?></span>
            <?php endif; ?>
        </p>

        <p>
            <?= form_label('Confirmer le mdp*', 'confirmMdp') ?>
            <?= form_password([
                'name' => 'confirmMdp',
                'id' => 'confirmMdp',
                'maxlength' => 45,
                'size' => 15
            ]) ?>
            <?php if (isset($validation) && $validation->hasError('confirmMdp')): ?>
                <span class="erreurSaisie"><?= esc($validation->getError('confirmMdp')) ?></span>
            <?php endif; ?>
        </p>
    </div>

    <div class="piedForm">
        <p>
            <?= form_submit('btnValider', 'Valider', ['class' => 'bouton']) ?>
            <?= form_reset('btnEffacer', 'Effacer', ['class' => 'bouton']) ?>
        </p>
    </div>

    <?= form_close(); ?>
    
</div>