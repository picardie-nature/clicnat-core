<?php
namespace Picnat\Clicnat;

class bobs_selection_retirer_polygon_superficie_max_espece extends bobs_selection_action {
	public function __construct($db) {
		parent::__construct($db);
		$this->allowed_varnames[] = 'id_selection';
	}

	public function prepare() {
		$this->ready = parent::prepare();
		return $this->ready;
	}

	const sql = 'delete from selection_data using observations o,citations c,especes e,espace_polygon ep
			where id_selection=$1
			and selection_data.id_citation=c.id_citation
			and o.id_observation=c.id_observation
			and o.id_espace=ep.id_espace
			and c.id_espece=e.id_espece
			and e.superficie_max <= ep.superficie';

	public function execute() {
		parent::execute();
		$q = bobs_qm()->query($this->db, 'del_big_poly', self::sql, [$this->id_selection]);
	}
}
