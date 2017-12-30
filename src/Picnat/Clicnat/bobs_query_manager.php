<?php
namespace Picnat\Clicnat;


class bobs_query_manager extends bobs_tests {
	private $prepared;

	function __construct() {
		$this->prepared = array();
	}

	/*
	* execute une requete type envoyée, avec son tableau de args
	* name : nom de la requete (et de la fonction appelante?)
	*/
	public function query($db, $name, $sql, $args) {
		$start = microtime(true);

		bobs_element::s_teste_ressource($db);

		if (!is_array($args))
			throw new \InvalidArgumentException('$args is not array');

		// $sql peut être vide quand la requête a été préparée
		self::cls($sql);

		self::cls($name);

		if (empty($name))
			throw new \InvalidArgumentException('$name est vide');

		if (array_key_exists($name, $this->prepared)) {
			$this->prepared[$name]['n'] += 1;
		} else {
			if (empty($sql))
				throw new \InvalidArgumentException('$sql is empty');
			if (!pg_prepare($db, $name, $sql)) {
				$err = pg_last_error($db);
				throw new \InvalidArgumentException("prepare query \"$name\" failed\n\n$sql\n$err\n");
			}
			$this->prepared[$name] = [
				'n' => 1,
				'sql' => $sql,
				'tmin' => null,
				'tmax' => null
			];
		}
		$this->prepared[$name]['last_args'] = $args;
		#name permet de se référer à la requete préparée
		$q = pg_execute($db, $name, $args);
		if (!$q) {
			throw new \Exception('query "'.$name.'" failed '.$sql.' '.pg_errormessage());
		}
		$temps = microtime(true)-$start;
		if ($this->prepared[$name]['n'] == 1) {
			# triple egalité?
			$this->prepared[$name]['tmin'] =  $this->prepared[$name]['tmax'] = $temps;
		} else {
			$this->prepared[$name]['tmin'] = min($this->prepared[$name]['tmin'], $temps);
			$this->prepared[$name]['tmax'] = max($this->prepared[$name]['tmax'], $temps);
		}
		return $q;
	}

	public function ready($name) {
		return array_key_exists($name, $this->prepared);
	}

	public function vdump() {
		return print_r($this->prepared);
	}

	public function clear() {
		unset($this->prepared);
		$this->prepared = [];
	}
}
