<?php
namespace Picnat\Clicnat;

class clicnat_sortie_date {
	private $db;
	protected $id_sortie;
	protected $date_sortie;
	protected $etat;
	protected $inscription_prealable;
	protected $inscription_date_limite;
	protected $inscription_participants_max;
	protected $heure_depart_domicile;
	protected $heure_arrivee_rdv;
	protected $heure_debut_sortie;
	protected $heure_fin_sortie;
	protected $heure_retour_domicile;
	protected $participants_adulte;
	protected $participants_enfant;
	protected $participants_provenance;
	protected $participants_information;
	protected $participants_dons;
	protected $id_espace_point;

	public function __construct($db, $r) {
		$this->db = $db;
		foreach (array_keys($r) as $k) {
			$this->$k = $r[$k];
		}
	}

	public function __get($prop) {
		switch ($prop) {
			case 'etat_lib':
				switch($this->etat) {
					case 1: return 'proposition';
					case 2: return 'refus';
					case 3: return 'valide';
					case 4: return 'annulation';
				}
				return 'indéterminé';
			case 'inscription_prealable':
				return $this->inscription_prealable == 't';
			case 'sortie':
				return new clicnat_sortie($this->db, (int)$this->id_sortie);

			default:
				return $this->$prop;
		}
	}

	const sql_par_date = 'select * from sortie_date where date_sortie::date = $1 order by date_sortie';

	public static function par_date($db, $yyyy_mm_dd) {
		$q = bobs_qm()->query($db, 'cal_sortie_par_date', self::sql_par_date, array($yyyy_mm_dd));
		$t = array();
		while ($r =  bobs_element::fetch($q)) {
			$t[] = new clicnat_sortie_date($db, $r);
		}
		return $t;
	}

	/**
	 * @brief Selectionne les sorties sur une periode (bornes inclues), optionnel critere sur l'etat
	 */
	public static function periode($db, $debut, $fin, $etats=array()) {
		$ret = array();
		$ts_jour = strtotime($debut);
		$ce_jour = $debut;
		while ($ce_jour <= $fin) {
			$sorties = clicnat_sortie_date::par_date($db, $ce_jour);
			$ret = array_merge ($ret, $sorties);
			$ts_jour += 86400;
			$ce_jour = strftime("%Y-%m-%d", $ts_jour);
		}
		return $ret;
	}

	const sql_par_etat = 'select coalesce(sortie.nom, \'sortie sans nom\') as nom, date_sortie, sortie.id_sortie
		from sortie_date,sortie
		where sortie.id_sortie=sortie_date.id_sortie and etat=$1 and id_utilisateur_propose=$2';

	const sql_par_etat_tout = 'select coalesce(sortie.nom, \'sortie sans nom\') as nom, date_sortie, sortie.id_sortie
		from sortie_date,sortie
		where sortie.id_sortie=sortie_date.id_sortie and etat=$1';

	/**
	 * @brief sorties par etat
	 */
	public static function par_etat($db, $etat, $id_utilisateur) {
		// id_utilisateur <= 0   -> on est admin -> tout
		if ($id_utilisateur > 0) {
			$q = bobs_qm()->query($db, 'cal_sortie_par_etat', self::sql_par_etat, array($etat,$id_utilisateur));
		} else {
			$q = bobs_qm()->query($db, 'cal_sortie_par_etat_tout', self::sql_par_etat_tout, array($etat));
		}
		return bobs_element::fetch_all($q);
	}

	public static function en_attente($db, $id_utilisateur) {
		return self::par_etat($db, 1, $id_utilisateur);
	}

	public static function pas_retenues($db, $id_utilisateur) {
		return self::par_etat($db, 2, $id_utilisateur);
	}

	public static function valides($db, $id_utilisateur) {
		return self::par_etat($db, 3, $id_utilisateur);
	}

	public static function annulees($db, $id_utilisateur) {
		return self::par_etat($db, 4, $id_utilisateur);
	}

	const sql_dernieres_modifs = 'select coalesce(sortie.nom, \'sortie sans nom\') as nom, date_sortie, sortie.id_sortie, coalesce(sortie.date_maj, to_timestamp(0)) as date_maj, etat
		from sortie_date,sortie
		where sortie.id_sortie=sortie_date.id_sortie and id_utilisateur_propose=$2
		order by date_maj desc';

	const sql_dernieres_modifs_tout = 'select coalesce(sortie.nom, \'sortie sans nom\') as nom, date_sortie, sortie.id_sortie, coalesce(sortie.date_maj, to_timestamp(0)) as date_maj, etat
		from sortie_date,sortie
		where sortie.id_sortie=sortie_date.id_sortie
		order by date_maj desc';

	public static function dernieres_modifs($db, $id_utilisateur) {
		// id_utilisateur <= 0   -> on est admin -> tout
		if ($id_utilisateur > 0) {
			$q = bobs_qm()->query($db, 'cal_sortie_dernieres_modifs', self::sql_dernieres_modifs, array($id_utilisateur));
		} else {
			$q = bobs_qm()->query($db, 'cal_sortie_dernieres_modifs_tout', self::sql_dernieres_modifs_tout, array());
		}
		return bobs_element::fetch_all($q);
	}

	public function update_field($champ, $valeur) {
		$table = 'sortie_date';
		$sql = sprintf("update {$table} set $champ = $3 where id_sortie = $1 and date_sortie=$2");
		$q = bobs_qm()->query($this->db, md5($sql), $sql, array($this->id_sortie, $this->date_sortie, $valeur));
		$pk = $this->pk;
		return $q;
	}
}
