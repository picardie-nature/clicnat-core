<?php
namespace Picnat\Clicnat\ExtractionsConditions;

class bobs_ext_c_auteur extends bobs_extractions_conditions {
	protected $id_utilisateur;
	const poste = true;

	function  __construct($id) {
		parent::__construct();
		$this->arguments[] = 'id_utilisateur';
		$this->id_utilisateur = $id==-1?-1:bobs_tests::cli($id, bobs_tests::except_si_vide);
	}

	static public function get_titre() {
		return 'Auteur';
	}

	public function get_sql() {
		return sprintf('observations.id_utilisateur=%d', $this->id_utilisateur);
	}

	public function get_tables() {
		return array('observations');
	}

	public static function new_by_array($t) {
		return new bobs_ext_c_auteur($t['id_utilisateur']);
	}

	public function  __toString() {
		try {
			$u = \Picnat\Clicnat\get_utilisateur($this->extraction->get_db(), $this->id_utilisateur);
			return "Saisie par : $u";
		} catch (\Exception $e) {
			return "Erreur dans ".__FILE__.' ligne '.__LINE__;
		}
	}

	public static function get_html() {
		return "
			<label for='lcond_auteur'>Auteur</label>
			<input id='lcond_auteur' type='text' name='id_utilisateur' class='autocomplete_utilisateur form-control' required=true/>
		";
	}
}
