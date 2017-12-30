<?php
namespace Picnat\Clicnat;

class bobs_selection_filtre_superficie_max extends bobs_selection_action {
	protected $selection;
	protected $id_selection;
	protected $smax;

	function __construct($db) {
		parent::__construct($db);
		$this->allowed_varnames[] = 'id_selection';
		$this->allowed_varnames[] = 'smax';
	}

	function execute() {
		$n_total = 0;
		$n_suppr = 0;
		$ids = [];
		foreach($this->selection->get_citations() as $citation) {
			$n_total++;
			$espace = $citation->observation()->espace();
			if (isset($espace->superficie)) {
				if ($espace->superficie >= $this->smax) {
					$ids[] = $citation->id_citation;
				}
			}
		}
		if (count($ids) > 0)
			$this->selection->enlever_ids($ids);
		$this->messages[] = count($ids)." citations retirées";
	}

	function prepare() {
		$this->selection = new bobs_selection($this->db, $this->id_selection);
	}
}
