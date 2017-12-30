<?php
namespace Picnat\Clicnat;

/**
 * @brief visite d'un nid d'hirondelle
 * @see clicnat_espace_hirondelle
 */
class clicnat_visite_espace_hirondelle extends bobs_element {
	/** @brief pk */
	protected $id_visite_nid;

	/** @brief date de la derniere modificiation de l'enregistrement */
	protected $date_modif;

	/** @vrief date de création de l'enregistrement */
	protected $date_creation;

	/** @brief date de la visite */
	protected $date_visite_nid;

	/** @brief identifiant du nid */
	protected $id_espace;

	/** @brief nombre de nids occupés par hirondelles rustique */
	protected $n_nid_occupe_r;

	/** @brief nombre de nids vides d'hirondelles rustique */
	protected $n_nid_vide_r;

	/** @brief nombre de nids détruits d'hironndelles rustique */
	protected $n_nid_detruit_r;

	/** @brief nombre de nids occupés par hirondelles de rivage */
	protected $n_nid_occupe_ri;

	/** @brief nombre de nids vides d'hirondelles de rivage */
	protected $n_nid_vide_ri;

	/** @brief nombre de nids détruits d'hironndelles de rivage */
	protected $n_nid_detruit_ri;

	/** @brief nombre de nids occupés par hirondelles de fenêtre */
	protected $n_nid_occupe_f;

	/** @brief nombre de nids vides d'hirondelles de fenêtre */
	protected $n_nid_vide_f;

	/** @brief nombre de nids détruits d'hirondelle de fenêtre */
	protected $n_nid_detruit_f;

	/** @brief visiteur */
	protected $id_utilisateur;

	/** @brief id observation associée **/
	protected $id_observation;

	const table = "visite_espace_hirondelle";

	public function __get($k) {
		$champs_ok = [
			'id_visite_nid',
			'date_modif',
			'date_creation',
			'date_visite_nid',
			'id_espace',
			'n_nid_occupe_r',
			'n_nid_vide_r',
			'n_nid_detruit_r',
			'n_nid_occupe_ri',
			'n_nid_vide_ri',
			'n_nid_detruit_ri',
			'n_nid_occupe_f',
			'n_nid_vide_f',
			'n_nid_detruit_f',
			'id_utilisateur',
			'id_observation'
		];
		if (in_array($k,$champs_ok))
			return $this->$k;
		else
			throw new InvalidArgumentException("$k existe pas");
	}

	function __construct($db, $id_visite_nid) {
		if (empty($id_visite_nid)) {
			throw new InvalidArgumentException("identifiant vide");
		}
		parent::__construct($db, "visite_espace_hirondelle", "id_visite_nid", $id_visite_nid);
		$this->champ_date_maj = 'date_modif';
	}

	public static function insert($db,$table,$data) {
		$data['id_visite_nid'] = self::nextval($db, "visite_espace_hirondelle_id_visite_nid_seq");
		parent::insert($db, $table, $data);
		return $data['id_visite_nid'];
	}

	/**
	 * @brief associer une observation à cette visite
	 * @param $observation une instance de bobs_observation
	 * @return boolean le résultat de l'association
	 */
	public function observation_ajouter($observation) {
		 return self::update_field('id_observation',$observation->id_observation);
	}

	/**
	 * @brief lister les citations associées à cette visite
	 * @return clicnat_iterateur_citations
	 */
	public function citations() {
		if($this->id_observation){
			$observation = get_observation($this->db,$this->id_observation);
			return $observation->get_citations();
		}
		return [];
	}

	/**
	 * @brief calcul le nombre de jeunes
	 * @todo mettre les codes espèces dans des constantes pour le projet hirondelles
	 * @todo retester
	 */
	public function nb_jeunes(){
		$nbs = ['nb_j_r' => 0,'nb_j_ri' => 0, 'nb_j_f' => 0];
		foreach ($this->citations() as $citation){
			if ('JUV' == $citation->age){
				switch ($citation->id_espece) {
					case CLICNAT_HIRONDELLE_ID_ESPECE_FENETRE :
						$nbs['nb_j_f'] += $citation->nb;
						break;
					case CLICNAT_HIRONDELLE_ID_ESPECE_RIVIERE :
						$nbs['nb_j_ri'] += $citation->nb;
						break;
					case CLICNAT_HIRONDELLE_ID_ESPECE_RUSTIQUE :
						$nbs['nb_j_r'] += $citation->nb;
						break;
				}
			}
		}
		return $nbs;
	}

	public function document_ajouter($doc_id) {
	}
}
