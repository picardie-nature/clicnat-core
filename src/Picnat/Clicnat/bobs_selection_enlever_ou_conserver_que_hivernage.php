<?php
namespace Picnat\Clicnat;

class bobs_selection_enlever_ou_conserver_que_hivernage extends bobs_selection_action {
	protected $selection;

	public function __construct($db) {
		parent::__construct($db);
		$this->allowed_varnames[] = 'id_selection';
		$this->allowed_varnames[] = 'enlever';
	}

	public function prepare() {
		$this->selection = new bobs_selection($this->db, $this->id_selection);
		$this->ready = parent::prepare();
		return $this->ready;
	}

	public function execute() {
		parent::execute();
		$ids = [];

		foreach ($this->selection->get_citations() as $citation) {
			if ($citation->get_espece()->est_dans_date_hivernage($citation->get_observation()->date_observation)) {
				if ($this->enlever)
					$ids[] = $citation->id_citation;
			} else {
				if (!$this->enlever)
					$ids[] = $citation->id_citation;
			}
		}
		return $this->selection->enlever_ids($ids);
	}
}
