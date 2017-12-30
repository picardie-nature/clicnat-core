<?php
namespace Picnat\Clicnat;

class clicnat2_reseau extends bobs_element implements i_clicnat_reseau, \JsonSerializable {
	protected $id;
	protected $nom;
	protected $id_gdtc;
	protected $restitution_nom_s;
	protected $restitution_nombre_jours;
	protected $restitution_auto;
	protected $date_modif;
	protected $date_creation;

	const sql_liste_reseaux = 'select * from reseau order by nom';
	const sql_n_especes = 'select coalesce(count(*),0) as n from reseau';
	const sql_l_coordinateurs = '
		select reseau_coordinateurs.* from reseau_coordinateurs,utilisateur
		where id_reseau=$1 and reseau_coordinateurs.id_utilisateur=utilisateur.id_utilisateur
		order by utilisateur.nom,utilisateur.prenom,utilisateur.id_utilisateur';
	const sql_l_validateurs = '
		select distinct utilisateur.id_utilisateur,id_espece,utilisateur.nom,utilisateur.prenom
		from reseau_validateurs,utilisateur
		where id_reseau=$1 and reseau_validateurs.id_utilisateur=utilisateur.id_utilisateur
		order by utilisateur.nom,utilisateur.prenom,utilisateur.id_utilisateur';
	const sql_l_branches = 'select rbe.*,borne_a from reseau_branche_especes rbe,especes e
		where rbe.id_espece=e.id_espece
		and rbe.id_reseau=$1
		order by borne_a';
	const sql_l_membres = 'select reseau_membres.*,nom,prenom from reseau_membres,utilisateur
		where id_reseau=$1 and reseau_membres.id_utilisateur=utilisateur.id_utilisateur
		order by nom,prenom';
	const sql_l_reseaux_u = 'select reseau.* from reseau,reseau_membres
		where id_utilisateur=$1
		and reseau.id=reseau_membres.id_reseau
		order by reseau.nom';
	const sql_l_branches_validateur = 'select distinct id_espece from reseau_validateurs
		where id_reseau=$1
		and id_utilisateur=$2';

	const sql_ajout_coordinateur = 'insert into reseau_coordinateurs (id_reseau,id_utilisateur) values ($1,$2)';
	const sql_ajout_validateur = 'insert into reseau_validateurs (id_reseau,id_utilisateur,id_espece) values ($1,$2,$3)';
	const sql_ajout_branche = 'insert into reseau_branche_especes (id_reseau,id_espece) values ($1,$2)';
	const sql_ajout_membre = 'insert into reseau_membres (id_reseau,id_utilisateur) values ($1,$2)';

	const sql_suppr_coordinateur = 'delete from reseau_coordinateurs where id_reseau=$1 and id_utilisateur=$2';
	const sql_suppr_validateur = 'delete from reseau_validateurs where id_reseau=$1 and id_utilisateur=$2 and id_espece=$3';
	const sql_suppr_branche = 'delete from reseau_branche_especes where id_reseau=$1 and id_espece=$2';
	const sql_suppr_membre = 'delete from reseau_membres where id_reseau=$1 and id_utilisateur=$2';

	/**
	 * @param $id identifiant du réseau sur 2 lettres
	 */
	function __construct($db, $id, $table='reseau') {
		parent::__construct($db, $table, 'id', $id);
	}

	public function __toString() {
		return $this->nom;
	}

	public function jsonSerialize() {
		return [
			"id" => $this->id,
			"nom" => $this->nom
		];
	}

	public function getInstance($db, $id, $table='reseau', $pk='id') {
		static $instances;
		if (!isset($instances))
			$instances = [];

		if (is_array($id)) {
			if (!isset($id[$pk])) {
				throw new Exception("le tableau \$id n'a pas de clé $pk");
			}
			$__id = $id[$pk];
		} else {
			$__id = $id;
		}

		if (!isset($instances[$__id]))
			$instances[$__id] = new self($db, $id);

		return $instances[$__id];
	}

