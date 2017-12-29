<?php
namespace Picnat\Clicnat;

/**
 * @brief Calendrier de prospection
 */
class bobs_calendrier extends bobs_element {
	public $id_date;
	public $id_espace;
	public $espace_table;
	public $date_sortie;
	public $commentaire;
	public $tag;

	const tag_max_length = 10;

	function __construct($db, $id) {
		parent::__construct($db, 'calendriers_dates', 'id_date', $id);
		$this->date_sortie_tstamp = strtotime($this->date_sortie);
	}

	/**
	* @return bobs_espace
	*/
	public function get_espace() {
		switch ($this->espace_table) {
			case 'espace_chiro':
				return get_espace_chiro($this->db, $this->id_espace);
			case 'espace_point':
				return get_espace_point($this->db, $this->id_espace);
			case 'espace_polygon':
				return get_espace_polygon($this->db, $this->id_espace);
		}
		throw new \Exception('espace '.$this->espace_table.' non géré');
	}

	public function get_participants() {
		$sql = 'select utilisateur.* from utilisateur,calendriers_participants
			where calendriers_participants.id_utilisateur=utilisateur.id_utilisateur
			and id_date=$1';
		$q = bobs_qm()->query($this->db, 'cal-get-particip', $sql, array($this->id_date));
		return self::fetch_all($q);
	}

	public function ajoute_participant($id_utilisateur) {
		self::cli($id_utilisateur);
		return parent::insert($this->db, 'calendriers_participants',
			array(
				'id_utilisateur' => $id_utilisateur,
				'id_date' => $this->id_date
			)
		);
	}

	public function enlever_participant($id_utilisateur) {
		self::cli($id_utilisateur);
		$sql = 'delete from calendriers_participants where id_date=$1 and id_utilisateur=$2';
		bobs_qm()->query($this->db, 'cal-del-particip', $sql, array($this->id_date, $id_utilisateur));
		$participants = $this->get_participants();
		if (count($participants) == 0) {
			$sql = 'delete from calendriers_dates where id_date=$1';
			bobs_qm()->query($this->db, 'cal-del', $sql, array($this->id_date));
		}
	}

	const sql_annulation_1 = 'delete from calendriers_participants where id_date=$1';
	const sql_annulation_2 = 'delete from calendriers_dates where id_date=$1';

	public function annuler() {
		bobs_qm()->query($this->db, 'cal_del_a', self::sql_annulation_1, array($this->id_date));
		bobs_qm()->query($this->db, 'cal_del_b', self::sql_annulation_2, array($this->id_date));
		return true;
	}

	const sql_update_commtr = 'update calendriers_dates set commentaire=$2 where id_date=$1';

	public function update_commentaire($commentaire) {
		bobs_qm()->query($this->db,'cal_commtr_up', self::sql_update_commtr, array($this->id_date, htmlspecialchars($commentaire,ENT_QUOTES,"UTF-8")));
	}

	public function sync_liste_participants($tab_id_utilisateur) {
		$participants = $this->get_participants();
		foreach ($participants as $p) {
			$position = array_search($p['id_utilisateur'], $tab_id_utilisateur);
			if ($position === false) {
				$this->enlever_participant($p['id_utilisateur']);
			} else {
				unset($tab_id_utilisateur[$position]);
			}

		}
		foreach ($tab_id_utilisateur as $id) {
			$this->ajoute_participant($id);
		}
		return true;
	}

	/**
	* création d'une nouvelle date
	* @param ressource $db
	* @param array $data id_espace, espace_table, date_sortie, tag
	* @return int
	*/
	public static function insert($db, $data) {
		$dsortie = self::date_fr2sql($data['date_sortie']);
		self::cls($data['commentaire']);
		self::cls($data['espace_table']);
		self::cli($data['id_espace']);
		self::cls($data['tag']);

		if (strlen($data['tag']) > self::tag_max_length)
			throw new \Exception('tag trop long');

		$d = [
			'id_date'      => self::nextval($db),
			'date_sortie'  => $dsortie,
			'commentaire'  => $data['commentaire'],
			'id_espace'    => $data['id_espace'],
			'espace_table' => $data['espace_table'],
			'tag'          => $data['tag']
		];
		parent::insert($db, 'calendriers_dates', $d);
		return $d['id_date'];
	}

	/**
	 * @brief clé suivante
 	 * @param ressource $db
 	 * @return int
	 */
	public static function nextval($db) {
		return parent::nextval($db, 'calendriers_dates_id_date_seq');
	}

	const sql_dates = 'select * from calendriers_dates where espace_table=$1
			order by date_sortie desc';

	/**
	 * @brief Donne les dates pour une catégorie
	 * @param $db ressource
	 * @param $espace_table nom de la table contenant les points
	 * @return bobs_calendrier[]
	 */
	public static function get_dates($db, $espace_table) {
		$q = bobs_qm()->query($db, 'cal_get_dates', self::sql_dates, [$espace_table]);
		$t = [];
		while ($r = self::fetch($q))
			$t[] = new bobs_calendrier($db, $r);
		return $t;
	}

	const sql_dates_espace = 'select id_date from calendriers_dates where espace_table=$1 and id_espace=$2';
	/**
	 * @brief donne les dates pour un objet carto
	 * @param $db ressource
	 * @param $espace_table nom de la table
	 * @param $id_espace numero de l'objet
	 * @return clicnat_iterateur_calendrier
	 */
	public static function get_dates_espace($db, $espace_table, $id_espace) {
		self::cls($espace_table, self::except_si_vide);
		self::cli($id_espace, self::except_si_inf_1);
		$t = array();
		$q = bobs_qm()->query($db, 'cal_get_dates_espaces', self::sql_dates_espace, array($espace_table,$id_espace));
		while ($r = self::fetch($q))
			$t[] = $r['id_date'];
		return new clicnat_iterateur_calendrier($db, $t);

	}
	const sql_dates_tag = 'select id_date from calendriers_dates where tag=$1 order by date_sortie desc';

	/**
	 * @brief Donne les dates pour un tag
	 * @param $db ressource
	 * @param $tag le tag
	 */
	public static function get_dates_tag($db, $tag) {
		self::cls($tag);
		if (strlen($tag) > self::tag_max_length)
			throw new Exception('tag trop long');
		$q = bobs_qm()->query($db, 'cal_get_dates_tag', self::sql_dates_tag, array($tag));
		$t = array();
		while ($r = self::fetch($q)) {
			$t[] = $r['id_date'];
		}
		return new clicnat_iterateur_calendrier($db, $t);
	}
}
