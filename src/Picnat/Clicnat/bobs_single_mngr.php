<?php
namespace \Picnat\Clicnat;

/**
 * @brief gestionnaire d'instances
 */
class bobs_single_mngr {
	private $instances;
	private $classe;
	private $pk;
	private $db;

	function __construct($classe, $pk, $db = null) {
		// FIXME $db doit devenir obligatoire
		$this->instances = array();
		$this->classe = $classe;
		$this->pk = $pk;
		if (!is_null($db))
			$this->db = $db;
	}

	function get($db, $t_id=null, $instance=null) {
		if (!is_resource($db) && empty($t_id)) {
			// le premier argument est en fait le deuxième
			// FIXME réécrire toutes les parties ou single_mngr est utilisé
			// pour inclure que le handler de la base ne soit plus passé
			$t_id = $db;
			$db = $this->db;
		}

		if (is_array($t_id))
			$id = $t_id[$this->pk];
		else
			$id = $t_id;

		if (empty($id))
			throw new InvalidArgumentException('$id is empty');

		if (!array_key_exists($id, $this->instances)) {
			if (memory_limit() - memory_get_usage() < (1024*1024*5)) {
				$this->free();
			}
			if (is_null($instance)) {
				$this->instances[$id] = new $this->classe($db, $t_id);
			} else {
				$this->instances[$id] = $instance;
			}
		}

		return $this->instances[$id];
	}

	function free() {
		foreach (array_keys($this->instances) as $k) {
			unset($this->instances[$k]);
		}
	}
}
