<?php
namespace Picnat\Clicnat;

class clicnat_protocoles extends bobs_element {
	const sql_sel = 'select * from protocoles where id_protocole=$1';
	protected $db;
	protected $id_protocole;
	protected $lib;
	protected $description;
	protected $ouvert;

	public function __construct($db, $id_proto) {
		$this->db = $db;
		$q = bobs_qm()->query($db, 'proto_construct', self::sql_sel, [$id_proto]);
		$r = self::fetch($q);
		if (!$r) {
			throw new Exception('pas trouvé');
		}
		$this->id_protocole = $r['id_protocole'];
		$this->lib = $r['lib'];
		$this->description = $r['description'];
		$this->ouvert = $r['ouvert'];
		$this->pk = "id_protocole";
		$this->table = "protocoles";
	}

	public function __toString() {
		return $this->lib;
	}

	public function __get($c) {
		switch ($c) {
			case 'id_protocole':
			case 'description':
			case 'lib':
				return $this->$c;
			case 'ouvert':
				return $this->$c == 't';
			default:
				throw new InvalidArgumentException();
		}
	}

	/**
	 * @brief Insertion d'un nouveau protocole
	 *
	 * champs du tableau $data :
	 *  - id_protocole (texte 30cars max)
	 *  - lib
	 *  - description
	 */
	public static function insert($db, $data) {
		parent::insert($db, 'protocoles', $data);
	}

	const sql_liste = 'select * from protocoles order by lib';

	public static function liste($db) {
		$q = bobs_qm()->query($db, 'l_proto_liste2', self::sql_liste, []);
		$t = [];
		while ($r = self::fetch($q)) {
			$t[] = new self($db, $r['id_protocole']);
		}
		return $t;
	}
}
?>
