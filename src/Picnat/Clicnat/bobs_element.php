<?php
namespace Picnat\Clicnat;

/**
 * @brief parent class
 */
class bobs_element extends bobs_tests {
	protected $db;
	protected $table;
	protected $pk;

	/**
	 * @brief champ date derniere modification
	 *
	 * le nom indiqué dans cette propriété sera mis
	 * à jour lors d'un appel a update_field
	 */
	protected $champ_date_maj = false;

	function __construct($db, $table, $pk, $id) {
		$this->table = $table;
		$this->pk = $pk;
		$this->db = $db;

		$this->teste_ressource();

		if (is_array($id)) {
			foreach ($id as $k => $v)
				$this->$k = $v;
		} else {
			if (!empty($id)) {
				$qm = bobs_qm();
				$sql = '';
				if (!$qm->ready($table.BOBS_SELECT_QUERY_SUFFIX))
					$sql = sprintf('select * from %s where %s=$1', $this->table, $this->pk);
				$q = $qm->query($db, $table.BOBS_SELECT_QUERY_SUFFIX, $sql, [$id]);
				$t = $this->fetch($q);
				if (!empty($t))
					foreach ($t as $k => $v) $this->$k = $v;
				else
					throw new clicnat_exception_pas_trouve('pas de résultat', BOBS_ERR_NOTFOUND);
			}
		}
	}

	/**
	 * @brief retourne le numéro de la décade d'une date
	 * @param $date date compatible avec strtotime
	 */
	static function decade($date) {
		return floor((int)strftime("%j", strtotime($date))/10);
	}

	/**
	 * @brief retourne le numéro de la décade du mois
	 * @param $date date compatible avec strotime
	 * @return int dans [1,3]
	 *
	 * pour j (jour du mois) :
	 *   1 à 10 => 1
	 *   11 à 20 => 2
	 *   21 et + => 3
	 */
	static function decade_mois($date) {
		$j = (int)sprintf("%d", strtotime($date));
		return $j>30?3:floor(($j-1)/10)+1;
	}

	private function teste_ressource() {
		self::s_teste_ressource($this->db);
	}

	public static function s_teste_ressource($db) {
		if (@get_resource_type($db) != DB_RESOURCE_TYPE)
			throw new \InvalidArgumentException('$db doit être une resource pgsql');
	}

	public static function nextval($db, $sequence) {
		self::s_teste_ressource($db);
		self::cls($sequence);

		if (empty($sequence))
			throw new \InvalidArgumentException('$sequence ne peut être vide');

		$q = bobs_qm()->query($db, 'nextval_'.$sequence, 'select nextval($1) as n', [$sequence]);
		$r = self::fetch($q);

		self::cli($r['n']);

		if (empty($r['n']))
			throw new \Exception('nextval a échoué, identifiant vide');

		return $r['n'];
	}

	public static function insert($db, $table, $data) {
		self::s_teste_ressource($db);
		self::cls($table);

		if (empty($table))
			throw new \InvalidArgumentException('$table ne peut être vide');

		if (!is_array($data))
			throw new \InvalidArgumentException('$data doit être un tableau');

		if (get_resource_type($db) != DB_RESOURCE_TYPE)
			throw new \InvalidArgumentException('$db doit être une resource pgsql');

		$qm = bobs_qm();
		$sql = '';

		// if it's first insert we must build sql
		if (!$qm->ready($table.BOBS_INSERT_QUERY_SUFFIX)) {
			$values = [];
			$sql = "insert into $table (";
			foreach ($data as $k => $v) {
				$values[] = $v;
				$sql .= sprintf('"%s",', $k);
			}
			$sql = sprintf("%s)\n values (", trim($sql, ','));

			for ($i=1; $i<=count($values); $i++)
				$sql .= sprintf('$%d,', $i);

			$sql = sprintf('%s)', trim($sql, ','));
		}

		$q = $qm->query($db, $table.BOBS_INSERT_QUERY_SUFFIX, $sql, $data);

		if (!$q) {
			echo "<pre>".htmlentities($sql)."</pre>";
			throw new \Exception('Problème lors de l\'insertion : '.pg_last_error());
		}

	}

	public function __get($k) {
		return $this->$k;
	}

	/**
	 * @brief mise à jour du champ de date de mise à jour
	 */
	public function update_date_maj_field() {
		return $this->update_field_now($this->champ_date_maj, true);
	}

	public function update_field_null($champ, $sans_dmaj_update=false) {
		$sql = sprintf("update {$this->table} set $champ = null where {$this->pk} = $1");
		$q = bobs_qm()->query($this->db, md5($sql), $sql, [$this->__get($this->pk)]);
		if ($q) {
			$this->$champ = null;
			if (!$sans_dmaj_update)
				$this->update_date_maj_field();
		}
		return $q;
	}

	/**
	 * @brief mise à jour d'un champ avec la date et l'heure courante
	 */
	public function update_field_now($champ, $sans_dmaj_update=false) {
		return $this->update_field($champ, strftime("%Y-%m-%d %H:%M:%S", mktime()), true);
	}

