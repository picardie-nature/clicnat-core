<?php
namespace Picnat\Clicnat;

class bobs_selection_melanger extends bobs_selection_action {
	protected $selection_a;
	protected $id_selection_a;
	protected $id_selection_b;
	protected $nom;

	public function __construct($db) {
		parent::__construct($db);
		$this->allowed_varnames = array_merge($this->allowed_varnames,
			array('id_selection_a','id_selection_b','nom'));
	}

	public function prepare() {
		self::cli($this->id_selection_a);
		self::cli($this->id_selection_b);
		if (empty($this->id_selection_a) || empty($this->id_selection_b)) {
			$this->ready = false;
			return false;
		}
		self::cls($this->nom);
		if (empty($this->nom)) {
			$this->ready = false;
			return false;
		}
		$this->selection_a = new bobs_selection($this->db, $this->id_selection_a);
		$this->ready = parent::prepare();
	}

	public function execute() {
		$id_selection_c = bobs_selection::nouvelle($this->db, $this->selection_a->id_utilisateur,$this->nom);
		$sql = "insert into selection_data (id_selection,id_citation)
				select distinct $1::integer,id_citation from selection_data
				where id_selection=$2 or id_selection=$3";
		bobs_qm()->query($this->db, 'sel_melange', $sql, array($id_selection_c, $this->id_selection_a, $this->id_selection_b));
		bobs_log("fusion selection : s{$this->id_selection_a} + s{$this->id_selection_b} = s{$id_selection_c}");
		$this->messages[] = "Sélections fusionnées dans <a href=\"?t=selection&sel={$id_selection_c}\">{$this->nom}</a>";
	}
}
