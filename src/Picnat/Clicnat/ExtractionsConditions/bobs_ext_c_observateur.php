<?php
namespace Picnat\Clicnat\ExtractionsConditions;

use Picnat\Clicnat\bobs_tests;

class bobs_ext_c_observateur extends bobs_extractions_conditions {
	protected $id_utilisateur;
	const poste = true;

	function __construct($id) {
		parent::__construct();
		$this->arguments[] = 'id_utilisateur';
		$this->id_utilisateur = bobs_tests::cli($id, bobs_tests::except_si_inf_1);
	}

	static public function get_titre() {
		return 'Observateur';
	}

	public function get_sql() {
		bobs_tests::cli($this->id_utilisateur, bobs_tests::except_si_inf_1);
		return sprintf('observations_observateurs.id_utilisateur=%d', $this->id_utilisateur);
	}

	public function get_tables() {
		return ['observations_observateurs'];
	}

	public static function new_by_array($t) {
		return new bobs_ext_c_observateur($t['id_utilisateur']);
	}

	public function  __toString() {
		try {
			$u = get_utilisateur($this->extraction->get_db(), $this->id_utilisateur);
			return "Observateur : $u";
		} catch (Exception $e) {
			return "Erreur dans ".__FILE__.' ligne '.__LINE__;
		}
	}

	public static function get_html() {
		return "
			<label for='lcond_observateur'>Auteur</label>
			<input id='lcond_observateur' type='text' name='id_utilisateur' class='autocomplete_utilisateur form-control' required=true/>
		";
	}
}
