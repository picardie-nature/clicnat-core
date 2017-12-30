<?php
namespace Picnat\Clicnat;

class clicnat_phoque extends bobs_element {
	protected $id_phoque;
	protected $nom;
	protected $numero;
	protected $id_espece;
	protected $sexe;
	protected $bague_numero;
	protected $bague_couleur;
	protected $pelage;
	protected $taches;
	protected $date_creation;
	protected $date_modification;

	// commentaires
	// mort

	const pelage_type = 't_phoque_pelage';
	const taches_type = 't_phoque_taches';
	const sexe_type = 't_phoque_sexe';

	function __construct($db, $id) {
		parent::__construct($db, 'phoques', 'id_phoque', $id);
		$this->champ_date_maj = 'date_modification';
	}

	function __get($c) {
		switch ($c) {
			case 'espece':
				return get_espece($this->db, $id_espece);
			case 'photos':
				return clicnat_phoque_photos::par_phoque($this);
			default:
				return $this->$c;
		}
	}

	function __toString() {
		return $this->nom;
	}

	const sql_par_nom = 'select * from phoques where nom = $1';

	public static function insert($db, $nom, $photo=false) {
		$id_phoque = self::nextval($db, 'phoques_id_phoque');
		parent::insert($db, 'phoques', array("nom"=>$nom, "id_phoque"=>$id_phoque, "numero"=>"Clicnat:$id_phoque"));
		$phoque = get_phoque($db, $id_phoque);
		if ($photo) {
			$photo->set_phoque($phoque);
		}
		return $phoque;
	}

	public static function par_nom($db, $nom) {
		$q = bobs_qm()->query($db, 'phoque_par_nom', self::sql_par_nom, array($nom));
		return get_phoque($db, self::fetch($q));

	}
	public static function choix_taches($db) {
		return get_db_type_enum($db, self::taches_type);
	}

	public static function choix_pelage($db) {
		return get_db_type_enum($db, self::pelage_type);
	}

	public static function choix_sexe($db) {
		return get_db_type_enum($db, self::sexe_type);
	}

	public function set_nom($nom) {
		self::cls($nom, self::except_si_vide);
		$this->update_field('nom', $nom);
	}

	public function set_numero($numero) {
		$this->update_field('numero', $numero);
	}

	public function set_bague_numero($numero) {
		$this->update_field('bague_numero', $numero);
	}

	public function set_bague_couleur($couleur) {
		$this->update_field('bague_couleur', $couleur);
	}

	public function set_taches($taches) {
		$this->update_field('taches', $taches);
	}

	public function set_pelage($pelage) {
		$this->update_field('pelage', $pelage);
	}

	public function set_sexe($sexe) {
		$this->update_field('sexe', $sexe);
	}

	/**
	 * @brief Obtenir une liste de phoques
	 * @param $filtres un tableau, chaque clé est le nom d'un filtre et contient un tableau avec la liste des valeurs pour la clé
	 * @return clicnat_iterateur_phoques
	 */
	public static function liste($db, $filtres=array()) {
		$sql = 'select id_phoque from phoques where 1=1 ';
		foreach ($filtres as $k => $vs) {
			switch ($k) {
				case 'sexe':
				case 'pelage':
				case 'taches':
				case 'id_espece':
					break;
				default:
					throw new Exception('filtre invalide');
			}
			$in = '';
			foreach ($vs as $v) {
				$in .= "'".self::escape($v)."',";
			}
			$in = trim($in,',');
			$sql .= 'and '.self::escape($k)." in ($in)";
		}
		$query_id = md5($sql);
		$q = bobs_qm()->query($db, $query_id, $sql, array());
		$r = self::fetch_all($q);
		return new clicnat_iterateur_phoques($db, array_column($r, 'id_phoque'));
	}
}
