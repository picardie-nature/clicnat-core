<?php
namespace Picnat\Clicnat;

/**
 * @brief Iterateur construit a partir d'un type enum de la base
 */
class clicnat_db_type_enum implements \Iterator {
	protected $typname;
	protected $position;
	protected $valeurs;

	const sql_liste = 'select enumlabel from pg_type,pg_enum where typname=$1 and pg_type.oid=enumtypid order by enumsortorder';

	/**
	 * @param $db connection à la base
	 * @param $typname nom du type
	 */
	public function __construct($db, $typname) {
		$this->db = $db;
		$this->typname = $typname;
		$this->position = 0;

		$q = bobs_qm()->query($this->db, 'db_enum_l', self::sql_liste, array($this->typname));
		$t = bobs_element::fetch_all($q);
		$this->valeurs = array_column($t, 'enumlabel');
	}

	public function rewind() {
		$this->position = 0;
	}

	public function key() {
		return $this->position;
	}

	public function next() {
		$this->position++;
	}

	public function valid() {
		return isset($this->valeurs[$this->position]);
	}

	public function count() {
		return count($this->valeurs);
	}

	public function current() {
		return $this->valeurs[$this->position];
	}
}
