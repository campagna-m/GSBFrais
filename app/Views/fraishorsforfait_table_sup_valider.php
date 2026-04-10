<h4>Descriptif des éléments hors forfait</h4>

<table class="listeLegere">
    <tr>
        <th class="date">Date</th>
        <th class="libelle">Libellé</th>
        <th class="montant">Montant</th>     
        <th class="action">&nbsp;</th>           
    </tr>

    <?php foreach($fraishorsforfait as $unfhf): ?>
        <tr>
            <td><?= esc($unfhf['dateFraisFR']) ?></td>
            <td class="libelle"><?= esc($unfhf['libelle']) ?></td>
            <td class="montant"><?= esc($unfhf['montantFormate']) ?></td>
            <td>
                <a href="<?= site_url('validation/refuser_fraishorsforfait/' . $unfhf['idLigneFHF']) ?>" 
                   onclick="return confirm('Voulez-vous vraiment refuser ce frais ?');">
                    <img src="<?= base_url('assets/images/delete.png') ?>" alt="Refuser" title="Refuser" />
                </a>
            </td>
        </tr>
    <?php endforeach; ?>
</table>