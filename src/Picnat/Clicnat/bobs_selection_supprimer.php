<?php
namespace Picnat\Clicnat;

class bobs_selection_supprimer extends bobs_selection_action {
	protected $selection;

	public function __construct($db) {
		parent::__construct($db);
		$this->allowed_varnames[] = 'id_selection';
	}

	public function prepare() {
		$this->selection = new bobs_selection($this->db, $this->id_selection);
		$this->ready = parent::prepare();
		return $this->ready;
	}

	public function execute() {
		parent::execute();
		$this->messages[] = 'sélection supprimée';
		return $this->selection->drop();
	}
}
