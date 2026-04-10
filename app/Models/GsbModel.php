<?php

namespace App\Models;

use CodeIgniter\Model;
use App\Libraries\Gsb_lib;

class GsbModel extends Model
{
    /** Retourne les informations d'un utilisateur */
    public function get_infos_utilisateur($login, $mdp)
    {
        return $this->db->table('infos_utilisateur')
            ->select('idutilisateur, nom, prenom, login, mdp, libelle, idRole')
            ->where('login', $login)
            ->where('mdp', $mdp)
            ->get()
            ->getRowArray();
    }

    /** Retourne les détails d'un utilisateur */
    public function get_detail_utilisateur($id)
    {
        return $this->db->table('utilisateur')
            ->where('idutilisateur', $id)
            ->get()
            ->getRowArray();
    }

    /** Mois disponibles pour un visiteur */
    public function get_les_mois_disponibles($idVisiteur)
    {
        return $this->db->table('fichefrais')
            ->select('CONCAT(annee,mois) AS "anneemois", annee, mois')
            ->where('idVisiteur', $idVisiteur)
            ->orderBy('annee', 'DESC')
            ->orderBy('mois', 'DESC')
            ->get()
            ->getResultArray();
    }

    /** Id fiche de frais pour une année et un mois */
    public function get_id_ficheFrais($idVisiteur, $annee, $mois)
    {
        return $this->db->table('fichefrais')
            ->select('fichefrais.idFiche, fichefrais.idEtat')
            ->where('fichefrais.idVisiteur', $idVisiteur)
            ->where('fichefrais.annee', $annee)
            ->where('fichefrais.mois', $mois)
            ->get()
            ->getRowArray();
    }

    /** Infos fiche de frais pour un mois */
    public function get_les_infos_ficheFrais($idFiche)
    {
        return $this->db->table('fichefrais')
            ->select('fichefrais.idFiche, fichefrais.idEtat, fichefrais.dateModif, fichefrais.nbJustificatifs, fichefrais.montantValide, etatfrais.libelle')
            ->join('etatfrais', 'fichefrais.idEtat = etatfrais.idetat')
            ->where('fichefrais.idFiche', $idFiche)
            ->get()
            ->getRowArray();
    }

    /** Frais forfait pour un mois */
    public function get_les_frais_forfait($idFiche)
    {
        return $this->db->table('lignefraisforfait')
            ->select('fraisforfait.idfraisforfait, fraisforfait.libelle, lignefraisforfait.quantite')
            ->join('fraisforfait', 'fraisforfait.idfraisforfait = lignefraisforfait.idFraisForfait')
            ->where('lignefraisforfait.idFiche', $idFiche)
            ->orderBy('lignefraisforfait.idFraisForfait', 'ASC')
            ->get()
            ->getResultArray();
    }

    /** Frais hors forfait pour un mois */
    public function get_les_frais_hors_forfait($idFiche)
    {
        return $this->db->table('lignefraishorsforfait')
            ->where('idFiche', $idFiche)
            ->get()
            ->getResultArray();
    }

    /** Vérifie si premier frais du mois */
    public function est_premier_frais_mois($idVisiteur, $annee, $mois)
    {
        $row = $this->db->table('fichefrais')
            ->select('count(*) AS nblignesfrais')
            ->where('idVisiteur', $idVisiteur)
            ->where('annee', $annee)
            ->where('mois', $mois)
            ->get()
            ->getRowArray();
        return $row['nblignesfrais'] === "0";
    }

    /** Dernier mois saisi */
    public function dernier_mois_saisi($idVisiteur)
    {
        $row = $this->db->table('fichefrais')
            ->select('max(CONCAT(annee,mois)) AS dernierAnneeMois')
            ->where('idVisiteur', $idVisiteur)
            ->get()
            ->getRowArray();
        return $row['dernierAnneeMois'];
    }

    /** Tous les id de frais forfait */
    public function get_les_id_frais_forfait()
    {
        return $this->db->table('fraisforfait')
            ->select('idfraisforfait')
            ->orderBy('idfraisforfait')
            ->get()
            ->getResultArray();
    }

    /** Crée nouvelles lignes de frais */
    public function cree_nouvelles_lignes_frais($idVisiteur, $annee, $mois)
    {
        $dernierMois = $this->dernier_mois_saisi($idVisiteur);
        $gsb_lib = new Gsb_lib();
        $num_annee = $gsb_lib->get_annee_from_anneemois($dernierMois);
        $num_mois = $gsb_lib->get_mois_from_anneemois($dernierMois);

        $laDerniereFiche = $this->get_id_ficheFrais($idVisiteur, $num_annee, $num_mois);

        if ($laDerniereFiche != null && $laDerniereFiche['idEtat'] == 'CR') {
            $idDerniereFiche = $laDerniereFiche['idFiche'];
            $this->maj_etat_fiche_frais($idDerniereFiche, 'CL');
        }

        $resultat = $this->db->table('fichefrais')->insert([
            'idVisiteur' => $idVisiteur,
            'annee' => $annee,
            'mois' => $mois,
            'nbJustificatifs' => 0,
            'montantValide' => 0,
            'dateModif' => date('Y-m-d'),
            'idEtat' => 'CR'
        ]);

        $insertionsOK = true;
        if ($resultat) {
            // On récupère l'id de la fiche qui vient d'être créée
            $nouvelleFiche = $this->get_id_ficheFrais($idVisiteur, $annee, $mois);
            $idNouvelleFiche = $nouvelleFiche['idFiche'];
            foreach ($this->get_les_id_frais_forfait() as $unIdFraisForfait) {
                $res = $this->db->table('lignefraisforfait')->insert([
                    'idFiche' => $idNouvelleFiche,
                    'idFraisForfait' => $unIdFraisForfait['idfraisforfait'],
                    'quantite' => 0
                ]);
                if (!$res) {
                    $insertionsOK = false;
                    break;
                }
            }
        } else {
            return false;
        }
        return $insertionsOK;
    }

