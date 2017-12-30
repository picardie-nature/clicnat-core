<?php
namespace Picnat\Clicnat;

class clicnat_cache_distance {
	const db_file = '/tmp/cache_distance.db';
	const buffer_max_rows = 250;
	const sql_create = 'create table cache_distance (id_a int, id_b int, d real, primary key (id_a,id_b))';

	private $use_pdo = true;
	private $db;
	private $write_buffer;

	public function __construct() {
		if ($this->use_pdo) {
			$this->db = new PDO('sqlite:'.self::db_file);
			$this->db->exec(self::sql_create);
		} else {
			$this->db = sqlite_open(self::db_file);
			sqlite_query($this->db, self::sql_create);
		}
		$this->write_buffer = array();
	}

	public function __destruct() {
		$this->sync();
		sqlite_close($this->db);
	}

	private function ordonne($id_a, $id_b) {
		return array(min($id_a, $id_b), max($id_a, $id_b));
	}

	public function sync() {
		if (!$this->use_pdo) {
			echo "#";
			flush();
			sqlite_query($this->db, "begin");
			foreach ($this->write_buffer as $l) {
				list($a,$b,$d) = $l;
				sqlite_query($this->db, "insert into cache_distance (id_a,id_b,d) values ($a,$b,$d)");
			}
			$this->write_buffer = array();
			sqlite_query($this->db, "commit");
			echo "#";
			flush();
		} else {
			$this->db->beginTransaction();
			$stmt = $this->db->prepare('insert into cache_distance (id_a,id_b,d) values (?, ?, ?)');
			foreach ($this->write_buffer as $l) {
				$stmt->execute($l);
			}
			$this->db->commit();
		}
	}

	public function get($id_a, $id_b) {
		list($a, $b) = $this->ordonne($id_a,$id_b);

		if (array_key_exists("$a.$b", $this->write_buffer))
			return $this->write_buffer["$a.$b"][2];
		if (!$this->use_pdo) {
			$r = sqlite_single_query($this->db, "select d from cache_distance where id_a=$a and id_b=$b", SQLITE_NUM);
		} else {
			$q = $this->db->query("select d from cache_distance where id_a=$a and id_b=$b");
			$r = $q->fetch(PDO::FETCH_ASSOC);
		}
		if (!$r && $r != '0') {
			return false;
		}
		return $r;
	}

	public function set($id_a, $id_b, $d) {
		list($a, $b) = $this->ordonne($id_a,$id_b);
		$this->write_buffer["$a.$b"] = array($a,$b,$d);
		if (count($this->write_buffer) > self::buffer_max_rows)
			$this->sync();

	}
}
