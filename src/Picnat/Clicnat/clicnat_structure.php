<?php
namespace Picnat\Clicnat;

class clicnat_structure extends bobs_element {
	protected $id_stucture;
	protected $nom;
	protected $type_mad;
	protected $data;
	protected $date_creation;
	protected $date_modif;
	protected $txt_id;

	const types_mads = 'mad_classes,mad_communes,mad_espaces,mad_xml';
	const sql_ajout_membre = 'insert into structures_membres (id_structure,id_utilisateur) values ($1,$2)';
	const sql_ajout_diff_restreinte = 'insert into structures_diff_restreintes (id_structure, id_utilisateur) values ($1,$2)';
	const sql_suppr_membre = 'delete from structures_membres where id_structure=$1 and id_utilisateur=$2';
	const sql_suppr_diff_restreinte = 'delete from structures_diff_restreintes where id_structure=$1 and id_utilisateur=$2';
	const sql_liste_membre = 'select id_utilisateur from structures_membres where id_structure=$1';
	const sql_liste_diff_restreinte = 'select id_utilisateur from structures_diff_restreintes where id_structure=$1';
	const sql_liste = 'select id_structure from structures order by nom';
	const sql_nb_donnees_mad = 'select count(*) from structures_mad where id_structure=$1';
	const sql_espace_structures = 'select id_espace from espace_structure where structure=$1';
	const sql_id_citation_max = 'select max(id_citation) from structures_mad where id_structure=$1';
	const sql_maj_mad_utl = '
		with src as (
			select id_citation,$1::integer from structures_mad
			where id_structure=$2::integer
			and not exists (
				select id_citation from utilisateur_citations_ok
				where id_utilisateur=$3::integer
				and utilisateur_citations_ok.id_citation=structures_mad.id_citation
			)
		)
		insert into utilisateur_citations_ok (id_citation,id_utilisateur) select * from src';

	public function __construct($db, $id_structure) {
		parent::__construct($db, 'structures', 'id_structure', $id_structure);
		$this->champ_date_maj = 'date_modif';
	}

	public function nb_donnees_mad() {
		$q = bobs_qm()->query($this->db, 'nb_donnees_mad', self::sql_nb_donnees_mad, array($this->id_structure));
		$r = self::fetch($q);
		return $r['count'];
	}

	public static function nouvelle($db, $nom) {
		$id = self::nextval($db, 'structures_id_structure_seq');
		$data = array('id_structure'=> $id, "nom" => self::cls($nom, self::except_si_vide));
		self::insert($db, 'structures', $data);
		return $id;
	}

	/**
	 * @brief liste des membres de la structure
	 * @return clicnat_iterateur_utilisateur
	 */
	public function membres() {
		$q = bobs_qm()->query($this->db, 'struct_liste_membre', self::sql_liste_membre, array($this->id_structure));
		return new clicnat_iterateur_utilisateurs($this->db, array_column(self::fetch_all($q), 'id_utilisateur'));
	}

	/**
	 * @brief liste des membres en diffusion restreinte de la structure
	 * @return clicnat_iterateur_utilisateur
	 */
	public function diffusions_restreintes() {
		$q = bobs_qm()->query($this->db, 'struct_liste_diff_r', self::sql_liste_diff_restreinte, array($this->id_structure));
		return new clicnat_iterateur_utilisateurs($this->db, array_column(self::fetch_all($q), 'id_utilisateur'));
	}

	/**
	 * @brief test si utilisateur est membre
	 * @param $id_utilisateur identifiant de l'utilisateur
	 * @return boolean
	 */
	public function est_membre($id_utilisateur) {
		return in_array($id_utilisateur, $this->membres->ids());
	}

	/**
	 * @brief ajouter un membre à la structure
	 * @param $id_utilisateur identifiant de l'utilisateur
	 */
	public function ajoute_membre($id_utilisateur) {
		return bobs_qm()->query($this->db, 'struct_ajout_membre', self::sql_ajout_membre, array($this->id_structure, $id_utilisateur));
	}

	/**
	 * @brief ajout un utilisateur en diffusion restreinte
	 * @param $id_utilisateur identifiant de l'utilisateur
	 */
	public function ajoute_diffusion_restreinte($id_utilisateur) {
		return bobs_qm()->query($this->db, 'struct_ajout_diff_r', self::sql_ajout_diff_restreinte, array($this->id_structure, $id_utilisateur));
	}

	/**
	 * @brief retirer un membre à la structure
	 * @param $id_utilisateur identifiant de l'utilisateur
	 */
	public function supprime_membre($id_utilisateur) {
		return bobs_qm()->query($this->db, 'struct_suppr_membre', self::sql_suppr_membre, array($this->id_structure, $id_utilisateur));
	}

	/**
	 * @brief retirer un utilisateur en diffusion restreinte
	 * @param $id_utilisateur identifiant de l'utilisateur
	 */
	public function supprime_diffusion_restreinte($id_utilisateur) {
		return bobs_qm()->query($this->db, 'struct_suppr_diff_r', self::sql_suppr_diff_restreinte, array($this->id_structure, $id_utilisateur));
	}

	public function __toString() {
		return $this->nom;
	}


	/**
	 * @brief le numéro de citation le plus grand de la MAD
	 * @return int
	 */
	public function dernier_id_citation() {
		$q = bobs_qm()->query($this->db, 'struct_id_citation_max', self::sql_id_citation_max, array($this->id_structure));
		$r = self::fetch($q);
		echo "Dernier id = {$r['max']}\n";
		return $r['max'];
	}

