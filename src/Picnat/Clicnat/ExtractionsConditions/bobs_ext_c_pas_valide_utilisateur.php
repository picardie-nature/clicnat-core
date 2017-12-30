<?php
namespace Picnat\Clicnat\ExtractionsConditions;

/**
 * @brief Citations pas évaluées par l'utilisateur
 */
class bobs_ext_c_pas_valide_utilisateur extends bobs_extractions_conditions{
	const poste = true;
	protected $id_utilisateur;

	function  __construct($id) {
		parent::__construct();
		$this->arguments[] = 'id_utilisateur';
		$this->id_utilisateur = bobs_tests::cli($id, bobs_tests::except_si_inf_1);
	}

	static public function get_titre() {
		return 'Pas déja validé par l\'utilisateur';
	}

	public function get_sql() {
		bobs_tests::cli($this->id_utilisateur, bobs_tests::except_si_inf_1);
		return sprintf('coalesce(not %d = ANY (citations.validation_avis_positif || citations.validation_avis_negatif || citations.validation_sans_avis), true)', $this->id_utilisateur);
	}

	public function get_tables()
	{
		return array('citations');
	}

	public static function new_by_array($t)
	{
		return new bobs_ext_c_pas_valide_utilisateur($t['id_utilisateur']);
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