	public function __get($prop) {
		switch ($prop) {
			case 'id':
				return $this->id;
			case 'id_gdtc':
				return $this->id_gdtc;
			case 'restitution_nombre_jours':
				return $this->restitution_nombre_jours;
			case 'restitution_nom_s':
				return $this->restitution_nom_s == 't';
			case 'restitution_f_ec':
				return "/tmp/{$this->id}_ec.html";
			case 'restitution_f_ce':
				return "/tmp/{$this->id}_ce.html";
			case 'restitution_f_li':
				return "/tmp/{$this->id}_li.html";
			case 'restitution_auto':
				return $this->restitution_auto == 't';
			case 'nom':
				return $this->nom;
			case 'coordinateurs':
				return $this->__coordinateurs();
			case 'validateurs':
				return $this->__validateurs();
			case 'where':
				$ws = [];
				foreach ($this->liste_branches_especes() as $branche) {
					$borne_a = $branche->borne_a;
					if (empty($borne_a))
						throw new Exception("Branche borne_a vide reseau::__get('where') id_espece={$branche->id_espece}");

					$ws[] = "(especes.borne_a>={$branche->borne_a} and especes.borne_b<={$branche->borne_b})";
				}
				if (count($ws) == 0) throw new Exception("Pas de branches dans le réseau {$this->id}");
				return "(".join(" or ", $ws).")";
		}
	}

	private $l_coordinateurs;

	private function __coordinateurs() {
		if (!isset($this->l_coordinateurs)) {
			$q = bobs_qm()->query($this->db, 'reseau_l_coord', self::sql_l_coordinateurs, [$this->id]);
			$this->l_coordinateurs = new clicnat_iterateur_utilisateurs($this->db, array_column(self::fetch_all($q), 'id_utilisateur'));
		}
		return $this->l_coordinateurs;
	}

	private $l_validateurs;

	private function __validateurs() {
		if (!isset($this->l_validateurs)) {
			$q = bobs_qm()->query($this->db, 'reseau_l_valid', self::sql_l_validateurs, [$this->id]);
			$ids = self::fetch_all($q);
			$this->l_validateurs = new clicnat_iterateur_validateurs($this->db, $ids);
		}
		return $this->l_validateurs;
	}

	public static function liste_reseaux($db) {
		$q = bobs_qm()->query($db, 'rliste_reseaux', self::sql_liste_reseaux, []);
		$reseaux = [];
		while ($r = self::fetch($q)) {
			$reseaux[] = self::getInstance($db, $r);
		}
		return $reseaux;
	}

	public static function liste_reseaux_membre($db, $utilisateur) {
		$id_utilisateur = is_object($utilisateur)?$utilisateur->id_utilisateur:(int)$utilisateur;
		$reseaux = [];
		$q = bobs_qm()->query($db, 'rliste_reseauxm', self::sql_l_reseaux_u, [$id_utilisateur]);
		while ($r = self::fetch($q)) {
			$reseaux[] = self::getInstance($db,$r);
		}
		return $reseaux;
	}


	public function liste_branches_especes() {
		$q = bobs_qm()->query($this->db, 'rliste_branches', self::sql_l_branches, [$this->id]);
		return new clicnat_iterateur_especes($this->db, array_column(self::fetch_all($q), 'id_espece'));
	}

	public function liste_branches_validateur($utilisateur) {
		$id_utilisateur = is_object($utilisateur)?$utilisateur->id_utilisateur:(int)$utilisateur;
		$q = bobs_qm()->query($this->db, 'rliste_branche', self::sql_l_branches_validateur, [$this->id, $id_utilisateur]);
		return new clicnat_iterateur_especes($this->db, array_column(self::fetch_all($q), 'id_espece'));
	}

	private $__liste_especes;

	public function liste_especes() {
		if (!isset($this->__liste_especes)) {
			$ids = [];
			foreach ($this->liste_branches_especes() as $branche) {
				$taxons = $branche->taxons_descendants();
				$ids = array_merge($ids, $taxons->ids());
			}
			$this->__liste_especes = new clicnat_iterateur_especes($this->db, $ids);
		}
		return $this->__liste_especes;
	}

	/**
	 * @deprecated
	 * @see liste_especes()
	 */
	public function get_liste_especes() {
		return $this->liste_especes;
	}

	public function get_n_especes() {
		$q = bobs_qm()->query($this->db, 'reseau_n_especes', self::sql_n_especes, []);
		$r = self::fetch($q);
		return $r['n'];
	}

	public function get_id() {
		return $this->id;
	}


	/**
	 * @brief Espèce présente dans le réseau
	 * @return boolean
	 */
	public function espece_dans_le_reseau($id_espece) {
		$espece = get_espece($this->db, $id_espece);
		foreach ($this->liste_branches_especes() as $esp_branche) {
			if (($esp_branche->borne_a <= $espece->borne_a) && ($esp_branche->borne_b >= $espece->borne_a))
				return true;
		}
		return false;
	}

	public static function get_reseau_espece($db, $id_espece) {
		foreach (self::liste_reseaux($db) as $reseau) {
			if ($reseau->espece_dans_le_reseau($id_espece)) {
				return $reseau;
			}
		}
		return false;
	}

