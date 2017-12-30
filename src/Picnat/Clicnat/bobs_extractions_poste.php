<?php
namespace Picnat\Clicnat;

class bobs_extractions_poste extends bobs_extractions {
	/**
	 * integer utilisateur
	 */
	protected $id_utilisateur;
	private $utilisateur;

	const classe_c_poste = 'bobs_ext_c_poste';

	/**
	 *
	 * @param handler $db
	 * @param integer $id_utilisateur
	 */
	function __construct($db, $id_utilisateur) {
		parent::__construct($db);
		$this->id_utilisateur = $id_utilisateur;
		$this->utilisateur = get_utilisateur($db, $id_utilisateur);
		$this->ajouter_condition(new bobs_ext_c_poste($id_utilisateur));
	}

	static public function get_conditions_dispo() {
		// retirer les conditions qu'on ne veut pas présenter
		// dans le poste
		$conditions = parent::get_conditions_dispo();
		foreach ($conditions as $classe => $obj) {
			if (!eval("return $classe::poste;"))
				unset($conditions[$classe]);
		}
		return $conditions;
	}

	private function choix_filtre() {
		foreach ($this->conditions as $k=>$c) {
			if (get_class($c) == self::classe_c_poste) {
				parent::retirer_condition($k);
				break;
			}
		}

		$n_resultat = $this->compte_ready();
		$utilisateur = get_utilisateur($this->db, $this->id_utilisateur);
		$n_accessible = $utilisateur->get_nb_citations_authok();
		$this->ajouter_condition(new bobs_ext_c_poste($this->id_utilisateur, $n_resultat>$n_accessible));
	}

	public function retirer_condition($condition_index) {
		if (get_class($this->conditions[$condition_index]) == self::classe_c_poste)
			return false;
		return parent::retirer_condition($condition_index);
	}

	public function ready() {
		$this->choix_filtre();
		return parent::ready(1);
	}

	public static function charge_xml($db, $xml, $id_utilisateur) {
		return parent::charge_xml($db, $xml, $id_utilisateur);
	}
}
