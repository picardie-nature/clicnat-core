<?php
namespace Picnat\Clicnat;

class bobs_referentiel_especes_tiers {
	public $nom;
	public $db;

	function __construct($db, $nom)
	{
		$this->nom = $nom;
		$this->db = $db;
	}

	public function ajoute($id_espece, $id_tiers)
	{
		$sql = 'insert into referentiel_especes_tiers (tiers,id_espece,id_tiers) values ($1,$2,$3)';
		return bobs_qm()->query($this->db, 'ref_tiers_ins', $sql, array($this->nom, $id_espece, $id_tiers));
	}

	public function supprime_espece($id_espece)
	{
		$sql = 'delete from referentiel where tiers=$1 and id_espece=$2';
		return bobs_qm()->query($this->db, 'ref_tiers_del1', $sql, array($this->nom, $id_espece));
	}

	public function supprime_reference_tiers($id_tiers) {
		$sql = 'delete from referentiel_especes_tiers where tiers=$1 and id_tiers=$2';
		return bobs_qm()->query($this->db, 'ref_tiers_del2', $sql, array($this->nom, $id_tiers));
	}

	public function get_referentiel()
	{
		$sql = 'select especes.*,id_tiers from especes
				left join referentiel_especes_tiers
				on (especes.id_espece=referentiel_especes_tiers.id_espece and tiers=$1)
				order by especes.classe,especes.ordre ';
		$q = bobs_qm()->query($this->db, 'ref_tiers_s1', $sql, array($this->nom));
		return bobs_element::fetch_all($q);
	}

	public function ligne_referentiel_csv_titres()
	{
		$t = array(
			"votre_id_espece",
			"notre_id_espece",
			"cd_nom_mnhn",
			"classe",
			"ordre",
			"nom_fr",
			"nom_sc",
			"statut_origine",
			"statut_biologique",
			"indice_rar",
			"niveau_connaissance",
			"menace_regional",
			"fiabilite_menace_regional",
			"etat_conservation",
			"prio_conservation",
			"fiabilite_prio_conservation",
			"obs_region"
		);
		$s = '';
		foreach ($t as $c) {
			$s.= "\"$c\";";
		}
		return trim($s,';')."\n";
	}

	public function ligne_referentiel_csv($ligne)
	{
		$espece = new bobs_espece($this->db, $ligne);
		$ref = $espece->get_referentiel_regional();
		$n_cit = $espece->get_nb_citations();
		$a_citation = $n_cit>0?1:0;

		return sprintf('"%d";"%d";"%s";"%s";"%s";"%s";"%s";"%s";"%s";"%s";"%s";"%s";"%s";"%s";"%s";"%s";"%s"'."\n",
				$ligne['id_tiers'], $ligne['id_espece'],$ligne['taxref_inpn_especes'],
				$ligne['classe'], $ligne['ordre'],
				$ligne['nom_f'], $ligne['nom_s'],
				$ref['statut_origine'], $ref['statut_bio'],
				$ref['indice_rar'], $ref['niveau_con'], $ref['categorie'],
				$ref['fiabilite'], $ref['etat_conv'], $ref['prio_conv_cat'],
				$ref['prio_conv_fia'], $a_citation);
	}
}
