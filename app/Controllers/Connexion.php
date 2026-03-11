<?php

namespace App\Controllers;

use App\Models\GsbModel;

class Connexion extends BaseController
{
    protected $gsb_model;

    public function __construct()
    {
        helper(['url', 'form']); // helpers URL et form

        $this->gsb_model = new GsbModel();
    }

    /**
     * Affiche l’écran de connexion
     */
    public function login()
    {
        return view('structures/page_entete')
            . view('structures/messages')
            . view('connexion')
            . view('structures/page_pied');
    }

    /**
     * Valide la saisie du formulaire de connexion
     */
    public function valider()
    {
        $reglesSaisie = [
            'txtLogin' => [
                'rules' => 'required|min_length[3]',
                'label' => 'Login'
            ],
            'pwdMdp' => [
                'rules' => 'required|min_length[3]',
                'label' => 'Mot de passe'
            ]
        ];

        if (!$this->validate($reglesSaisie)) {
            // Redirection avec input et validation
            return redirect()->back()->withInput()->with('validation', $this->validator);
        }

        $login = $this->request->getPost('txtLogin');
        $mdp = $this->request->getPost('pwdMdp');

        $utilisateur = $this->gsb_model->get_infos_utilisateur($login, $mdp);

        if ($utilisateur) {

            $infosMdp = $this->gsb_model->get_infos_mdp($utilisateur['idutilisateur']); // Récupère les infos du mot de passe en BDD

            $dateCreationMdp = date_create($infosMdp['dateCreationMdp']); // Transforme la date BDD en objet date
            $dateAujourdhui = date_create('now'); // Crée la date d'aujourd'hui

            $difference = $dateAujourdhui->diff($dateCreationMdp); // Soustrait les deux dates
            $differenceJours = $difference->days; // Récupère le nombre total de jours d'écart

            session()->set([
                'idutilisateur' => $utilisateur['idutilisateur'],
                'nom' => $utilisateur['nom'],
                'prenom' => $utilisateur['prenom'],
                'libelleRole' => $utilisateur['libelle'],
                'idRole' => $utilisateur['idRole'],
                'isLoggedIn' => true
            ]);

            if ($differenceJours >= 1) { 
                session()->set('force_changement_mdp', true);
                return redirect()->to('/motdepasse')->with('erreurs', 'Votre mot de passe a expiré, vous devez le changer.');
            }

            return redirect()->to('/accueil');
        }

        return redirect()->back()->withInput()->with('erreurs', 'Login ou mot de passe incorrect.');
    }

    /**
     * Déconnecte l’utilisateur
     */
    public function deconnexion()
    {
        session()->remove(['idutilisateur', 'nom', 'prenom', 'libelleRole', 'idRole', 'isLoggedIn']);
        return redirect()->to('/')->with('infos', 'Vous avez bien été déconnecté.');
    }
}
