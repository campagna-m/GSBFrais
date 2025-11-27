<?php
$session = session();
?>

<div id="menuGauche">
    <div id="infosUtil">
        <div id="user">
            <img src="<?= base_url('assets/images/UserIcon.png') ?>" alt="Utilisateur" />
        </div>
        <div id="infos">
            <h2><?= esc($session->get('prenom') . ' ' . $session->get('nom')) ?></h2>
            <h3><?= $session->get('libelleRole') ?></h3>
        </div>
        <ul class="menuList">
            <li>
                <?= anchor('connexion/deconnexion', 'Déconnexion', ['title' => 'Se déconnecter']) ?>
            </li>
        </ul>
    </div>

    <ul id="menuPrincipal" class="menuList">
        <li>
            <?= anchor('accueil', 'Accueil', ['title' => 'Accueil']) ?>
        </li>
        <!-- Session Visiteur Médical -->
        <?php if ($session->get('idRole') == 'VM') {
        ?>
            <li>
                <?= anchor('gererfrais', 'Saisie fiche de frais', ['title' => 'Saisie fiche de frais']) ?>
            </li>
            <li>
                <?= anchor('etatfrais', 'Mes fiches de frais', ['title' => 'Consultation de mes fiches de frais']) ?>
            </li>
        <?php
        }
        ?>
        <!-- Session Comptable -->
        <?php if ($session->get('idRole') == 'CO') {
        ?>
            <li>
                <?= anchor('validation', 'Valider fiches', ['title' => 'Valider les fiches de frais des visiteurs']) ?>
            </li>
            <li>
                <?= anchor('suivi', 'Rembourser fiches', ['title' => 'Consulter le suivi des fiches de frais']) ?>
            </li>
        <?php
        }
        ?> 
    </ul>
</div>