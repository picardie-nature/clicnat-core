<?php
namespace Picnat\Clicnat\ExtractionsConditions;

class bobs_ext_c_effectif_inferieur extends bobs_ext_c_effectif_superieur {
	public function __toString() {
		return "Effectif inférieur à {$this->n}";
	}

	static public function get_titre() {
		return 'Effectif max';
	}

	public function get_sql() {
		return sprintf('greatest(coalesce(citations.nb,0),coalesce(citations.nb_min,0)) <= %d and greatest(coalesce(citations.nb,0),coalesce(citations.nb_max,0)) > 0 and citations.nb != -1', $this->n);
	}

	public static function new_by_array($t) {
		return new self($t['n']);
	}

	public static function get_html() {
		return "
			<label for='lcond_eff_inf'>Effectif maximum</label>
			<input id='lcond_eff_inf' type='text' name='n' class='form-control' required=true>
		";
	}
}
