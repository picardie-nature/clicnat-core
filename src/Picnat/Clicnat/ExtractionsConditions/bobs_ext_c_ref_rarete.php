<?php
namespace Picnat\Clicnat\ExtractionsConditions;

class bobs_ext_c_ref_rarete extends bobs_extractions_conditions {
	protected $criteres;
	const poste = true;

	function  __construct($criteres) {
		parent::__construct();
		$this->arguments[] = 'criteres';

		if (is_array($criteres)) {
			$s = '';
			foreach ($criteres as $critere) {
				$s .= "'{$critere}',";
			}
			$criteres = trim($s, ',');
		}
		$this->criteres = $criteres;
	}

	function  __toString() {
		if (empty($this->criteres))
			return "rareté : aucun critère sélectionné";

		return 'degrés de rareté : '.$this->criteres;
	}

	public static function get_titre() {
		return "Critère de rareté";
	}

	public function get_tables() {
		return ['referentiel_regional'];
	}

	public function get_sql() {
		return "referentiel_regional.indice_rar in ({$this->criteres})";
	}

	public static function new_by_array($t) {
		$retenues = [];
		$degs = bobs_espece::liste_indice_rar();
		foreach ($degs as $degr) {
			if (isset($t[$degr]))
			$retenues[] = $degr;
		}
		return new self($retenues);
	}

	public static function get_html() {
		$degs = bobs_espece::liste_indice_rar();
		$r = "";
		foreach ($degs as $deg) {
			$r .= "
				<div>
					<input type='checkbox' name='{$deg}' value='{$deg}'/>
					<label for='lcond_rar{$deg}'>{$deg}</label>
				</div>
			";
		}
		return $r;
	}
}
