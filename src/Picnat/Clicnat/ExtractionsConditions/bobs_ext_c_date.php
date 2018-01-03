<?php
namespace Picnat\Clicnat\ExtractionsConditions;
use Picnat\Clicnat\bobs_element;

class bobs_ext_c_date extends bobs_extractions_conditions {
	const poste = true;
	protected $date;

	function __construct($date) {
		parent::__construct();
		$this->arguments[] = 'date';
		$this->date = $date;
	}

	public function  __toString() {
		return "au {$this->date}";
	}

	public static function get_titre() {
		return "Date d'observation";
	}

	public function get_sql() {
		$sql_date = bobs_element::date_fr2sql($this->date);
		return "observations.date_observation = '{$sql_date}'::date";
	}

	public static function new_by_array($t) {
		return new bobs_ext_c_date($t['date']);
	}

	public function get_tables() {
		return array('observations');
	}
}
