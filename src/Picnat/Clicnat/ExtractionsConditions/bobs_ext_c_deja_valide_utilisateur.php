<?php
namespace Picnat\Clicnat\ExtractionsConditions;
use Picnat\Clicnat\bobs_tests;

/**
 * @brief Citations déja évaluées par l'utilisateur
 */
class bobs_ext_c_deja_valide_utilisateur extends bobs_extractions_conditions{
	protected $id_utilisateur;

	function  __construct($id) {
		parent::__construct();
		$this->arguments[] = 'id_utilisateur';
		$this->id_utilisateur = bobs_tests::cli($id, bobs_tests::except_si_inf_1);
	}

	static public function get_titre() {
		return 'Déja validé par l\'utilisateur';
	}

	public function get_sql() {
		bobs_tests::cli($this->id_utilisateur, bobs_tests::except_si_inf_1);
		return sprintf('citations.validation_avis_positif || citations.validation_avis_negatif || citations.validation_sans_avis @> ARRAY[%d]', $this->id_utilisateur);
	}

	public function get_tables()
	{
		return array('citations');
	}

	public static function new_by_array($t)
	{
		return new bobs_ext_c_deja_valide_utilisateur($t['id_utilisateur']);
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