	public function est_coordinateur($utilisateur) {
		$id_utilisateur = is_object($utilisateur)?$utilisateur->id_utilisateur:(int)$utilisateur;
		$liste = $this->__coordinateurs();
		return $liste->in_array($id_utilisateur);
	}

	public function est_validateur($utilisateur) {
		$id_utilisateur = is_object($utilisateur)?$utilisateur->id_utilisateur:(int)$utilisateur;
		$liste = $this->__validateurs();
		return $liste->in_array($id_utilisateur);
	}

	public function ajouter_coordinateur($utilisateur) {
		$id_utilisateur = is_object($utilisateur)?$utilisateur->id_utilisateur:(int)$utilisateur;
		if (empty($id_utilisateur)) throw new Exception('id ne peut être vide');
		bobs_log("réseau ajout coordinateur $id_utilisateur sur le réseau {$this->id}");
		return bobs_qm()->query($this->db, 'r_ajout_coordinateur', self::sql_ajout_coordinateur, [$this->id, $id_utilisateur]);
	}

	public function ajouter_validateur($utilisateur, $espece) {
		$id_utilisateur = is_object($utilisateur)?$utilisateur->id_utilisateur:(int)$utilisateur;
		$id_espece = is_object($espece)?$espece->id_espece:(int)$espece;
		bobs_log("ajout validateur $id_utilisateur sur le réseau {$this->id} peut valider branche $id_espece");
		return bobs_qm()->query($this->db, 'r_ajout_espece', self::sql_ajout_validateur, [$this->id, $id_utilisateur, $id_espece]);
	}

	public function ajouter_branche($espece) {
		$id_espece = is_object($espece)?$espece->id_espece:(int)$espece;
		bobs_log("ajout branche $id_espece sur le réseau {$this->id}");
		return bobs_qm()->query($this->db, 'r_ajout_branche', self::sql_ajout_branche, [$this->id, $id_espece]);
	}

	public function retirer_coordinateur($utilisateur) {
		$id_utilisateur = is_object($utilisateur)?$utilisateur->id_utilisateur:(int)$utilisateur;
		if (empty($id_utilisateur)) throw new Exception('id ne peut être vide');
		bobs_log("retrait coordinateur $id_utilisateur du réseau {$this->id}");
		return bobs_qm()->query($this->db, 'r_suppr_coordinateur', self::sql_suppr_coordinateur, [$this->id, $id_utilisateur]);
	}

	public function retirer_validateur($utilisateur, $espece) {
		$id_utilisateur = is_object($utilisateur)?$utilisateur->id_utilisateur:(int)$utilisateur;
		$id_espece = is_object($espece)?$espece->id_espece:(int)$espece;
		bobs_log("retrait validateur $id_utilisateur sur le réseau {$this->id} branche $id_espece");
		return bobs_qm()->query($this->db, 'r_suppr_espece', self::sql_suppr_validateur, [$this->id, $id_utilisateur, $id_espece]);
	}

	public function retirer_branche($espece) {
		$id_espece = is_object($espece)?$espece->id_espece:(int)$espece;
		bobs_log("retrait branche $id_espece sur le réseau {$this->id}");
		return bobs_qm()->query($this->db, 'r_suppr_branche', self::sql_suppr_branche, [$this->id, $id_espece]);
	}

	public function membres() {
		$q = bobs_qm()->query($this->db, 'r_l_membres', self::sql_l_membres, [$this->id]);
		return new clicnat_iterateur_utilisateurs($this->db, array_column(self::fetch_all($q), 'id_utilisateur'));
	}

	public function ajouter_membre($utilisateur) {
		$id_utilisateur = is_object($utilisateur)?$utilisateur->id_utilisateur:(int)$utilisateur;
		bobs_log("ajout membre $id_utilisateur au réseau {$this->id}");
		return bobs_qm()->query($this->db, 'r_ajt_membre', self::sql_ajout_membre, [$this->id, $id_utilisateur]);
	}

	public function retirer_membre($utilisateur) {
		$id_utilisateur = is_object($utilisateur)?$utilisateur->id_utilisateur:(int)$utilisateur;
		bobs_log("retrait membre $id_utilisateur du réseau {$this->id}");
		return bobs_qm()->query($this->db, 'r_suppr_membre', self::sql_suppr_membre, [$this->id, $id_utilisateur]);
	}

