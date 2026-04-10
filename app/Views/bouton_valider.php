<?php helper('form'); ?>

<div id="sousContenu">

    <div class="corpsForm">

    </div>

    <div class="piedForm">
        <p>
            <?= form_open('validation/maj_etat_fiches_valider') ?>

            <?= form_hidden('visiteur', $selection_visiteur_fichefrais_cl) ?>
            <?= form_hidden('annee', $annee) ?>
            <?= form_hidden('mois', $mois) ?>
            
            <?= form_submit('btnValider', 'Valider', ['class' => 'bouton']) ?>
            <?= form_close() ?>
        </p>
    </div>

</div>