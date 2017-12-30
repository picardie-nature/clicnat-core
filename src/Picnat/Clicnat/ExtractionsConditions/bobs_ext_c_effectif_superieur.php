<?php
namespace Picnat\Clicnat\ExtractionsConditions;

class bobs_ext_c_effectif_superieur extends bobs_extractions_conditions {
	protected $n;
	const poste = true;

	function __construct($n) {
		parent::__construct();
		$this->arguments[] = 'n';
		$this->n = bobs_tests::cli($n, bobs_element::except_si_vide);
	}

	public function  __toString() {
		return "Effectif supérieur à {$this->n}";
	}

	static public function get_titre() {
		return 'Effectif mini';
	}

	public function get_sql() {
		return sprintf('greatest(coalesce(citations.nb,0),coalesce(citations.nb_min,0)) >= %d and citations.nb != -1', $this->n);
	}

	public function get_tables() {
		return ['citations'];
	}

	public static function new_by_array($t) {
		return new self($t['n']);
	}

	public static function get_html() {
		return "
			<label for='lcond_eff_sup'>Effectif minimum</label>
			<input id='lcond_eff_sup' type='text' name='n' class='form-control' required=true>
		";
	}
}
