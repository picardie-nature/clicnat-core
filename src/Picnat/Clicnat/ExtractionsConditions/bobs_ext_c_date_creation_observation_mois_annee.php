<?php
namespace Picnat\Clicnat\ExtractionsConditions;

class bobs_ext_c_date_creation_observation_mois_annee extends bobs_extractions_conditions {
	const poste = true;
	protected $mois;
	protected $annee;

	function __construct($mois, $annee) {
		parent::__construct();
		$this->arguments[] = 'mois';
		$this->arguments[] = 'annee';
		if ($mois < 1 || $mois > 12) {
			throw new \Exception("mois invalide");
		}
		$this->mois = (int)$mois;
		$this->annee = (int)$annee;
	}

	public function  __toString() {
		return "créée en {$this->mois}/{$this->annee}";
	}

	public static function get_titre() {
		return "Date de création dans la base par mois";
	}

	public function get_sql() {
		return sprintf("extract('year' from observations.date_creation) = $this->annee and extract('month' from observations.date_creation) = $this->mois");
	}

	public static function new_by_array($t) {
		return new self($t['mois'], $t['annee']);
	}

	public function get_tables() {
		return ['observations'];
	}
}
