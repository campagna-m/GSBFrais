<?php helper('form');
?>

<div id="sousContenu">

    <?= form_open('validation/corriger_fraisforfait') ?>

    <div class="corpsForm">
        <?php foreach ($fraisforfait as $unff): ?>
            <p>
                <?= form_label($unff['libelle'] . ' : ', 'nb' . $unff['idfraisforfait']) ?>
                <?= form_input([
                    'type' => 'number',
                    'name' => 'lesFrais[' . $unff['idfraisforfait'] . ']',
                    'id' => 'nb' . $unff['idfraisforfait'],
                    'maxlength' => '4',
                    'size' => '4',
                    'min' => '0',
                    'max' => '1000',
                    'value' => old('lesFrais[' . $unff['idfraisforfait'] . ']', $unff['quantite']),
                    'style' => 'text-align: right'
                ]) ?>
            </p>
        <?php endforeach; ?>
    </div>

    <div class="piedForm">
        <p>
            <?= form_submit('btnCorriger', 'Corriger', ['class' => 'bouton']) ?>
        </p>
    </div>

    <?= form_close() ?>

</div>