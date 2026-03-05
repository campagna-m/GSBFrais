<?php helper('form'); ?>

<div id="sousContenu">

    <div class="corpsForm">

    </div>

    <div class="piedForm">
        <p>
            <?= form_open('remboursement/maj_etat_fiches_rembourse') ?>

            <?= form_hidden('visiteur', $selection_visiteur_fichefrais_va) ?>
            <?= form_hidden('annee', $annee) ?>
            <?= form_hidden('mois', $mois) ?>
            
            <?= form_submit('btnRembourser', 'Rembourser', ['class' => 'bouton']) ?>
            <?= form_close() ?>
        </p>
    </div>

</div>
