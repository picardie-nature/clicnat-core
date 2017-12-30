<?php
namespace Picnat\Clicnat\ExtractionsConditions;

class bobs_ext_c_precision_date_max extends bobs_extractions_conditions {
	protected $pmax;
	const poste = false;

	function __construct($pmax) {
		parent::__construct();
		bobs_element::cli($annee);
		$this->arguments[] = 'pmax';
		$this->pmax = $pmax;
	}

	public function  __toString() {
		return "précision date max {$this->pmax} jour(s)";
	}

	static public function get_titre() {
		return "Précision date max";
	}

	public function get_sql() {
		return sprintf("observations.precision_date <= %d", $this->pmax);
	}

	public function get_tables() {
		return array('observations');
	}

	public static function new_by_array($t) {
		return new bobs_ext_c_annee($t['pmax']);
	}
}
