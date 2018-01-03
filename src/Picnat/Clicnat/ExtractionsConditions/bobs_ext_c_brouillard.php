<?php
namespace Picnat\Clicnat\ExtractionsConditions;

class bobs_ext_c_brouillard extends bobs_extractions_conditions {
	protected $brouillard;
	const poste = true;

	/**
	 * @param $brouillard bool
	 */
	function __construct($brouillard) {
		parent::__construct();
		$this->arguments[] = 'brouillard';
		$this->brouillard = $brouillard;
	}

	public function  __toString() {
		return $this->brouillard?'Observations pas encore envoyées':'Observations envoyées';
	}

	static public function get_titre() {
		return 'Observations envoyées ou non';
	}

	public function get_sql() {
		return sprintf("observations.brouillard=%s", $this->brouillard?'true':'false');
	}

	public function get_tables() {
		return ['observations'];
	}

	public static function new_by_array($t) {
		return new self($t['brouillard']);
	}
}
