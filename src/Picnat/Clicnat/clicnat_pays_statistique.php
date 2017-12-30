<?php
namespace \Picnat\Clicnat;

class clicnat_pays_statistique extends bobs_element {
	protected $id_pays;
	protected $nom;

	function __construct($db, $id) {
		parent::__construct($db, 'pays_statistique', 'id_pays', $id);
	}

	public static function getInstance($db, $id) {
		static $instances = [];

		if (!isset($intances[$id])) {
			$instances[$id] = new self($db, $id);
		}

		return $instances[$id];
	}

	const sql_liste = 'select * from pays_statistique order by nom';

	public static function liste($db) {
		static $t;
		if (!isset($t)) {
			$t = [];
			$q = bobs_qm()->query($db, 'pays_statistique_sel', self::sql_liste, []);
			while ($r = self::fetch($q)) {
				$t[] = new self($db, $r);
			}
		}
		return $t;
	}

	public function __toString() {
		return $this->nom;
	}

	public function __get($c) {
		switch ($c) {
			case 'nom':
				return $this->nom;
			case 'id_pays':
				return $this->id_pays;
			default:
				throw new \InvalidArgumentException($c);
		}
	}
}