    /** Met à jour l'état d'une fiche */
    public function maj_etat_fiche_frais($idFiche, $etat)
    {
        $this->db->table('fichefrais')->update(
            ['idEtat' => $etat, 'dateModif' => date('Y-m-d')],
            ['idFiche' => $idFiche]
        );
    }

    /** Met à jour les frais forfait */
    public function maj_frais_forfait($idFiche, array $lesFrais)
    {
        $majOK = true;
        foreach (array_keys($lesFrais) as $idFrais) {
            $res = $this->db->table('lignefraisforfait')->update(
                ['quantite' => $lesFrais[$idFrais]],
                ['idFiche' => $idFiche, 'idFraisForfait' => $idFrais]
            );
            if (!$res) {
                $majOK = false;
                break;
            }
        }
        return $majOK;
    }

    /** Supprime un frais hors forfait */
    public function supprimer_frais_hors_forfait($idFrais)
    {
        return $this->db->query("CALL SupprimerFraisHorsForfait($idFrais)");
    }

    /** Crée un nouveau frais hors forfait */
    public function creer_nouveau_frais_hors_forfait($idFiche, $libelle, $date, $montant)
    {
        $resultat = $this->db->table('lignefraishorsforfait')->insert([
            'idFiche' => $idFiche,
            'libelle' => $libelle,
            'dateFrais' => $date,
            'montant' => $montant
        ]);
        return $resultat;
    }

    // Sélection des fiches de frais pour les visiteurs selon l'idEtat en paramètre
    public function get_visiteurs_fiches_etat($idEtat)
    {
        return $this->db->table('fichefrais')
            ->select('utilisateur.idutilisateur, utilisateur.nom, utilisateur.prenom, fichefrais.idFiche, fichefrais.annee, fichefrais.mois, fichefrais.idEtat')
            ->join('utilisateur', 'fichefrais.idVisiteur = utilisateur.idutilisateur')
            ->where('utilisateur.idRole', 'VM')
            ->where('fichefrais.idEtat', $idEtat)
            ->orderBy('utilisateur.nom', 'ASC')
            ->get()
            ->getResultArray();
    }

    // Sélection des mots de passe utilisateur
    public function get_infos_mdp($idutilisateur)
    {
        return $this->db->table('utilisateur')
            ->select('idutilisateur, mdp, dateCreationMdp')
            ->where('idutilisateur', $idutilisateur)
            ->get()
            ->getRowArray();
    }

    // Met à jour le mot de passe utilisateur
    public function maj_motdepasse($idutilisateur, $nouveauMdp)
    {
        return $this->db->table('utilisateur')
            ->where('idutilisateur', $idutilisateur)
            ->update([
                'mdp' => $nouveauMdp,
                'dateCreationMdp' => date('Y-m-d')
            ]);
    }

    // Met à jour l'état d'une fiche à Remboursé (RB)
    public function maj_etat_fiches_rembourse($idvisiteur, $annee, $mois)
    {
        return $this->db->table('fichefrais')
            ->where('idEtat', 'VA')
            ->where('idVisiteur', $idvisiteur)
            ->where('annee', $annee)
            ->where('mois', $mois)
            ->update(['idEtat' => 'RB']);
    }

    // Calcul le montant des fraisforfait et fraishorsforfait
    public function get_montant_fraisforfait_fraishorsforfait($idFiche)
    {
        $total = 0;

        $lesFraisForfait = $this->db->table('lignefraisforfait')
            ->join('fraisforfait', 'lignefraisforfait.idFraisForfait = fraisforfait.idfraisforfait')
            ->where('idFiche', $idFiche)
            ->get()
            ->getResultArray();

        foreach ($lesFraisForfait as $frais) {
            $total += ($frais['quantite'] * $frais['montant']);
        }

        $lesFraisHorsForfait = $this->db->table('lignefraishorsforfait')
            ->where('idFiche', $idFiche)
            ->notLike('libelle', 'REFUSE')
            ->get()
            ->getResultArray();

        foreach ($lesFraisHorsForfait as $fraisHF) {
            $total += $fraisHF['montant'];
        }

        return $total;
    }

    // Refuser un frais hors forfait
    public function refuser_frais_hors_forfait($idFrais)
    {
        $fraishorsforfait = $this->db->table('lignefraishorsforfait')
            ->where('idLigneFHF', $idFrais)
            ->notLike('libelle', 'REFUSE')
            ->get()
            ->getRowArray();

        if ($fraishorsforfait != null) {

            $this->db->table('lignefraishorsforfait')
                ->where('idLigneFHF', $idFrais)
                ->update(['libelle' => 'REFUSE : ' . $fraishorsforfait['libelle']]);
        }
    }

    // Met à jour l'état d'une fiche à Validé (VA) et sauvegarde le montant
    public function maj_etat_fiches_valider($idvisiteur, $annee, $mois)
    {

        $fiche = $this->get_id_ficheFrais($idvisiteur, $annee, $mois);
        $montant = $this->get_montant_fraisforfait_fraishorsforfait($fiche['idFiche']);

        return $this->db->table('fichefrais')
            ->where('idEtat', 'CL')
            ->where('idVisiteur', $idvisiteur)
            ->where('annee', $annee)
            ->where('mois', $mois)
            ->update([
                'idEtat' => 'VA',
                'montantValide' => $montant
            ]);
    }
}
