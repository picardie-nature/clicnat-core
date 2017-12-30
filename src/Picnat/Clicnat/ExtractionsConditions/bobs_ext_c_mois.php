<?php
namespace Picnat\Clicnat\ExtractionsConditions;

class bobs_ext_c_mois extends bobs_extractions_conditions {
	protected $mois;
	const poste = true;

	function  __construct($mois) {
		parent::__construct();
		$this->mois = $mois;
		$this->arguments[] = 'mois';
	}

	function  __toString() {
		return 'mois numéro '.$this->mois;
	}

	public static function get_titre() {
		return 'Mois';
	}

	public function get_sql() {
		return sprintf("extract('month' from observations.date_observation) = %02d", $this->mois);
	}

	public function get_tables() {
		return ['observations'];
	}

	public static function new_by_array($t) {
		return new bobs_ext_c_mois($t['mois']);
	}

	public static function get_html() {
		return "
			<label for='lcond_mois'>Mois de l'observation</label>
			<select id='lcond_mois' name='mois'>
				<option value='1'>Janvier</option>
				<option value='2'>Février</option>
				<option value='3'>Mars</option>
				<option value='4'>Avril</option>
				<option value='5'>Mai</option>
				<option value='6'>Juin</option>
				<option value='7'>Juillet</option>
				<option value='8'>Août</option>
				<option value='9'>Septembre</option>
				<option value='10'>Octobre</option>
				<option value='11'>Novembre</option>
				<option value='12'>Décembre</option>
			</select>
		";
	}
}