	/**
	 * @brief maj de liste les données mise à disposition de la structure
	 */
	public function mad_structure($reset=false,$verb=false) {
		$cmin = false;
		if (!$reset) {
			$cmin = new bobs_ext_c_id_citation_min($this->dernier_id_citation());
		}
		$extractions = $this->get_extractions();
		$n = count($extractions);
		if ($verb) echo "$n extractions\n";
		foreach ($this->get_extractions() as $e) {
			if ($cmin) $e->ajouter_condition($cmin);
			$e->autorise_structure($this->id_structure);
		}
	}

	/**
	 * @brief distribution des données de la structure à l'utilisateur
	 * @param $utilisateur instance de bobs_utilisateur
	 */
	public function mad($utilisateur) {
		$data = array($utilisateur->id_utilisateur, $this->id_structure, $utilisateur->id_utilisateur);
		$q = bobs_qm()->query($this->db, 'maj_mad_utl_s', self::sql_maj_mad_utl, $data);
		return $q;
	}

	public function types_mads() {
		return explode(',', self::types_mads);
	}

	public static function structures($db) {
		$q = bobs_qm()->query($db, 'liste_structures', self::sql_liste, array());
		return new clicnat_iterateur_structure($db, array_column(self::fetch_all($q),'id_structure'));
	}

	public function __get($champ) {
		switch ($champ) {
			case 'type_mad':
				if (empty($this->type_mad)) return false;
				else return $this->type_mad;
			case 'id_structure':
				return $this->$champ;
			case 'data':
				$t = json_decode($this->data, true);
				if (!$t) return array();
				else return $t;
			case 'txt_id':
				return $this->txt_id;
			default:
				throw new Exception("propriété $champ inaccessible");
		}
	}

	/**
	 * @brief si type_mad=mad_classes test si la classe est associé à la structure
	 * @return boolean
	 */
	public function a_classe($classe) {
		if ($this->type_mad != "mad_classes")
			throw new Exception("utiliser uniquement pour mad par classes");
		$data = $this->__get("data");
		if (isset($data['classes'])) {
			return in_array($classe, $data['classes']);
		}
		return false;
	}


	public function espaces_structures() {
		if ($this->type_mad != "mad_espaces")
			throw new Exception("utiliser uniquement pour mad par classes");
		$data = $this->__get("data");
		if (!array_key_exists('ref_espace_structure', $data))
			return array();
		$q = bobs_qm()->query($this->db, "sel_l_esp_structures", self::sql_espace_structures, array($data['ref_espace_structure']));
		$r = self::fetch_all($q);
		$ids = array();
		foreach ($r as $e) {
			$ids[] = array('espace_table'=>'espace_structure', 'id_espace'=>$e['id_espace']);
		}
		return new clicnat_iterateur_espaces($this->db, $ids);
	}

	public function communes() {
		if ($this->type_mad != "mad_communes")
			throw new Exception("utiliser uniquement pour mad par communes");
		$data = $this->__get("data");
		$it_data = array();
		if (isset($data['communes'])) {
			foreach ($data['communes'] as $id)
				$it_data[] = array("espace_table"=>"espace_commune", "id_espace"=>$id);
		}
		return new clicnat_iterateur_espaces($this->db, $it_data);
	}

	public function get_extractions() {
		$extractions = array();
		switch ($this->type_mad) {
			case 'mad_classes':
				$extraction = new clicnat_extractions_mad_structure($this->db);
				$data = $this->__get('data');
				if (isset($data['classes'])) {
					$n = 0;
					foreach ($data['classes'] as $classe) {
						$extraction->ajouter_condition(new bobs_ext_c_classe($classe));
						$n++;
					}
					if ($n > 0) $extractions[] = $extraction;
				}
				break;
			case 'mad_communes':
				$extraction = new clicnat_extractions_mad_structure($this->db);
				foreach ($this->communes() as $commune) {
					$extraction->ajouter_condition(new bobs_ext_c_commune($commune->id_espace));
				}
				$extractions[] = $extraction;
				break;
			case 'mad_espaces':
				$espaces_tables = array('espace_point', 'espace_chiro', 'espace_line', 'espace_polygon');
				foreach ($espaces_tables as $espace_table) {
					$extraction = new clicnat_extractions_mad_structure($this->db);
					$n = 0;
					foreach ($this->espaces_structures() as $espace_struct) {
						$c = new bobs_ext_c_poly('espace_structure', $espace_table, $espace_struct->id_espace);
						$extraction->ajouter_condition($c);
						$n++;
					}
					if ($n > 0) $extractions[] = $extraction;
				}
				break;
			case 'mad_xml':
				$data = $this->__get('data');
				$extractions[] = clicnat_extractions_mad_structure::charge_xml($this->db, $data['xml']);
				break;
		}
		if (count($extractions) > 0) {
			$diff_r = $this->diffusions_restreintes();
			if ($diff_r->count() > 0) {
				foreach ($extractions as $e) {
					$e->ajouter_condition(new bobs_ext_c_sans_diffusion_restreinte($diff_r->ids()));
				}
			}
		} else {
			throw new Exception('Aucune extraction générée');
		}
		if (!empty($this->txt_id)) {
			$extraction = new clicnat_extractions_mad_structure($this->db);
			$extraction->ajouter_condition(new bobs_ext_c_tag_structure($this->txt_id));
			$extractions[] = $extraction;
		}
		return $extractions;
	}
}
