<?php helper('form'); ?>

<div id="sousContenu">

    <div class="corpsForm">

    </div>

    <div class="piedForm">
        <p>
            <?= form_open('remboursement/maj_etat_fiches_mois_rembourse') ?>
            <?= form_hidden('lstVisiteur', $id_visiteur_selectionne) ?>
            <?= form_hidden('lstMois', $annee_mois) ?>
            <?= form_submit('btnRembourser', 'Rembourser', ['class' => 'bouton']) ?>
            <?= form_close() ?>
        </p>
    </div>

</div>