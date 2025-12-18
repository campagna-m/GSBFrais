<?php

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */
$routes->get('/', 'Connexion::login');
$routes->get('connexion', 'Connexion::login');
$routes->post('/connexion/valider', 'Connexion::valider');
$routes->get('/connexion/deconnexion', 'Connexion::deconnexion');

$routes->get('/accueil', 'Accueil::index');

$routes->get('gererfrais', 'Gererfrais::index');
$routes->post('gererfrais/maj_fraisforfait', 'Gererfrais::valider_maj_fraisforfait');
$routes->post('gererfrais/creation_fraishorsforfait', 'Gererfrais::valider_creation_fraishorsforfait');
$routes->get('gererfrais/supp_fraishorsforfait/(:num)', 'Gererfrais::supprimer_fraishorsforfait/$1');

$routes->get('etatfrais', 'Etatfrais::index');
$routes->post('etatfrais/mois', 'Etatfrais::selectionner_mois');

$routes->get('remboursement', 'Remboursement::index');


$routes->post('remboursement/selectionner_visiteur', 'Remboursement::selectionner_visiteur');
$routes->post('remboursement/selectionner_mois', 'Remboursement::selectionner_mois');
$routes->post('remboursement/maj_etat_fiches_mois_rembourse', 'Remboursement::maj_etat_fiches_mois_rembourse');

$routes->get('validation', 'Validation::index');
