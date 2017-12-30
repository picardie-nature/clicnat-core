<?php
namespace Picnat\Clicnat\ExtractionsConditions;

class bobs_ext_c_ref_menace extends bobs_extractions_conditions {
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
			return "menace : aucun critère sélectionné";

		return 'degrés de menace : '.$this->criteres;
	}

	public static function get_titre() {
		return "Critère de menace";
	}

	public function get_tables() {
		return array('referentiel_regional');
	}

	public function get_sql() {
		return "referentiel_regional.categorie in ({$this->criteres})";
	}

	public static function new_by_array($t) {
		if (!array_key_exists('criteres', $t)) {
			$retenues = array();
			$degs = bobs_espece::liste_degre_menace();
			foreach ($degs as $degr) {
				if (isset($t[$degr]))
				$retenues[] = $degr;
			}
			return new bobs_ext_c_ref_menace($retenues);
		} else {
			$t = explode(',',$t['criteres']);
			foreach ($t as $k=>$v) {
				$t[$k] = trim($v,"'");
			}
			return new bobs_ext_c_ref_menace($t);
		}
	}

	public static function get_html() {
		$degs = bobs_espece::liste_degre_menace();
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
