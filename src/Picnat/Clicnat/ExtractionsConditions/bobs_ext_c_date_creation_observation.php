<?php
namespace Picnat\Clicnat\ExtractionsConditions;

class bobs_ext_c_date_creation_observation extends bobs_extractions_conditions {
	const poste = true;
	protected $date;

	function __construct($date) {
		parent::__construct();
		$this->arguments[] = 'date';
		$this->date = $date;
	}

	public function  __toString() {
		return "créée le {$this->date}";
	}

	public static function get_titre() {
		return "Date de création dans la base";
	}

	public function get_sql() {
		$sql_date = bobs_element::date_fr2sql($this->date);
		return "observations.date_creation::date = '{$sql_date}'::date";
	}

	public static function new_by_array($t) {
		return new self($t['date']);
	}

	public function get_tables() {
		return ['observations'];
	}
}
