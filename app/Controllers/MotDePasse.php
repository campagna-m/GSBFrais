<?php

namespace App\Controllers;

use App\Models\GsbModel;

class MotDePasse extends BaseController
{
    protected $gsb_model;

    public function __construct()
    {
        helper(['url', 'form']);
        $this->gsb_model = new GsbModel();
    }

    /** Méthode par défaut */
    public function index()
    {
        // Vérifie si l’utilisateur est connecté
        if (!session()->get('isLoggedIn')) {
            return redirect()->to('/');
        }

        return $this->commun();
    }

    /** Traitement commun pour affichage de la page */
    private function commun()
    {
        echo view('structures/page_entete');
        echo view('structures/messages');

        if (session()->get('force_changement_mdp')) {
            $data['titre'] = "Mot de passe expiré";
        } else {
            $data['titre'] = "Modification du mot de passe";
            echo view('sommaire');
        }

        echo view('structures/contenu_entete', $data);
        echo view('motdepasse_modification');
        echo view('structures/page_pied');
    }

    public function valider()
    {
        $reglesSaisie = [
            'ancienMdp' => [
                'rules' => 'required',
                'label' => 'Ancien mot de passe'
            ],
            'nouveauMdp' => [
                'rules' => 'required|min_length[12]|regex_match[/^(?=.*[A-Z])(?=.*[a-z])(?=.*[0-9])(?=.*[^A-Za-z0-9]).+$/]',
                'label' => 'Nouveau mot de passe',
                'errors' => [
                    'min_length'  => 'Le mot de passe doit contenir au moins 12 caractères.',
                    'regex_match' => 'Le mot de passe doit inclure au moins 1 majuscule, 1 minuscule, 1 chiffre et 1 caractère spécial.'
                ]
            ],
            'confirmMdp' => [
                'rules' => 'required|matches[nouveauMdp]',
                'label' => 'Confirmation du mot de passe',
                'errors' => [
                    'matches' => 'La confirmation ne correspond pas au nouveau mot de passe.'
                ]
            ]
        ];

        if (!$this->validate($reglesSaisie)) {
            // Redirection avec input et validation
            return redirect()->back()->withInput()->with('validation', $this->validator);
        }

        $idutilisateur = session()->get('idutilisateur');

        $ancienMdpSaisi = $this->request->getPost('ancienMdp');
        $infosMdpBDD = $this->gsb_model->get_infos_mdp($idutilisateur);

        // Vérifie si le mot de passe de la BDD est différent de l'ancien mot de passe saisi
        if ($infosMdpBDD['mdp'] !== $ancienMdpSaisi) {
            return redirect()->back()->withInput()->with('erreurs', 'L\'ancien mot de passe est incorrect.');
        }

        $nouveauMdp = $this->request->getPost('nouveauMdp');

        // Met à jour le mot de passe utilisateur
        $this->gsb_model->maj_motdepasse($idutilisateur, $nouveauMdp);

        // Supprime la session force_changement_mdp si elle existait
        if (session()->has('force_changement_mdp')) {
            session()->remove('force_changement_mdp');
        }

        return redirect()->to('/accueil')->with('infos', 'Votre mot de passe a bien été modifié.');
    }
}