	public function citations_a_valider($utilisateur,$n=50,$inverse_ordre = false) {
		$extraction = new clicnat_extractions($this->db);
		$extraction->ajouter_condition(new bobs_ext_c_reseau($this));
		$branches_utilisateur = $this->liste_branches_validateur($utilisateur);
		if ($branches_utilisateur->count() < 1) {
			throw new Exception("{$utilisateur} pas validateur sur le réseau");
		}
		foreach ($branches_utilisateur as $branche) {
			$extraction->ajouter_condition(new bobs_ext_c_taxon_branche($branche->id_espece));
		}
		$extraction->ajouter_condition(new bobs_ext_c_tag_attente());
		$extraction->ajouter_condition(new bobs_ext_c_pas_valide_utilisateur($utilisateur->id_utilisateur));
		$extraction->ajouter_condition(new bobs_ext_c_pas_observateur($utilisateur->id_utilisateur));
		$extraction->limite($n);
		if (INSTALL == 'picnat' && $this->id == 'av') {
			$extraction->ajouter_condition(new bobs_ext_c_liste_especes(LISTE_ESPECE_VALIDATION_OISEAU));
		}
		return $extraction->get_citations($inverse_ordre);
	}

	public function stats_validation($utilisateur = null){
		$stats = [];
		try {
			$extraction = new clicnat_extractions($this->db);
			$extraction->ajouter_condition(new bobs_ext_c_reseau($this));
			$stats['n_citations'] = $extraction->compte();
			$extraction->ajouter_condition(new bobs_ext_c_tag_invalide());
			$stats['n_citation_invalide'] = $extraction->compte();
			$extraction->retirer_condition(1);
			$extraction->ajouter_condition(new bobs_ext_c_tag_attente());
			$stats['n_citations_attente'] = $extraction->compte();
			$stats['pourcent_valide'] = round(100 * ($stats['n_citations_attente']) / $stats['n_citations']);
			if ( $utilisateur != null ) {
				$branches_utilisateur = $this->liste_branches_validateur($utilisateur);
				if ($branches_utilisateur->count() < 1) {
					throw new Exception("{$utilisateur} pas validateur sur le réseau");
				}
				foreach ($branches_utilisateur as $branche) {
					$extraction->ajouter_condition(new bobs_ext_c_taxon_branche($branche->id_espece));
				}
				$extraction->ajouter_condition(new bobs_ext_c_pas_observateur($utilisateur->id_utilisateur));
				$stats['n_citations_utl'] = $extraction->compte();

				$extraction->ajouter_condition(new bobs_ext_c_pas_valide_utilisateur($utilisateur->id_utilisateur));
				$stats['n_citations_a_valider_utl'] = $extraction->compte();
				$stats['pourcent_attente_utl'] = round(100 * ($stats['n_citations_utl'] - $stats['n_citations_a_valider_utl']) / $stats['n_citations_utl']);
			}
		}
		catch (Exception $e){

		}
		return $stats;

	}

	const TAG_EN_ATTENTE_VALIDATION = 579;

	public function selection_a_valider($utilisateur,$selection,$n=50){
		if( isset($selection) && !is_null($selection)){
			$ids = implode(',',$selection->get_citations()->ids());
			$sql = 'select citations.id_citation
			from citations,especes
				where citations.id_citation in ('.$ids.')
				 and (citations.id_citation in (select id_citation from citations_tags where id_tag='.self::TAG_EN_ATTENTE_VALIDATION.'))
				and (coalesce(not $1 = ANY (citations.validation_avis_positif || citations.validation_avis_negatif || citations.validation_sans_avis), true))
				and ($2 not in (select observations_observateurs.id_utilisateur from observations_observateurs where observations_observateurs.id_observation = citations.id_observation))
			       and citations.id_espece = especes.id_espece
				';
			$branches_utilisateur = $this->liste_branches_validateur($utilisateur);
			if ($branches_utilisateur->count() < 1) {
				throw new Exception("{$utilisateur} pas validateur sur le réseau");
			}
			else{
				$sql .= " and (";
				foreach ($branches_utilisateur as $branche) {
					$esp = get_espece($this->db,$branche->id_espece);
					$sql .= "(especes.borne_a between {$esp->borne_a} and {$esp->borne_b})";
						$sql .= " or ";

				}
				$sql .= "1=0)  limit  50";
				$q = bobs_qm()->query($this->db, 'sel_a_val', $sql, [$utilisateur->id_utilisateur,$utilisateur->id_utilisateur]);
				$tt = bobs_element::fetch_all($q);
				$tcit = array_column($tt, 'id_citation');
				//var_dump($sql);
				return new clicnat_iterateur_citations($this->db,$tcit);
			}
		}
		return new clicnat_iterateur_citations($this->db,[]);
	}

	public static function planifier_mad_tete_reseau($db) {
		clicnat_tache::ajouter($db, strftime("%Y-%m-%d %H:%M:%S",mktime()), 0, "MAD tête de réseau", "clicnat_mad_tete_reseau", []);
	}
}
