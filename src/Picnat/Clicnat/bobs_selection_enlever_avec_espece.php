<?php
namespace Picnat\Clicnat;

class bobs_selection_enlever_avec_espece extends bobs_selection_action {
	protected $selection;
	protected $id_espece;
	protected $ready;

	public function __construct($db) {
		parent::__construct($db);
		$this->allowed_varnames[] = 'id_selection';
		$this->allowed_varnames[] = 'id_espece';
	}

	public function prepare() {
		$this->selection = new bobs_selection($this->db, $this->id_selection);
		$this->ready = parent::prepare();
		return $this->ready;
	}

	public function execute() {
		parent::execute();
		$ids = $this->selection->id_citations_avec_espece(get_espece($this->db, $this->id_espece));
		$this->messages[] = count($ids).' citation(s) retirée(s)';
		return $this->selection->enlever_ids($ids);
	}
}
