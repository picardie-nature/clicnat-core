<?php
namespace Picnat\Clicnat\ExtractionsConditions;

class bobs_ext_c_indice_qualite extends bobs_extractions_conditions {
	protected $criteres;
	const poste = true;

	function __construct($criteres) {
		parent::__construct();
		$this->arguments[] = 'criteres';

		$s = '';
		if (is_array($criteres)) {
			foreach ($criteres as $critere) {
				$s .= sprintf("%d,", $critere);
			}
		}

		$this->criteres = trim($s,',');
	}

	function __toString() {
		if (empty($this->criteres))
			return "Fiabilité dans l'identification, aucun sélectionné";

		return "Fiabitilité de l'identification : {$this->criteres}";
	}

	public static function get_titre() {
		return "Fiabilité dans l'identification";
	}

	public function get_tables() {
		return array('citations');
	}

	public function get_sql() {
		if (empty($this->criteres)) return "1=2";
		return "coalesce(indice_qualite,4) in ({$this->criteres})";
	}

	public static function new_by_array($t) {
		if (!is_array($t['criteres'])) $t['criteres'] = explode(',',$t['criteres']);
		$criteres = array();
		for ($i=1;$i<=4;$i++) {
			if (array_search($i, $t['criteres']) !== false)
				$criteres[] = $i;
		}
		return new bobs_ext_c_indice_qualite($criteres);
	}

	public static function get_html() {
		return "
			<div>
				<input id='lcond_iq4' type='checkbox' value='4'/>
				<label for='lcond_iq4'>très fort</label>
			</div>
			<div>
				<input id='lcond_iq3' type='checkbox' value='3'/>
				<label for='lcond_iq3'>fort</label>
			</div>
			<div>
				<input id='lcond_iq2' type='checkbox' value='2'/>
				<label for='lcond_iq2'>moyen</label>
			</div>
			<div>
				<input id='lcond_iq1' type='checkbox' value='1'/>
				<label for='lcond_iq1'>faible</label>
			</div>
		";
	}
}
