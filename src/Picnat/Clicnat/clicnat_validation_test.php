<?php

namespace Picnat\Clicnat;

abstract class clicnat_validation_test {
	protected $citation;
	protected $params;
	protected $ordre_execution;

	function __construct($citation) {
		$this->db = $citation->db();
		$this->citation = $citation;
		$this->params = array(); // paramètres par défaut
		// spécifique à cette espèce
		$this->espece_params = $citation->get_espece()->get_validation_params(get_class($this));
	}

	/**
	 * @brief evaluation du test
	 * @return array [passe=bool,message=text]
	 */
	abstract public function evaluer();

	protected function get_param($p) {
		if (isset($this->espece_params[$p])) {
			return $this->espece_params[$p];
		} elseif (isset($this->params[$p])) {
			return $this->params[$p];
		}
		throw new Exception("Pas de parametre $p");
	}
}
