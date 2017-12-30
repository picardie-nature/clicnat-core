<?php
namespace Picnat\Clicnat;

class clicnat_validateur extends clicnat_utilisateur {
	protected $id_espece;

	public function __construct($db, $id_utilisateur, $id_espece) {
		parent::__construct($db, $id_utilisateur);
		$this->id_espece = $id_espece;
	}

	public function __get($c) {
		switch ($c) {
			case 'id_espece':
				return $this->id_espece;
			default:
				return parent::__get($c);
		}
	}

	public function espece() {
		return get_espece($this->db, $this->id_espece);
	}
}
