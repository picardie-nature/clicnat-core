<?php
namespace Picnat\Clicnat;

class bobs_selection_enlever_avec_classe extends bobs_selection_action {
	protected $selection;
	protected $classe;
	protected $ready;

	public function __construct($db) {
		parent::__construct($db);
		$this->allowed_varnames[] = 'id_selection';
		$this->allowed_varnames[] = 'classe';
	}

	public function prepare() {
		$this->selection = new bobs_selection($this->db, $this->id_selection);
		$this->ready = parent::prepare();
		return $this->ready;
	}

	public function execute() {
		parent::execute();
		$ids = $this->selection->id_citations_avec_classe($this->classe);
		$this->messages[] = count($ids).' citation(s) retirée(s)';
		return $this->selection->enlever_ids($ids);
	}
}
