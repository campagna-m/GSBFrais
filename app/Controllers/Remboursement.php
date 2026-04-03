<?php

namespace App\Controllers;

use App\Models\GsbModel;
use App\Libraries\Gsb_lib;

class Remboursement extends BaseController
{
    private $selection_visiteur_fichefrais_va;
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

        $this->selection_visiteur_fichefrais_va = null;
        return $this->commun();
    }

    // Sélection des fiches via la liste déroulante
    public function selection_fiches_frais_va()
    {
        $this->selection_visiteur_fichefrais_va = $this->request->getPost('lstVisiteur');
        return $this->commun();
    }

    /** Rembourse les fiches du visiteur */
    public function maj_etat_fiches_mois_rembourse()
    {
        $id_visiteur = $this->request->getPost('visiteur');
        $annee = $this->request->getPost('annee');
        $mois = $this->request->getPost('mois');

        $this->gsb_model->maj_etat_fiches_rembourse($id_visiteur, $annee, $mois);

        return $this->commun();
    }

    /** Traitement commun pour affichage de la page */
    private function commun()
    {
        echo view('structures/page_entete');
        echo view('structures/messages');
        echo view('sommaire');

        $data['titre'] = 'Fiches de frais à rembourser';
        echo view('structures/contenu_entete', $data);

        // Liste des visiteurs VA
        $les_visiteurs = $this->gsb_model->get_visiteurs_fiches_etat("VA");

        if (!$les_visiteurs) {
            return redirect()->back()->with('erreurs', 'Aucun visiteur trouvé');
        }

        // Récupère le visiteur sélectionné depuis la session
        if ($this->selection_visiteur_fichefrais_va !== null) {
        } else {
            $this->selection_visiteur_fichefrais_va = session()->get('selection_visiteur_fichefrais_va');
        }

        // Si aucun visiteur sélectionné, prendre le premier visiteur de la liste
        if (!$this->selection_visiteur_fichefrais_va) {
            $this->selection_visiteur_fichefrais_va = $les_visiteurs[0]['idutilisateur'];
        }

        // Sauvegarde le visiteur sélectionné en session
        session()->set('selection_visiteur_fichefrais_va', $this->selection_visiteur_fichefrais_va);

        // Liste déroulante des visiteurs
        $options_visiteurs = [];
        foreach ($les_visiteurs as $un_visiteur) {
            $nom_mois = $this->gsb_lib->get_nom_mois($un_visiteur['mois']);
            $libelle = $un_visiteur['nom'] . " " . $un_visiteur['prenom'] . " - " . $nom_mois . " " . $un_visiteur['annee'];
            $options_visiteurs[$un_visiteur['idutilisateur']] = $libelle;
        }

        $data_visiteur['lst_contenu'] = $options_visiteurs;
        $data_visiteur['lst_select'] = $this->selection_visiteur_fichefrais_va;
        $data_visiteur['lst_action'] = 'remboursement/selection_fiches_frais_va';
        $data_visiteur['lst_id'] = 'lstVisiteur';
        $data_visiteur['lst_label'] = 'Visiteur';
        $data_visiteur['sc_titre'] = 'Sélectionnez une fiche de frais à rembourser :';

        echo view('structures/souscontenu_entete', $data_visiteur);
        echo view('liste_deroulante', $data_visiteur);
        echo view('structures/souscontenu_pied');

        $annee_selectionnee = null;
        $mois_selectionne = null;

        foreach ($les_visiteurs as $un_visiteur) {
            if ($un_visiteur['idutilisateur'] == $this->selection_visiteur_fichefrais_va) {
                $annee_selectionnee = $un_visiteur['annee'];
                $mois_selectionne = $un_visiteur['mois'];
                break; // Arrête la recherche dès que les infos sont trouvées
            }
        }

        // Zone état
        $fiche = $this->gsb_model->get_id_ficheFrais($this->selection_visiteur_fichefrais_va, $annee_selectionnee, $mois_selectionne);

        if ($fiche != null && !empty($fiche['idFiche'])) {
            $this->id_fiche = $fiche['idFiche'];
            $detail_fiche = $this->gsb_model->get_les_infos_ficheFrais($this->id_fiche);

            $detail_fiche['dateModifFr'] = $this->gsb_lib->date_vers_francais($detail_fiche['dateModif']);
            $detail_fiche['montantFormate'] = $this->gsb_lib->format_montant($detail_fiche['montantValide']);

            $data['fiche'] = $detail_fiche;
            $data['idFiche'] = $this->id_fiche;

            echo view('structures/souscontenu_entete', $data);

            // Affichage de l'état de la fiche
            echo view('etat_fiche', $data);

            // Frais forfait
            $data['fraisforfait'] = $this->gsb_model->get_les_frais_forfait($this->id_fiche);
            echo view('fraisforfait_table', $data);

            // Frais hors forfait
            $liste_frais_hf = $this->gsb_model->get_les_frais_hors_forfait($this->id_fiche);

            foreach ($liste_frais_hf as $cle => $frais_hf) {
                $liste_frais_hf[$cle]['dateFraisFR'] = $this->gsb_lib->date_vers_francais($frais_hf['dateFrais']);
                $liste_frais_hf[$cle]['montantFormate'] = $this->gsb_lib->format_montant($frais_hf['montant']);
            }

            $data['fraishorsforfait'] = $liste_frais_hf;
            echo view('fraishorsforfait_table', $data);

            // Bouton rembourser
            $data_bouton['selection_visiteur_fichefrais_va'] = $this->selection_visiteur_fichefrais_va;
            $data_bouton['annee'] = $annee_selectionnee;
            $data_bouton['mois'] = $mois_selectionne;
            echo view('bouton_rembourser', $data_bouton);

            echo view('structures/souscontenu_pied');

            // Pied de page
            echo view('structures/page_pied');
        }
    }
    
}