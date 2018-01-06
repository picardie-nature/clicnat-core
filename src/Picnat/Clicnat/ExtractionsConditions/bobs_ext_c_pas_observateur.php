<?php
namespace Picnat\Clicnat\ExtractionsConditions;
use Picnat\Clicnat\bobs_tests;

/**
 * @brief Citations pas réalisées par l'utilisateur
 */
class bobs_ext_c_pas_observateur extends bobs_extractions_conditions{
	const poste = true;
	protected $id_utilisateur;

	function  __construct($id) {
		parent::__construct();
		$this->arguments[] = 'id_utilisateur';
		$this->id_utilisateur = bobs_tests::cli($id, bobs_tests::except_si_inf_1);
	}

	static public function get_titre() {
		return 'Observations non réalisées par l\'utilisateur';
	}

	public function get_sql() {
		bobs_tests::cli($this->id_utilisateur, bobs_tests::except_si_inf_1);
		return sprintf('citations.id_observation not in (select observations_observateurs.id_observation from observations_observateurs where observations_observateurs.id_utilisateur = %d)', $this->id_utilisateur);
	}

	public function get_tables() {
		return ['citations'];
	}

	public static function new_by_array($t) {
		return new self($t['id_utilisateur']);
	}

	public function  __toString() {
		try {
			$u = get_utilisateur($this->extraction->get_db(), $this->id_utilisateur);
			return $u->__toString();
		} catch (Exception $e) {
			return "Erreur dans ".__FILE__.' ligne '.__LINE__;
		}
	}
}
