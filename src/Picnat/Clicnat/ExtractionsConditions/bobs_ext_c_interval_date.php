<?php
namespace Picnat\Clicnat\ExtractionsConditions;

class bobs_ext_c_interval_date extends bobs_extractions_conditions {
	protected $date_a;
	protected $date_b;
	private $sql_date_a;
	private $sql_date_b;
	const poste = true;

	function __construct($date_a, $date_b) {
		$this->date_a = $date_a;
		$this->date_b = $date_b;
		$this->sql_date_a = bobs_element::date_fr2sql($this->date_a);
		$this->sql_date_b = bobs_element::date_fr2sql($this->date_b);
		parent::__construct();
		$this->arguments[] = 'date_a';
		$this->arguments[] = 'date_b';
	}

	public function __toString() {
		return "entre le {$this->date_a} et le {$this->date_b}";
	}

	public static function get_titre() {
		return "Intervalle de dates";
	}

	public function get_sql() {
		return "observations.date_observation between '{$this->sql_date_a}'::date and '{$this->sql_date_b}'::date";
	}

	public static function new_by_array($t) {
		return new bobs_ext_c_interval_date($t['date_a'], $t['date_b']);
	}

	public function get_tables() {
		return array('observations');
	}
}
