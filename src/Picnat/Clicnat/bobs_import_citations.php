<?php
namespace Picnat\Clicnat;

class bobs_import_citations extends bobs_element {
	public $id_import;
	public $num_ligne;
	public $id_citation;
	public $id_observation;
	public $id_espece;
	public $sexe;
	public $age;
	public $nb;
	public $nb_vol;
	public $precision_nb;
	public $qualite;
	public $commentaire;
	public $origine_statut_repro;
	public $statut_repro;
	public $statut_doublon;
	public $ref_import;
	public $distance_contact;
	public $determinateur;
	public $num_groupe;
	public $espece_confirme_par_obs;
	private $observation;
	private $import;
	private $ligne_original;

	function  __construct($db, $id) {
		parent::__construct($db, 'imports_citations', 'id_citation', $id);
	}

	/**
	* @brief retourne l'objet import associé
	* @return bobs_import
	*/
	public function get_import() {
		if (!isset($this->import))
			$this->import = new bobs_import($this->db, $this->id_import);
		return $this->import;
	}

	public function get_ligne() {
		if (!isset($this->ligne_original))
			$this->ligne_original = $this->get_import()>ligne($this->num_ligne);
		return $this->ligne_original;
	}

	/**
	* @return bobs_import_observations
	*/
	public function get_observation() {
		if (!isset($this->observation))
			$this->observation = new bobs_import_observations($this->db, $this->id_observation);
		return $this->observation;
	}

	public function get_espece_str() {
		$imp = $this->get_import();
		return $imp->extract_espece_str($this->get_ligne());
	}

	public function init_citation() {
	}

	const sql_set_nb = 'update imports_citations set nb=$2 where id_citation=$1';
	const sql_unset_nb= 'update imports_citations set nb=null where id_citation=$1';

	public function set_nb($n) {
		$n = self::cli($n, bobs_tests::null_si_vide);
		if (!is_null($n)) {
			if (bobs_qm()->query($this->db, 'imp_cit_set_nb', self::sql_set_nb, array($this->id_citation, $n))) {
				$this->nb = $n;
			}
		} else {
			if (bobs_qm()->query($this->db, 'imp_cit_set_nb_null', self::sql_unset_nb, array($this->id_citation))) {
				$this->nb = null;
			}
		}
	}

	const sql_set_genre = 'update imports_citations set sexe=$2 where id_citation=$1';
	public function set_genre($s) {
		if (bobs_qm()->query($this->db, 'imp_cit_set_g', self::sql_set_genre, array($this->id_citation, $s))) {
			$this->sexe = $s;
		}
	}

	const sql_set_age = 'update imports_citations set age=$2 where id_citation=$1';

	public function set_age($a) {
		if (bobs_qm()->query($this->db, 'imp_cit_set_age', self::sql_set_age, array($this->id_citation, $a))) {
			$this->age = $a;
		}
	}

	public function set_indice_qualite($indice) {
		$sql = "update imports_citations set indice_qualite=$2 where id_citation=$1";
		$sql_n = "update imports_citations set indice_qualite=null where id_citation=$1";
		if (empty($indice)) {
			bobs_qm()->query($this->db, 'imp_set_indice_qn', $sql_n, array($this->id_citation));
			$this->indice_qualite = null;
		} else {
			if (bobs_qm()->query($this->db, 'imp_set_indice_q', $sql, array($this->id_citation, $indice))) {
				$this->indice_qualite = $indice;
			}
		}
	}

	public function set_commentaire($c) {
		$sql = 'update imports_citations set commentaire=$2 where id_citation=$1';
		if (bobs_qm()->query($this->db, 'imp_cit_set_c', $sql, [$this->id_citation, $c])) {
			$this->commentaire = $c;
		}
	}

	public function ajoute_tag($id_tag) {
		if (empty($this->id_import)) {
			throw new Exception('ID IMPORT VIDE');
		}
		$sql = 'insert into imports_citations_tags (id_import, id_citation, id_tag) values ($1, $2, $3)';
		return bobs_qm()->query($this->db, 'imp_cit_add_tag', $sql, [$this->id_import, $this->id_citation, $id_tag]);
	}

	public function get_espece() {
		if ($this->id_espece > 0) {
			return get_espece($this->db, $this->id_espece);
		}
		return false;
	}
}
