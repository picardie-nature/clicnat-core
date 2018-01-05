<?php
namespace Picnat\Clicnat;

/**
 * @brief les Classes d'espèces
 */
class bobs_classe extends bobs_tests {
	protected $db;
	protected $classe;

	const classe_type = 't_espece_classe';

	public function __construct($db, $classe) {
		self::cls($classe, self::except_si_vide);
		if (strlen($classe) > 1) {
			throw new \Exception('trop long');
		}
		$this->db = $db;
		$this->classe = $classe;
	}

	public function __get($p) {
		switch($p) {
			case 'classe':
				return $this->classe;
			default:
				throw \InvalidArgumentException("$p");
		}
	}

	public function __toString() {
		return $this->get_classe_lib(self::en_francais);
	}

	/**
	 * @brief liste les classes d'espèces disponnibles
	 * @see bobs_espece::get_classe_lib()
	 * @return un tableau de lettres
	 */
	public static function get_classes() {
		return get_db_type_enum(get_db(), self::classe_type);
	}

	const en_francais = true;
	const en_latin = false;

	/**
	 * @brief Nom de la classe
	 * @param $langue si vrai retour en français sinon en latin
	 * @return le libellé de la classe
	 *
	 * La liste des classes est gérées par le type t_espece_classe
	 * pour ajouter une classe exécuter la requête :
	 *
	 * alter type t_espece_classe add value 'D';
	 */
	public function get_classe_lib($langue=self::en_francais) {
		return self::get_classe_lib_par_lettre($this->classe, $langue);
	}

	/**
	 * @brief Nom de la classe
	 * @return le libellé de la classe
	 */
	public static function get_classe_lib_par_lettre($lettre, $fra=true) {
		$fra = $fra === true;
		switch ($lettre) {
			case 'A':
				return $fra?'Arachnides':'Arachnida';
			case 'B':
				return $fra?'Amphibiens':'Amphibia';
			case 'R':
				return $fra?'Reptiles':'Reptilia';
			case 'O':
				return $fra?'Oiseaux':'Aves';
			case 'M':
				return $fra?'Mammifères':'Mammalia';
			case 'I':
				return $fra?'Insectes':'Insecta';
			case 'P':
				return $fra?'Poissons':'Actinopterygii';
			case 'L':
				return $fra?'Bivalves':'Bivalvia';
			case 'N':
				return $fra?'Annélides':'Annelida';
			case 'C':
				return $fra?'Crustacés':'Crustacea';
			case 'H':
				return $fra?'Hydrozoaires':'Hydrozoa';
			case 'S':
				return $fra?'Chilopodes':'Chilopoda';
			case 'D':
				return $fra?'Diplopodes':'Diplopoda';
			case 'G':
				return $fra?'Gastéropodes':'Gastropoda';
			case 'E':
				return $fra?'Collemboles':'Collembola';
			case '_':
				return $fra?'Non applicable':'Non applicable';
		}
		throw new \InvalidArgumentException('pas de conversion pour classe "'.$lettre.'"');
	}

	/**
	 * @brief liste les ordres pour une classe
	 * @param $db ressource
	 * @param $classe la lettre le la classe
	 * @return un tableau
	 *
	 * les colonnes du tableau retourné : ordre,md5
	 */
	public function get_ordres() {
	    $sql = 'select distinct ordre, md5(coalesce(ordre,\'NULL\')||classe) as md5
		    from especes where classe=$1
		    order by ordre';
	    $q = bobs_qm()->query($this->db, 'ordre4classe', $sql, array($this->classe));
	    return bobs_element::fetch_all($q);
	}

	const sql_liste_espece = 'select * from especes where md5(coalesce(ordre,\'NULL\')||classe)=$1 order by famille,nom_s,nom_f';
	public static function get_especes_for_ordre_classe($db, $md5) {
	    $q = bobs_qm()->query($db, 'md5-classe-ordre', self::sql_liste_espece, array($md5));
	    return bobs_element::fetch_all($q);
	}

	public function especes_ordre_md5($md5) {
		return $this->get_especes_for_ordre_classe($this->db, $md5);
	}

	/**
	 * @brief Ordres dans un classement simplifié
	 * @return bobs_tag racine de la classe sinon faux
	 */
	public function a_classement_simple() {
		try {
			return bobs_tags::by_ref($this->db, 'EGR'.$this->classe);
		} catch (\Exception $e) {
			return false;
		}
	}

	const sql_lesp_nom_simple = '
			select e.*,t.lib as nom_simple
			from especes e,especes_tags et ,tags t
			where t.parent_id = $1
			and et.id_tag=t.id_tag
			and et.id_espece=e.id_espece
			order by t.id_tag, e.nom_f, e.nom_s';

	/**
	 * @brief Liste les especes avec le nom du tag
	 * @return un tableau associatif
	 */
	public function liste_especes_nom_simple() {
		$tag = $this->a_classement_simple();
		if (!$tag) {
			return [];
		}
		$q = bobs_qm()->query($this->db, 'cl_lesp_nsimple', self::sql_lesp_nom_simple, [$tag->id_tag]);
		return bobs_element::fetch_all($q);
	}

	public function especes() {
		return bobs_espece::get_liste_par_classe($this->db, $this->classe);
	}
}
