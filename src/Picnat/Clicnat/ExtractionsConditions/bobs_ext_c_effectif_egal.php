<?php
namespace Picnat\Clicnat\ExtractionsConditions;

class bobs_ext_c_effectif_egal extends bobs_ext_c_effectif_superieur {
	public function __toString() {
		return "Effectif égal à {$this->n}";
	}

	static public function get_titre() {
		return 'Effectif';
	}

	public function get_sql() {
		return sprintf('(coalesce(citations.nb,0) = %d or %d between nb_min and nb_max)', $this->n, $this->n);
	}

	public static function new_by_array($t) {
		return new self($t['n']);
	}

	public static function get_html() {
		return "
			<label for='lcond_eff_eq'>Effectif égal à</label>
			<input id='lcond_eff_eq' type='text' name='n' class='form-control' required=true>
		";
	}
}
