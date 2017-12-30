<?php
namespace Picnat\Clicnat;

class clicnat_depot_dechets_observations extends bobs_element {
	protected $id_observation;
	protected $id_depotoir;
	protected $date_observation;
	protected $documents_refs;
	protected $categories_dechets;
	protected $auteur;

	const __table__ = 'depotoirs_observations';
	const __prim__ = 'id_observation';
	const __seq__ = 'depotoirs_observations_id_observation_seq';

	const sql_ajoute_classe = 'update depotoirs_observations set categories_dechets = categories_dechets::text[]||ARRAY[$2] where id_observation=$1';
	const sql_ajoute_document = 'update depotoirs_observations set document_ref = document_ref::text[]||ARRAY[$2] where id_observation=$1';

	function __construct($db, $id) {
		parent::__construct($db, self::__table__, self::__prim__, $id);
		$this->champ_date_maj = 'date_modif';
	}

	public static function creer($db, $data) {
		$id_observation = self::nextval($db, self::__seq__);
		parent::insert($db, self::__table__, [
			"id_observation" => $id_observation,
			"id_depotoir" => $data['id_depotoir'],
			"date_observation" => $data["date_observation"],
		]);
		return $id_observation;
	}

	public function ajoute_classe($classe) {
		$q = bobs_qm()->query($this->db, 's_ajoute_classe', self::sql_ajoute_classe, [$this->id_observation, $classe]);
		if (!$q) throw new Exception();
		return true;
	}

	public function ajoute_document($doc_id) {
		$q = bobs_qm()->query($this->db, 's_ajoute_document', self::sql_ajoute_document, [$this->id_observation, $doc_id]);
		if (!$q) throw new Exception();
		return true;
	}

	public static function classes() {
		static $_c;
		if (!isset($_c)) {
			$_c = [
				'vegetaux' => 'végétaux',
				'metaux' => 'métaux',
				'plastiques' => 'plastiques, pvc',
				'gravats' => 'gravats',
				'bois' => 'bois, taille, mobilier, charpente',
				'batterie' => 'piles, batteries',
				'obus' => 'obus',
				'amiante' => 'tôle en amiante',
				'chimique' => 'peintures, huiles de vidange, produit chimique',
				'deee' => 'électroménager : four, réfrigérateur, machine à laver...',
				'sac_poub' => 'sac poubelle abandonné',
				'epave' => 'carcasse ou épave de voiture'
			];
		}
		return $_c;
	}
}
