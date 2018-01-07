<?php
namespace Picnat\Clicnat;

/**
 * @brief Itérateur d'objet bobs_espace_*
 */
class clicnat_iterateur_espaces implements \Iterator {
	private $db;
	private $position;
	private $ids = [];

	const sql_g_esp_table = 'select id_espace,table_espace from espace where id_espace=$1';

	/**
	 * @brief constructeur
	 * @param $db handler db
	 * @param $ids Un tableau de lignes [espace_table, id_espace]
	 */
	public function __construct($db, $ids) {
		$this->db = $db;
		$this->position = 0;
		$this->ids = $ids;
	}

	function rewind() {
		$this->position = 0;
	}

	function count() {
		return count($this->ids);
	}

	function current() {
		if (isset($this->ids[$this->position])) {
			switch ($this->ids[$this->position]['espace_table']) {
				case 'espace_point':
					return get_espace_point($this->db, $this->ids[$this->position]['id_espace']);
				case 'espace_polygon':
					return get_espace_polygon($this->db, $this->ids[$this->position]['id_espace']);
				case 'espace_chiro':
					return get_espace_chiro($this->db, $this->ids[$this->position]['id_espace']);
				case 'espace_line':
					return get_espace_ligne($this->db, $this->ids[$this->position]['id_espace']);
				case 'espace_commune':
					return get_espace_commune($this->db, $this->ids[$this->position]['id_espace']);
				case 'espace_departement':
					return get_espace_departement($this->db, $this->ids[$this->position]['id_espace']);
				case 'espace_toponyme':
					return get_espace_toponyme($this->db, $this->ids[$this->position]['id_espace']);
				case 'espace_littoral':
					return get_espace_littoral($this->db, $this->ids[$this->position]['id_espace']);
				case 'espace_structure':
					return get_espace_structure($this->db, $this->ids[$this->position]['id_espace']);
				case 'espace_l93_10x10':
					return get_espace_l93_10x10($this->db, $this->ids[$this->position]['id_espace']);
				case 'espace_l93_5x5':
					return get_espace_l93_5x5($this->db, $this->ids[$this->position]['id_espace']);


			}
			throw new \Exception("Position: {$this->position} table:'{$this->ids[$this->position]['espace_table']}'");
		} else {
			throw new \Exception("Index vide");
		}
	}

	function key() {
		return $this->position;
	}

	function next() {
		$this->position++;
	}

	function valid() {
		return isset($this->ids[$this->position]);
	}
}