	/**
	 * @brief mise à jour d'un champ dans la base
	 * @param $champ la colonne a modifier
	 * @param $valeur la nouvelle valeur
	 * @param $sans_dmaj_update si vrai ne pas mettre à jour le champ date de mise à jour
	 * @return le résultat de la requête
	 */
	public function update_field($champ, $valeur, $sans_dmaj_update=false) {
		if ($this->$champ == $valeur)
			return true;

		if (empty($this->pk))
			throw new \Exception("clé primaire non définie");

		if (empty($this->table))
			throw new \Exception("table non définie");

		$id = $this->__get($this->pk);

		if (empty($id)) {
			throw new \Exception("Pas de valeur pour la clé primaire {$this->pk}");
		}

		$sql = sprintf("update {$this->table} set $champ = $2 where {$this->pk} = $1");
		$q = bobs_qm()->query($this->db, md5($sql), $sql, [$id, $valeur]);
		if (pg_affected_rows($q) == 0) {
			throw new \Exception("Aucune modification enregistrée");
		}
		if ($q) {
			$this->$champ = $valeur;
			if ($this->champ_date_maj && !$sans_dmaj_update) {
				$this->update_date_maj_field();
			}
		}
		return $q;
	}

	/**
	 * @brief exécute une requête SQL
	 * @param $db un descripteur vers la base de données
	 * @param $sql code SQL de la requête
	 * @param $opts des options
	 *
	 * options : logmaj => enregistre la requêtre dans un fichier
	 */
	static function query($db, $sql, $opts=array()) {
		if (get_resource_type($db) != DB_RESOURCE_TYPE)
			throw new \InvalidArgumentException('$db doit être une resource pgsql');

		$q = @pg_query($db, $sql);
		if (!$q)
			throw new \Exception('Erreur base de données '.pg_last_error().'<br/>'.$sql);

		return $q;
	}

	public static function fetch($q) {
		if (!is_resource($q))
			throw new \Exception('$q doit être une ressource');
			# retourne un tableau associatif contenant la ligne du résultat contenu dans $q
		return pg_fetch_assoc($q);
	}

	public static function query_fetch_all($db, $sql, $opts = []) {
		self::s_teste_ressource($db);

		$q = self::query($db, $sql, $opts);
		return self::fetch_all($q);
	}

	public static function fetch_all($q) {
		if (!is_resource($q))
			throw new \Exception('$q est pas une ressource');
		# retourne un tableau contenant toutes les lignes du resultat
		$t = pg_fetch_all($q);

		// probably no result, but return
		// array for count and foreach loops
		if (!t) {
			return [];
		}
		return $t;
	}

	public function escape($str) {
		return pg_escape_string($str);
	}

	public static function query_assoc($db, $sql) {
		$q = self::query($db,$sql);
		return self::fetch($q);
	}

	/**
	 * @brief transforme une colonne array de la base en un tableau d'entiers
	 */
	public function array_integer_get($prop) {
		return split(',', str_replace(['{','}'], '', $this->$prop));
	}

	/**
	 * @brief transforme une colonne array de la base en un critère in()
	 *
	 * {3,2,1} => (3,2,1)
	 */
	public function array_integer_get_sqlin($prop) {
		return str_replace(['{','}'], ['(',')'], $this->$prop);
	}

	/**
	 * @brief sortie csv tabulée dans un fichier
	 *
	 * @param $fp un descripteur de fichier
	 *
	 */
	public static function array2tabtxt($tab, $fp) {
		$l = '';
		foreach ($tab[0] as $k=>$v) {
			$l .= $k."\t";
		}
		fwrite($fp, sprintf("%s\r\n", trim($l, "\t")));

		foreach ($tab as $ltab) {
			$l = '';
			foreach ($ltab as $k => $v) {
				$l .= '"'.(empty($v)?' ':$v)."\"\t";
			}

			fwrite($fp, sprintf("%s\r\n", trim($l, "\t")));
		}
	}

	const sql_columns = "select column_name from information_schema.columns where table_name=$1";

	/**
	 * @brief liste des colonnes d'une table
	 * @return un tableau
	 */
	public function table_columns() {
		$q = bobs_qm($this->db, 'columns', self::sql_columns, [$this->table]);
		$t = self::fetch_all($q);
		return array_column($t, 'column_name');
	}

	const sql_column_type = "select data_type from information_schema.columns where table_name=$1 and column_name=$2";

	public function column_type($col) {
		$q = bobs_qm()->query($this->db, 'column_type', self::sql_column_type,[$this->table, $col]);
		$r = self::fetch($q);
		return isset($r['data_type'])?$r['data_type']:false;
	}

	public static function date_fr2tab($texte) {
		self::cls($texte);
		$allc = str_split($texte);
		$delim = null;
		foreach ($allc as $c) {
			if (!is_numeric($c)) {
				$delim = $c;
				break;
			}
		}

		if (empty($delim)) {
			throw new \Exception('Date invalide - délimiteur introuvable');
		}

		list($jour, $mois, $annee) = explode($delim, $texte);

		return array(DATE_J=>$jour, DATE_M=>$mois, DATE_A=>$annee);
	}

	public static function date_fr2sql($texte) {
		$d = self::date_fr2tab($texte);
		return implode('-', [$d[DATE_A], $d[DATE_M], $d[DATE_J]]);
	}

	public static function truefalse($bool) {
		return $bool?'true':'false';
	}

	public function get_table() {
		return $this->table;
	}

	public function db() {
		return $this->db;
	}
}


$context = 'general';

require_once(OBS_DIR.'tags.php');
require_once(OBS_DIR.'commentaires.php');
