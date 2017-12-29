<?php
namespace Picnat\Clicnat;

class clicnat_citation extends bobs_citation {
	public function getInstance($db, $id, $table='citations', $pk='id_citation') {
		static $instances;
		if (!isset($instances))
			$instances = [];

		if (is_array($id)) {
			if (!isset($id[$pk])) {
				throw new \Exception("le tableau \$id n'a pas de clé $pk");
			}
			$__id = $id[$pk];
		} else {
			$__id = $id;
		}

		if (!isset($instances[$__id]))
			$instances[$__id] = new self($db, $id);

		return $instances[$__id];
	}
}
