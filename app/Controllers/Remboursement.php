<?php

namespace App\Controllers;

use App\Models\GsbModel;
use App\Libraries\Gsb_lib;

class Remboursement extends BaseController
{
    protected $id_visiteur_selectionne;
    protected $annee_mois;
    private $id_fiche;

    protected $gsb_lib;
    protected $gsb_model;

    public function __construct()
    {
        helper(['url', 'form', 'html']);

        $this->gsb_lib = new Gsb_lib();
        $this->gsb_model = new GsbModel();
    }

    /** Méthode par défaut */
    public function index()
    {
        // Vérifie si l’utilisateur est connecté
        if (!session()->get('isLoggedIn')) {
            return redirect()->to('/');
        }

        $this->id_visiteur_selectionne = null;
        $this->annee_mois = null;
        $this->id_fiche = null;
        return $this->commun();
    }

    /** Sélection d’un visiteur */
    public function selectionner_visiteur()
    {
        $this->id_visiteur_selectionne = $this->request->getPost('lstVisiteur');
        return $this->commun();
    }

    /** Sélection d’un mois */
    public function selectionner_mois()
    {
        $this->annee_mois = $this->request->getPost('lstMois');
        return $this->commun();
    }

    /** Rembourse les fiches du visiteur */
    public function maj_etat_fiches_mois_rembourse()
    {
        $idVisiteur = $this->request->getPost('lstVisiteur');
        $anneeMois  = $this->request->getPost('lstMois');

        // Séparer année et mois
        $annee = substr($anneeMois, 0, 4);
        $mois  = substr($anneeMois, 4, 2);

        $this->gsb_model->maj_etat_fiches_mois_rembourse($idVisiteur, $annee, $mois);
        return $this->commun();
    }

    /** Traitement commun pour affichage de la page */
    private function commun()
    {
        echo view('structures/page_entete');
        echo view('structures/messages');
        echo view('sommaire');

        $data['titre'] = "Fiches de frais à rembourser";
        echo view('structures/contenu_entete', $data);

        // Liste des visiteurs
        $les_visiteurs = $this->gsb_model->get_tous_les_visiteurs();

        if (!$les_visiteurs) {
            return redirect()->back()->with('erreurs', "Aucun visiteur trouvé");
        }

        // Récupère le visiteur sélectionné depuis la session
        if ($this->id_visiteur_selectionne !== null) {
        } else {
            $this->id_visiteur_selectionne = session()->get('id_visiteur_selectionne');
        }

        // Si aucun visiteur sélectionné, prendre le premier visiteur de la liste
        if (!$this->id_visiteur_selectionne) {
            $this->id_visiteur_selectionne = $les_visiteurs[0]['idutilisateur'];
        }

        // Sauvegarde le visiteur sélectionné en session
        $idVisiteur = $this->id_visiteur_selectionne;
        session()->set('id_visiteur_selectionne', $idVisiteur);

        // Liste déroulante des visiteurs
        $options_visiteurs = [];
        foreach ($les_visiteurs as $un_visiteur) {
            $libelle = $un_visiteur['nom'] . " " . $un_visiteur['prenom'];
            $options_visiteurs[$un_visiteur['idutilisateur']] = $libelle;
        }

        $data_visiteur['lst_contenu'] = $options_visiteurs;
        $data_visiteur['lst_select'] = $this->id_visiteur_selectionne;
        $data_visiteur['lst_action'] = 'remboursement/selectionner_visiteur';
        $data_visiteur['lst_id'] = 'lstVisiteur';
        $data_visiteur['lst_label'] = 'Visiteur';
        $data_visiteur['sc_titre'] = 'Sélectionnez un visiteur :';
        echo view('structures/souscontenu_entete', $data_visiteur);
        echo view('liste_deroulante', $data_visiteur);
        echo view('structures/souscontenu_pied');

        // Liste des mois correspondants au visiteur sélectionné
        if ($this->id_visiteur_selectionne) {
            $les_anneemois = $this->gsb_model->get_les_mois_disponibles($this->id_visiteur_selectionne);
            // Si aucun mois sélectionné, prendre le premier disponible
            if (!$this->annee_mois) {
                $this->annee_mois = $les_anneemois[0]['anneemois'];
            }

            // Liste déroulante des mois
            $options_mois = [];
            foreach ($les_anneemois as $une_anneemois) {
                $libelle = $this->gsb_lib->get_nom_mois($une_anneemois['mois']) . " " . $une_anneemois['annee'];
                $options_mois[$une_anneemois['anneemois']] = $libelle;
            }
            $data_mois['lst_contenu'] = $options_mois;
            $data_mois['lst_select'] = $this->annee_mois;
            $data_mois['lst_action'] = 'remboursement/selectionner_mois';
            $data_mois['lst_id'] = 'lstMois';
            $data_mois['lst_label'] = 'Mois';
            $data_mois['sc_titre'] = 'Mois à sélectionner :';
            echo view('structures/souscontenu_entete', $data_mois);
            echo view('liste_deroulante', $data_mois);
            echo view('structures/souscontenu_pied');

            // Fiche sélectionnée
            $num_annee = $this->gsb_lib->get_annee_from_anneemois($this->annee_mois);
            $num_mois = $this->gsb_lib->get_mois_from_anneemois($this->annee_mois);
            $date_titre = $this->gsb_lib->get_nom_mois($num_mois) . " " . $num_annee;

            $data['sc_titre'] = 'Fiche de frais du mois de ' . $date_titre . ' :';

            echo view('structures/souscontenu_entete', $data);

            // Zone état
            $fiche = $this->gsb_model->get_id_ficheFrais($this->id_visiteur_selectionne, $num_annee, $num_mois);
            $this->id_fiche = $fiche['idFiche'];
            $detailFiche = $this->gsb_model->get_les_infos_ficheFrais($this->id_fiche);
            $detailFiche['dateModifFr'] = $this->gsb_lib->date_vers_francais($detailFiche['dateModif']);
            $detailFiche['montantFormate'] = $this->gsb_lib->format_montant($detailFiche['montantValide']);
            $data['fiche'] = $detailFiche;
            $data['idFiche'] = $this->id_fiche;
            echo view('etat_fiche', $data);

            // Frais forfait
            $data['fraisforfait'] = $this->gsb_model->get_les_frais_forfait($this->id_fiche);
            echo view('fraisforfait_table', $data);

            // Frais hors forfait
            $listeFraisHorsForfait = $this->gsb_model->get_les_frais_hors_forfait($this->id_fiche);
            foreach ($listeFraisHorsForfait as &$fraisHF) {
                $fraisHF['dateFraisFR'] = $this->gsb_lib->date_vers_francais($fraisHF['dateFrais']);
                $fraisHF['montantFormate'] = $this->gsb_lib->format_montant($fraisHF['montant']);
            }
            unset($fraisHF);
            $data['fraishorsforfait'] = $listeFraisHorsForfait;
            echo view('fraishorsforfait_table', $data);

            // Bouton rembourser
            $data_bouton['id_visiteur_selectionne'] = $this->id_visiteur_selectionne;
            $data_bouton['annee_mois'] = $this->annee_mois;
            echo view('bouton_rembourser', $data_bouton);

            echo view('structures/souscontenu_pied');

            // Pied de page
            echo view('structures/page_pied');
        }
    }
}
