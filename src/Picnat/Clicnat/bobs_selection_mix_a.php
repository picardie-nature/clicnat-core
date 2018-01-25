<?php
namespace Picnat\Clicnat;

class bobs_selection_mix_a extends bobs_selection_action {
	protected $id_selection;
	protected $projection;
	protected $pas;
	protected $selection;

	public function __construct($db) {
		parent::__construct($db);
		$this->allowed_varnames = array_merge($this->allowed_varnames,
			array(
				'id_selection',
				'projection',
				'pas'
			));
	}

	public function prepare() {
		self::cli($this->id_selection, self::except_si_inf_1);
		self::cli($this->projection, self::except_si_inf_1);
		self::cli($this->pas, self::except_si_inf_1);
		$this->selection = new bobs_selection($this->db, $this->id_selection);
		$this->ready = parent::prepare();
	}

	public function execute() {
		$this->selection->mix($this->pas, $this->projection);
		$this->messages[] = 'agrégation des données terminée';
	}
}
