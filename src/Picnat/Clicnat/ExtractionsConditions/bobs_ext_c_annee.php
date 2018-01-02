<?php
namespace Picnat\Clicnat\ExtractionsConditions;
use Picnat\Clicnat\bobs_element;

class bobs_ext_c_annee extends bobs_extractions_conditions {
	protected $annee;
	const poste = true;

	function __construct($annee) {
		parent::__construct();
		bobs_element::cli($annee);
		if (empty($annee)) {
			throw new \Exception("année vide");
		}
		$this->arguments[] = 'annee';
		$this->annee = $annee;
	}

	public function  __toString() {
		return 'Année '.$this->annee;
	}

	static public function get_titre() {
		return 'Année';
	}

	public function get_sql() {
		return sprintf("extract('year' from observations.date_observation) = %04d", $this->annee);
	}

	public function get_tables() {
		return ['observations'];
	}

	public static function new_by_array($t) {
		return new self($t['annee']);
	}

	public static function get_html() {
		return "
			<label for='lcond_annee'>Année de l'observation</label>
			<input id='lcond_annee' type='text' name='annee' class='form-control' required=true>
		";
	}
}
