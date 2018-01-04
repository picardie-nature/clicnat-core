<?php
namespace Picnat\Clicnat;

/**
 * @brief Un taxon
 *
 * mal nommée espece cette classe permet de décrire des taxons
 */
class bobs_espece extends bobs_abstract_espece {
	public $id_espece;
	public $espece;
	public $classe;
	public $type_fiche;
	public $systematique;
	public $ordre;
	public $commentaire;
	public $famille;
	public $nom_f;
	public $nom_s;
	public $nom_a;
	protected $nom_pic;
	protected $nom_bzh;
	protected $nom_corse;
	protected $nom_occi;
	protected $nom_alsace;
	public $taxref_inpn_especes;
	public $jour_debut_nidif;
	public $jour_fin_nidif;
	public $mois_debut_nidif;
	public $mois_fin_nidif;
	public $taxref_dreal;
	protected $determinant_znieff;
	public $habitat;
	public $menace;
	public $action_conservation;
	public $commentaire_statut_menace;
	protected $invasif;
	public $id_chr;
	public $niveaux_restitutions;
	protected $textes_valides;
	protected $n_citations;
	protected $expert;
	protected $exclure_restitution;
	protected $id_espece_parent;
	protected $borne_a;
	protected $borne_b;
	protected $absent_region;
	protected $remarquable;
	protected $categorie_arbo;

	const restitution_public = 4;
	const restitution_structure = 2;
	const restitution_reseau = 1;

	const table_tags = 'especes_tags';
	const table_commentaires = 'especes_commentaires';

	public function __construct($db, $id) {
		try {
			parent::__construct($db, 'especes', 'id_espece', $id);
		} catch (clicnat_exception_pas_trouve $e) {
			throw new clicnat_exception_espece_pas_trouve("pas d'espèce pour l'identifiant $id");
		}
	}

	public function __get($c) {
		switch ($c) {
			case 'id_espece':
				return $this->id_espece;
			case 'determinant_znieff':
				return $this->determinant_znieff == 't';
			case 'invasif':
				return $this->invasif == 't';
			case 'textes_valides':
				return $this->textes_valides == 't';
			case 'n_citations':
				return $this->n_citations;
			case 'expert':
				return $this->expert == 't';
			case 'exclure_restitution':
				return $this->exclure_restitution == 't';
			case 'id_espece_parent':
				return $this->id_espece_parent;
			case 'nom_pic':
				return $this->nom_pic;
			case 'nom_bzh':
				return $this->nom_bzh;
			case 'nom_corse':
				return $this->nom_corse;
			case 'nom_occi':
				return $this->nom_occi;
			case 'nom_alsace':
				return $this->nom_alsace;
			case 'absent_region':
				return $this->absent_region == 't';
			case 'remarquable':
				return $this->remarquable == 't';
			case 'categorie_arbo':
				return $this->categorie_arbo == 't';
			case 'borne_a':
				return $this->borne_a;
			case 'borne_b':
				return $this->borne_b;
		}
	}

	public function  __toString() {
		self::cls($this->nom_f);
		if (empty($this->nom_f)) {
			if (empty($this->nom_s)) {
				return 'espèce sans nom';
			}
			return self::cls($this->nom_s);
		}
		return ucfirst($this->nom_f);
	}

	public function set_id_espece_parent($id_espece) {
		self::cli($id_espece, self::except_si_vide);
		$this->update_field('id_espece_parent', $id_espece, true);
	}

	public function ages() {
		$liste = [
			'1A'  => ['val' => '1A',  'lib' => 'un an', 'prop' => true, 'classes' => 'OM'],
			'+1A' => ['val' => '+1A', 'lib' => 'plus de un an', 'prop' => true, 'classes' => 'OM'],
			'2A'  => ['val' => '2A',  'lib' => 'deux ans', 'prop' => true, 'classes' => 'OM'],
			'+2A' => ['val' => '+2A', 'lib' => 'plus de deux ans', 'prop' => true, 'classes' => 'OM'],
			'3A'  => ['val' => '3A',  'lib' => 'trois ans', 'prop' => true, 'classes' => 'OM'],
			'4A'  => ['val' => '4A',  'lib' => 'quatre ans', 'prop' => true, 'classes' => 'OM'],
			'5A'  => ['val' => '5A',  'lib' => 'cinq ans', 'prop' => true, 'classes' => 'OM'],
			'AD'  => ['val' => 'AD',  'lib' => 'adulte', 'prop' => true, 'classes' => 'BROMI'],
			'AD&' => ['val' => 'AD&', 'lib' => 'adulte et immature', 'prop' => true, 'classes' => 'I'],
			'ADP' => ['val' => 'ADP', 'lib' => 'adulte et pulli', 'prop' => true, 'classes' => 'O'],
			'EX'  => ['val' => 'EX',  'lib' => 'exuvie', 'prop' => true, 'classes' => 'I'],
			'IMM' => ['val' => 'IMM', 'lib' => 'immature', 'prop' => true, 'classes' => 'OI'],
			'EME' => ['val' => 'EM',  'lib' => 'émergence', 'prop' => true, 'classes' => 'I'],
			'JUV' => ['val' => 'JUV', 'lib' => 'juvénile',	'prop' => true, 'classes' => 'BORI'],
			'LA'  => ['val' => 'LA',  'lib' => 'larve', 'prop' => true, 'classes' => 'IBR'],
			'P'   => ['val' => 'P',   'lib' => 'ponte', 'prop' => true, 'classes' => 'BR'],
			'PUL' => ['val' => 'PUL', 'lib' => 'poussin', 'prop' => true, 'classes' => 'O'],
			'VOL' => ['val' => 'VOL', 'lib' => 'volant', 'prop' => true, 'classes' => 'OI'],
			'CHE' => ['val' => 'CHE', 'lib' => 'chenille', 'prop' => true, 'classes' => 'I'],
			'CRY' => ['val' => 'CRY', 'lib' => 'chrysalide', 'prop' => true, 'classes' => 'I']
		];

		$ret = [['val' => '?',   'lib' => 'inconnu', 'prop' => true, 'classes' => 'ABROMIPL']];

		foreach ($liste as $k => $genre) {
			if (strpos($genre['classes'],$this->classe) !== false) {
				$ret[] = $genre;
			}
		}

		return $ret;
	}

	public function genres() {
		return [
			' '  => ['val' => ' ',  'lib' => 'inconnu', 'prop' => false],
			'?'  => ['val' => '?',  'lib' => 'inconnu', 'prop' => true],
			'C'  => ['val' => 'C',  'lib' => 'couple',  'prop' => true], // mais c'est pas un âge ça ?
			'F'  => ['val' => 'F',  'lib' => 'femelle', 'prop' => true],
			'F?' => ['val' => 'F?', 'lib' => 'femelle', 'prop' => false],
			'FI' =>	['val' => 'FI', 'lib' => 'femelle ou immature', 'prop' => true],
			'I'  =>	['val' => 'I',  'lib' => 'immature','prop' => true],
			'M'  => ['val' => 'M',  'lib' => 'mâle',    'prop' => true],
			'M?' => ['val' => 'M?', 'lib' => 'mâle ?',  'prop' => false],
			'MF' => ['val' => 'MF', 'lib' => 'mâle et femelle', 'prop' => true],
			'N'  => ['val' => 'N',  'lib' => 'N ?',     'prop' => false]
		];
	}
	/**
	 * @brief Instance du taxon parent
	 * @return false s'il n'y en a pas
	 */
	public function taxon_parent() {
		if (empty($this->id_espece_parent))
			return false;

		return get_espece($this->db, $this->id_espece_parent);
	}

	const sql_categorie_arbo = 'select * from especes where categorie_arbo order by borne_a';

	public static function taxons_categorie_arbo($db) {
		$q = bobs_qm()->query($db, 'esp_taxons_cat_arbo', self::sql_categorie_arbo, []);
		$r = self::fetch_all($q);
		return new clicnat_iterateur_especes($db, array_column($r, 'id_espece'));
	}

	const sql_taxons_enfants = 'select * from especes where id_espece_parent=$1 order by nom_s,nom_f';

	public function taxons_enfants() {
		$q = bobs_qm()->query($this->db, 'esp_taxons_enft', self::sql_taxons_enfants, [$this->id_espece]);
		$r = self::fetch_all($q);
		return new clicnat_iterateur_especes($this->db, array_column($r, 'id_espece'));
	}

	public function taxons_voisins() {
		$q = bobs_qm()->query($this->db, 'esp_taxons_enft', self::sql_taxons_enfants, [$this->id_espece_parent]);
		$r = self::fetch_all($q);
		return new clicnat_iterateur_especes($this->db, array_column($r, 'id_espece'));
	}

	const sql_derniers_ajouts = 'select id_espece from especes order by id_espece desc limit $1';

	public static function derniers_ajouts($db, $limite=30) {
		$q = bobs_qm()->query($db, 'derniers_ajouts', self::sql_derniers_ajouts, [$limite]);
		$r = self::fetch_all($q);
		return new clicnat_iterateur_especes($db, array_column($r, 'id_espece'));
	}

	/**
	 * @brief Retourne un tableau de tous les taxons parents
	 * @return array
	 */
	public function taxons_parents() {
		$taxon = $this;
		$t = [];
		while ($t2 = $taxon->taxon_parent()) {
			$t[] = $t2;
			$taxon = $t2;
			continue;
		}
		return array_reverse($t);
	}

	public function set_borne($borne, $valeur) {
		$this->update_field("borne_$borne", $valeur, true);
	}

	public static function bornage($db) {
		$espece = get_espece($db, RACINE_ARBRE_TAXO); // Animalia
		function r($espece, $b) {
			$espece->set_borne("a", $b);
			$b++;
			foreach ($espece->taxons_enfants() as $enfant) {
				$b = r($enfant, $b);
			}
			$espece->set_borne("b", $b);
			$b++;
			return $b;
		}
		r($espece,1);
	}
	const sql_taxons_descendants = 'select id_espece from especes where borne_a>$1 and borne_b<$2 order by borne_a';

	public function taxons_descendants() {
		$q = bobs_qm()->query($this->db, 'taxons_descendants', self::sql_taxons_descendants, [$this->borne_a, $this->borne_b]);
		$r = self::fetch_all($q);
		return new clicnat_iterateur_especes($this->db, array_column($r, 'id_espece'));
	}

	const sql_sel_nom_sc_inpn = "select id_espece,nom_sc from v_especes_synonymes_sc_inpn where nom_sc ilike $1";

	/**
	 * @brief rechercher dans les synonymes de taxref
	 * @param $critere texte
	 * @return array (id_espece, nom_sc) nom_sc = synonyme (et pas le nom sc de l'objet correspondant à l'id_espece)
	 */
	public static function synonymes_nom_sc_inpn($db, $critere) {
		$critere = str_replace("%"," ",$critere);
		$q = bobs_qm()->query($db, 's_nom_s_inpn', self::sql_sel_nom_sc_inpn, array("$critere%"));
		return self::fetch_all($q);
	}

	public function xeno_canto() {
		return new clicnat_oiseau_xcanto($this->db, $this->id_espece);
	}

	/**
	 * @brief liste les étiquettes associée à l'espèce
	 */
	public function get_tags() {
		return $this->__get_tags(self::table_tags, $this->id_espece, 'and id_espece=$1');
	}

	/**
	 * @brief ajoute une étiquette associée à l'espèce
	 */
	public function ajoute_tag($id_tag, $intval=null, $textval=null) {
		return $this->__ajoute_tag(self::table_tags, 'id_espece', $id_tag, $this->id_espece, $intval, $textval);
	}

	/**
	 * @brief retirer l'étiquette de l'espèce
	 */
	public function supprime_tag($id_tag) {
		return $this->__supprime_tag(self::table_tags, 'id_espece', $id_tag, $this->id_espece);
	}

	/**
	 * @brief liste les commentaires à l'espèce
	 */
	public function get_commentaires() {
		return $this->__get_commentaires(self::table_commentaires, 'id_espece', $this->id_espece);
	}

	/**
	 * @brief ajouter un commentaire à l'espèce
	 */
	public function ajoute_commentaire($type_c, $id_utilisateur, $commtr) {
		return $this->__ajoute_commentaire(self::table_commentaires, 'id_espece', $this->id_espece, $type_c, $commtr, $id_utilisateur);
	}

	/**
	 * @brief supprimer le commentaire
	 */
	public function supprime_commentaire($id_commentaire) {
		return $this->__supprime_commentaire(self::table_commentaires, $id_commentaire);
	}

	/**
	 * @brief liste les ages possibles pour cette espèce
	 */
	public function get_age_list() {
		$ages = bobs_citation::get_age_list();
		$t = array();
		foreach ($ages as $a) {
			if (strpos($a['classes'], $this->classe) !== FALSE)
				$t[] = $a;
		}
		return $t;
	}

	/**
	 * @brief test si l'espèce est accessible pour un niveau de restitution
	 * @return boolean
	 */
	public function get_restitution_ok($niveaux) {
		self::cli($niveaux);
		return ($this->niveaux_restitutions&intval($niveaux)) == $niveaux;
	}

	const sql_set_niv_r = 'update especes set niveaux_restitutions=$2 where id_espece=$1';

	/**
	 * @brief change le niveau de restitution de l'espèce
	 */
	private function set_niveaux_restitutions($niveaux) {
		self::cli($niveaux);

		if ($niveaux < 0)
			throw new Exception('ne peut être plus petit que zéro');

		if ($niveaux > (self::restitution_public|self::restitution_reseau|self::restitution_structure))
			throw new Exception('$niveaux trop grand '.$niveaux);

		$q = bobs_qm()->query($this->db, 'esp_set_niv_r', self::sql_set_niv_r, array($this->id_espece, $niveaux));

		if (!$q)
			throw new Exception('pas de mise a jour');

		$this->niveaux_restitutions = $niveaux;

		return true;
	}

	/**
	 * @brief ajoute ce niveau de restitution sans modifier les autres
	 */
	public function add_niveau_restitution($niveau) {
		self::cli($niveau);
		return $this->set_niveaux_restitutions($this->niveaux_restitutions|$niveau);
	}

	/**
	 * @brief enlève ce niveau de restitution sans modifier les autres
	 */
	public function del_niveau_restitution($niveau) {
		self::cli($niveau);
		return $this->set_niveaux_restitutions($this->niveaux_restitutions&~$niveau);
	}

	/**
	 * @brief liste les classes d'espèces disponnibles
	 * @see bobs_espece::get_classe_lib()
	 * @deprecated
	 * @return un tableau de lettres
	 */
	public static function get_classes() {
		return bobs_classe::get_classes();
	}

	const sql_esp_ordres = 'select distinct ordre from especes where ordre is not null and length(trim(ordre))>0';

	/**
	 * @brief liste les ordres d'espèces disponible
	 * @return un tableau
	 */
	public static function get_ordres($db) {
	    $q = bobs_qm()->query($db, 'espece_ordres', self::sql_esp_ordres, array());
	    return self::fetch_all($q);
	}

	/**
	 * @brief retourne l'objet correspondant au comité d'homologation régional ou faux
	 *
	 * @return bobs_chr
	 */
	public function get_chr() {
		if ($this->id_chr)
			return get_chr($this->db, $this->id_chr);

		return false;
	}

	const sql_unset_id_chr = 'update especes set id_chr=null where id_espece=$1';
	const sql_set_id_chr = 'update especes set id_chr=$2 where id_espece=$1';

	/**
	 * @brief Modifie le chr associé
	 */
	public function set_chr($id_chr) {
		if (empty($id_chr)) {
			$q = bobs_qm()->query($this->db, 'bobs_esp_unset_id_chr', self::sql_unset_id_chr, array($this->id_espece));
			if ($q)
				$this->id_chr = null;
		} else {
			$q = bobs_qm()->query($this->db, 'bobs_esp_set_id_chr', self::sql_set_id_chr, array($this->id_espece, $id_chr));
			if ($q)
				$this->id_chr = $id_chr;
		}
		return $q;
	}

	const en_francais = true;
	const en_latin = false;

	/**
	 * @brief Nom de la classe
	 * @param $langue si vrai retour en français sinon en latin
	 * @return le libellé de la classe
	 */
	public function get_classe_lib($langue=bobs_classe::en_francais) {
		return bobs_classe::get_classe_lib_par_lettre($this->classe, $langue);
	}

	/**
	 * @brief Nom de la classe
	 * @deprecated
	 * @return le libellé de la classe
	 */
	public static function get_classe_lib_par_lettre($lettre, $fra=true) {
		return bobs_classe::get_classe_lib_par_lettre($lettre, $fra);
	}

	const sql_get_id_ref_tiers = 'select * from referentiel_especes_tiers where tiers=$1 and id_espece=$2';

	public function get_id_referentiel_tiers($referentiel) {
		self::cls($referentiel);
		$q = bobs_qm()->query($this->db, 'g_id_ref_tiers', self::sql_get_id_ref_tiers, array($referentiel,$this->id_espece));
		$r = self::fetch($q);

		if (!isset($r['id_tiers']))
			return false;

		return $r['id_tiers'];
	}

	const sql_by_ref_tiers = 'select id_espece from referentiel_especes_tiers where tiers=$1 and id_tiers=$2';

	/**
	 * @brief trouve une espèce par son identifiant dans un référentiel tiers
	 * @param $db ressource base de données
	 * @param $tiers référence du tiers
	 * @param $id numéro de l'espèce (dans le ref. tiers)
	 * @return bobs_espece
	 */
	public static function by_id_ref_tiers($db, $tiers, $id) {
		self::cli($id);
		$q = bobs_qm()->query($db, 'g_by_ref_tiers', self::sql_by_ref_tiers, array($tiers, $id));
		$r = self::fetch($q);
		if (is_array($r))
			return get_espece($db, $r['id_espece']);
		return false;
	}

	const sql_sel_ref_tiers = '
		select
			*,
			case tiers
				when \'gbif\' then \'http://www.gbif.org/species/\'||id_tiers
				when \'taxref\' then \'https://inpn.mnhn.fr/espece/cd_nom/\'||id_tiers
				else null
			end as url
		from referentiel_especes_tiers
		where id_espece=$1';
	const sql_add_ref_tiers = 'insert into referentiel_especes_tiers (tiers,id_tiers,id_espece) values ($1,$2,$3)';
	const sql_del_ref_tiers = 'delete from referentiel_especes_tiers where id_tiers=$2 and tiers=$1 and id_espece=$3';

	public function liste_references_tiers() {
		$q = bobs_qm()->query($this->db, "liste_ids_ref_esp_tiers", self::sql_sel_ref_tiers, array($this->id_espece));
		return self::fetch_all($q);
	}

	public function ajoute_reference_tiers($tiers, $id_tiers) {
		self::cls($tiers, self::except_si_vide);
		self::cli($id_tiers);
		return bobs_qm()->query($this->db, "insert_id_ref_tiers", self::sql_add_ref_tiers, array($tiers,$id_tiers,$this->id_espece));
	}

	public function supprime_reference_tiers($tiers, $id_tiers) {
		return bobs_qm()->query($this->db, "delete_id_ref_tiers", self::sql_del_ref_tiers, array($tiers,$id_tiers,$this->id_espece));
	}

	/**
	 * @brief instance de l'espèce dans le référentiel PN.
	 * @deprecated va disparaitre au profit de get_referentiel_regional
	 * @see get_referentiel_regional
	 * @return bobs_referentiel un instance de bobs_referentiel
	 */
	public function get_referentiel_pn() {
		global $contexte;
		if ($contexte == 'promontoire')
		    throw new Exception('not public');
		$table = $this->classe=='O'?'import_ref_pn_oiseaux':'import_ref_pn';
		$sql = sprintf("select * from %s where id_espece=%d", $table, $this->id_espece);
		$q = self::query($this->db, $sql);
		$r = self::fetch($q);
		$this->ref_pn = new bobs_referentiel($this->db, $r, $table);
		return $this->ref_pn;
	}

	const sql_sel_ref_reg = 'select * from referentiel_regional where id_espece=$1';

	/**
	 * @brief retourne le contenu du referentiel regional
	 * @deprecated anciennes table du premier référentiel voir statut_regional()
	 * @return array un tableau associatif
	 */
	public function get_referentiel_regional() {
		self::cli($this->id_espece);

		$q = bobs_qm()->query($this->db, 'esp_ref_regional', self::sql_sel_ref_reg, [$this->id_espece]);
		$this->referentiel_regional = self::fetch($q);
		return $this->referentiel_regional;
	}

	const sql_statut_regional = '
		select annee_publi,rarete,menace
		from listes_rarete_menace,listes_rarete_menace_data
		where listes_rarete_menace.id_liste_rarete_menace=listes_rarete_menace_data.id_liste_rarete_menace
		and id_espece=$1
		order by annee_publi desc';

	/**
	 * @brief retourne les différents status de menace et rareté d'une espèce
	 * @return array annee_publi,rarete,menace
	 */
	public function statut_regional() {
		$q = bobs_qm()->query($this->db, 'statut_regional_esp', self::sql_statut_regional, [$this->id_espece]);
		return self::fetch_all($q);
	}

	/**
	 * @deprecated
	 */
	public function update_referentiel_regional($args) {
		$keys = array('statut_origine', 'statut_bio', 'indice_rar', 'niveau_con',
				'categorie', 'fiabilite', 'etat_conv', 'prio_conv_cat',	'prio_conv_fia');

		if ($this->referentiel_regionale_existe()) {
		    foreach ($keys as $k) {
			$args[$k] = trim($args[$k]);

		    }
		    $sql = sprintf("update referentiel_regional set
				    statut_origine=%s,
				    statut_bio=%s,
				    indice_rar=%s,
				    niveau_con=%s,
				    categorie=%s,
				    fiabilite=%s,
				    etat_conv=%s,
				    prio_conv_cat=%s,
				    prio_conv_fia=%s
			    where id_espece = %d",
				    !empty($args['statut_origine'])?sprintf("'%s'", pg_escape_string($args['statut_origine'])):'null',
				    !empty($args['statut_bio'])?sprintf("'%s'", pg_escape_string($args['statut_bio'])):'null',
				    !empty($args['indice_rar'])?sprintf("'%s'", pg_escape_string($args['indice_rar'])):'null',
				    !empty($args['niveau_con'])?sprintf("'%s'", pg_escape_string($args['niveau_con'])):'null',
				    !empty($args['categorie'])?sprintf("'%s'", pg_escape_string($args['categorie'])):'null',
				    !empty($args['fiabilite'])?sprintf("'%s'", pg_escape_string($args['fiabilite'])):'null',
				    !empty($args['etat_conv'])?sprintf("'%s'", pg_escape_string($args['etat_conv'])):'null',
				    !empty($args['prio_conv_cat'])?sprintf("'%s'", pg_escape_string($args['prio_conv_cat'])):'null',
				    !empty($args['prio_conv_fia'])?sprintf("'%s'", pg_escape_string($args['prio_conv_fia'])):'null',
				    $this->id_espece);
		    $q = self::query($this->db, $sql);
		} else {
		    $insert_args = array();

		    foreach ($keys as $k)
			$insert_args[$k] = trim($args[$k]);

		    $insert_args['id_espece'] = $this->id_espece;
		    parent::insert($this->db, 'referentiel_regional', $insert_args);
		}
	}

	/**
	 * @brief Liste les espèces en fonction de la classe
	 * @return un tableau de bobs_espece
	 */
	public static function get_liste_par_classe($db, $lettre, $seulement_presente=false) {
		$seulement_presente = false;
		self::cls($lettre);
		if (empty($lettre))
			throw new Exception('$lettre is empty');
		$seulement_presente_a = '';
		$nom = 'species_by_class';
		if ($seulement_presente) {
			$nom .= '_only_present';
			$seulement_presente_a = 'and especes.id_espece in (select distinct id_espece from citations)';
			$sql = 'select distinct e.* from especes e,citations c
				where classe=$1 and c.id_espece=e.id_espece';
		} else {
			$sql = 'select * from especes where classe=$1 order by nom_f,nom_s';// ,$seulement_presente_a);
		}

		$qm = bobs_qm();
		$q = $qm->query($db, $nom, $sql, array($lettre));
		$t = array();
		while ($r = self::fetch($q))
			$t[] = get_espece($db, $r);
		return $t;
	}

	/**
	 * @brief Associer un taxon MNMH avec l'espèce
	 * @todo maj ref tiers
	 */
	public function associer_taxref($id_taxref) {
		self::cli($id_taxref, self::except_si_vide);
		$this->ajoute_reference_tiers('taxref', $id_taxref);
		$this->taxref_inpn_especes = $id_taxref;
	}

	/**
	 * @brief Enlever l'association avec le taxon MNMH
	 */
	public function enlever_taxref() {
		$t = $this->liste_reference_tiers("taxref");
		if (count($t) <= 0) {
			return false;
		}
		$this->supprime_reference_tiers("taxref", $t[0]['id_tiers']);
	}

	/**
	 * @brief Instance bobs_espece_inpn associée
	 */
	public function get_inpn_ref() {
		$id_taxref = $this->get_id_referentiel_tiers('taxref');
		if ($id_taxref === false)
			return false;
		return new bobs_espece_inpn($this->db, $id_taxref);
	}

	/**
	 * @brief statistiques par commune
	 * @deprecated
	 *
	 * retourne les observations groupées par commune, avec
	 * le mois,l'année et l'effectif observé
	 */
	public function get_communes_data() {
		if (is_array($this->communes_data))
			return $this->communes_data;
		$this->communes_data = array();
		$sql = sprintf("
			select commune_espace_point(os.id_espace), escom.nom,
				count(ci.id_citation) as n_citations, sum(ci.nb) as snb,
				to_char(date_observation,'yyyy') as annee,
				to_char(date_observation,'mm') as mois
			from citations ci,
				observations os,
				espace_commune escom
			where ci.id_espece=%d
				and os.id_observation=ci.id_observation
				and commune_espace_point(os.id_espace) is not null
				and commune_espace_point(os.id_espace)=escom.id_espace
			group by commune_espace_point(os.id_espace),
				escom.nom,
				os.date_observation
			order by escom.nom,
				to_char(date_observation,'yyyymm')
			",$this->id_espece);
		$q = self::query($this->db, $sql);
		while ($r = self::fetch($q))
			$this->communes_data[] = $r;
		return $this->communes_data;
	}

	const sql_cit_par_mois_sans_annee = 'select mois,100*n/(select sum(n) from especes_stats_mois where id_espece=$1) as pcent,n from especes_stats_mois where id_espece=$2 order by mois';

	/**
	 * @brief statistiques par mois (sans années)
	 * @return tableau associatif (mois, pcent, n)
	 */
	public function citations_par_mois_sans_annee() {
		return bobs_qm()->query($this->db, 'esp_cit_mois_sans_annee', self:: sql_cit_par_mois_sans_annee, array($this->id_espece,$this->id_espece));
	}

	/**
	 * @brief statistiques par mois (avec années)
	 *
	 * @return array le nombre de citations par mois et par année
	 */
	public function citations_par_mois() {
		$citations = [];
		$sql = "
			select 	count(ci.id_citation) as n_citations, sum(ci.nb) as snb,
				to_char(date_observation,'yyyy') as annee,
				to_char(date_observation,'mm') as mois
			from citations ci, observations os
			where ci.id_espece=$1
				and os.id_observation=ci.id_observation
				and ci.nb != -1
			group by os.date_observation
			order by to_char(date_observation,'yyyymm')";
		$q = bobs_qm()->query($this->db, 'citations_par_mois_esp', $sql, [$this->id_espece]);
		$data = self::fetch_all($q);
		$vmin = $vmax = -1;
		foreach($data as $l) {
			if (!array_key_exists($l['annee'], $citations))
				$citations[$l['annee']] = [
							'01'=>['n'=>0], '02'=>['n'=>0],
							'03'=>['n'=>0], '04'=>['n'=>0],
							'05'=>['n'=>0], '06'=>['n'=>0],
							'07'=>['n'=>0], '08'=>['n'=>0],
							'09'=>['n'=>0], '10'=>['n'=>0],
							'11'=>['n'=>0], '12'=>['n'=>0]
				];
			$citations[$l['annee']][$l['mois']]['n'] +=  $l['n_citations'];
			if ($vmin == -1)
				$vmin = $vmax = $l['n_citations'];
			else {
				$vmin = min($vmin, $citations[$l['annee']][$l['mois']]['n']);
				$vmax = max($vmax, $citations[$l['annee']][$l['mois']]['n']);
			}
		}
		ksort($citations);
		$vmin = 0;
		foreach ($citations as $annee => $t_annee) {
			for ($i=1;$i<=12;$i++) {
				$x = $citations[$annee][sprintf("%02d",$i)]['n'];
				$c = intval(($x-$vmin)/$vmax*128)+127;
				$citations[$annee][sprintf("%02d",$i)]['c'] = $c;
			}
		}
		return $citations;
	}

	/**
	 * @brief statistiques de présence sur les communes
	 * @deprecated
	 * Retourne la liste des communes où l'espèce a été vue, avec
	 * le mois, l'année de la dernière observation et le nombre de
	 * citations
	 *
	 * @return array
	 */

	public function liste_communes_presentes() {
		$communes = array();
		$this->get_communes_data();
		foreach($this->communes_data as $l) {
			if (!array_key_exists($l['nom'], $communes)) {
				$communes[$l['nom']] = array(
					'id' => $l['commune_espace_point'],
					'nom' => $l['nom'],
					'mois' => 0,
					'annee' => 0,
					'snb' => 0,
					'n_citations' => 0
				);
			}
			if (($l['annee'] > $communes[$l['nom']]['annee']) or
				($l['annee'] == $communes[$l['nom']]['annee'] and $l['mois'] > $communes[$l['nom']]['mois'])) {
				$communes[$l['nom']]['annee'] = $l['annee'];
				$communes[$l['nom']]['mois'] = $l['mois'];
			}
			$communes[$l['nom']]['n_citations'] += $l['n_citations'];
			$communes[$l['nom']]['snb'] += $l['snb'];
		}
		return $communes;
	}

	const sql_id_citation_max = 'select max(id_citation) from citations where id_espece=$1';

	private function id_citation_max() {
		$q = bobs_qm()->query($this->db, 'id_cit_max_esp', self::sql_id_citation_max, array($this->id_espece));
		$r = self::fetch($q);
		return $r['max'];
	}

	/**
	 * @brief Liste les communes où l'espèce est présente ou a été vue
	 * @return un tableau associatif
	 *
	 * Les colonnes :
	 *  - commune_id_espace
	 *  - nom nom en majuscule uniquement
	 *  - nom2 nom minuscule/majuscule et accentué
	 *  - ymin premier année d'obs
	 *  - ymax dernière année d'obs
	 */
	public function liste_communes_presentes_2() {
		$sql = "
		select id_espace,nom,nom2,dept,max(y) as ymax,min(y) as ymin from (
			select
                                espace_commune.id_espace,
                                espace_commune.nom, espace_commune.nom2,dept,
                                extract('year' from date_observation) as y,
                                (coalesce(superficie,0)<=superficie_max or superficie_max=0) as utilisable
                        from
                                espace_intersect,citations,especes,espace_commune,observations left join espace_polygon ep on ep.id_espace=observations.id_espace
                        where
                                observations.id_espace=espace_intersect.id_espace_obs and
                                especes.id_espece=citations.id_espece and
                                espace_intersect.table_espace_ref='espace_commune' and
                                observations.brouillard = false and
                                citations.id_observation=observations.id_observation and
                                citations.id_espece=$1 and coalesce(citations.nb,0)>=0 and
                                coalesce(indice_qualite,3)>=3 and
                                id_citation not in (select id_citation from citations_tags where id_tag in (591,592)) and
                                espace_commune.id_espace=espace_intersect.id_espace_ref
                        group by espace_commune.id_espace, espace_commune.nom, espace_commune.nom2,dept,superficie,superficie_max,date_observation
                        order by dept,espace_commune.nom
		) as subq where utilisable=true group by id_espace,nom,nom2,dept order by nom";
		$q = bobs_qm()->query($this->db, 'esp_com_pre2', $sql, array($this->id_espece));
		return self::fetch_all($q);
	}

	public function entrepot_liste_communes_presence($departement) {
		$state = entrepot::db()->especes_presence_communes_status->findOne(array("id_espece"=>$this->id_espece));

		if (is_null($state)) return array();

		$criteres = ["id_espece" => "{$this->id_espece}", "dept"=>sprintf("%d",$departement), "version" => (int)$state['current_version']];

		$curseur = entrepot::db()->especes_presence_communes_data->find($criteres);
		$curseur->sort(array("nom"=>1));
		return $curseur;
	}

	public function enregistre_liste_communes_presence($force=false) {
		$id_citation_max = $this->id_citation_max();
		$state = entrepot::db()->especes_presence_communes_status->findOne(array("id_espece"=>$this->id_espece));
		if (is_null($state)) {
			$state = array(
				"id_espece" => $this->id_espece,
				"last_run" => new MongoDate(),
				"current_version" => 0,
				"start" => new MongoDate(),
				"end" => null,
				"last_id_citation" => 0
			);
			entrepot::db()->especes_presence_communes_status->insert($state);
		} else {
			if ($id_citation_max > $state['last_id_citation'] || $force) {
				$state['start'] = new MongoDate();
				$state['end'] = null;
				entrepot::db()->especes_presence_communes_status->save($state);
			} else {
				// Pas necessaire de mettre a jour
				$state['last_run'] = new MongoDate();
				entrepot::db()->especes_presence_communes_status->save($state);
				return true;
			}
		}
		$version = $state['current_version'] + 1;
		foreach ($this->liste_communes_presentes_2() as $commune) {
			$commune['version'] = $version;
			$commune['id_espece'] = $this->id_espece;
			entrepot::db()->especes_presence_communes_data->insert($commune);
		}
		$state['current_version'] = $version;
		$state['end'] = new MongoDate();
		$state['last_run'] = new MongoDate();
		$state['last_id_citation'] = $id_citation_max;
		entrepot::db()->especes_presence_communes_status->save($state);
		entrepot::db()->especes_presence_communes_data->remove(array("id_espece"=>$this->id_espece, "version" => array('$lt' => $version)));
	}

	/**
	 * @brief Mise à jour des stats des communes
	 *
	 * a lancer après avoir executé  enregistre_liste_communes_presence() sur toutes les communes
	 */
	public static function entrepot_calcul_stats_communes() {
		// mise à jour nb esp par commune
		foreach (entrepot::db()->especes_presence_communes_data->distinct("id_espace") as $id_espace) {
			$curseur = entrepot::db()->especes_presence_communes_data->find(array("id_espace" => $id_espace));
			$d = array("id_espace" => $id_espace, "n" => $curseur->count());
			foreach ($curseur as $esp) {
				try {
					$espece = get_espece(get_db(), $esp['id_espece']);
				} catch (clicnat_exception_espece_pas_trouve $e) {
					// a priori l'espece n'est plus dans le référentiel
					bobs_log("entrepot: id_espece inexistant {$esp['id_espece']} suppression des stats associées\n");
					entrepot::db()->communes_stats_data->remove(array("id_espece" => "{$esp['id_espece']}"));
					$espece = false;
				}
				if ($espece) {
					$k = "n_{$espece->classe}";
					isset($d[$k])?$d[$k]++:$d[$k]=1;
				}
			}
			entrepot::db()->communes_stats_data->findAndModify(
				array("id_espace" => $id_espace),
				array('$set' => $d),
				null,
				array('upsert' => true)
			);
		}
	}

	/**
	 * @deprecated
	 */
	public static function get_liste_rouge($db) {
	    $sql = "select especes.* from especes,referentiel_regional
		    where especes.id_espece=referentiel_regional.id_espece
		    and categorie in ('VU','EN','CR') order by classe,nom_f,nom_s";
	    return self::fetch_all(bobs_qm()->query($db, 'esp_l_r', $sql, array()));
	}

	const sql_liste_rouge = "
		select especes.id_espece from referentiel_regional,especes
		where especes.id_espece=referentiel_regional.id_espece
		and categorie in ('VU','EN','CR')
		order by classe,nom_f,nom_s";

	/**
	 * @brief liste des espèces de la liste rouge
	 * @return clicnat_iterateur_especes
	 */
	public static function liste_rouge($db) {
		$q = bobs_qm()->query($db, 'sql_liste_rouge', self::sql_liste_rouge, array());
		$r = self::fetch_all($q);
		return new clicnat_iterateur_especes($db, array_column($r, 'id_espece'));
	}


	/**
	 * @brief Index le nom scientifique de l'espèce
	 */
	public function indexer_nom_scientifique_espece() {
		bobs_qm()->query($this->db, 'esp_index_noms_del', 'delete from especes_index where id_espece=$1', array($this->id_espece));
		try {
			$mots = self::index_nom($this->nom_s);
		} catch (Exception $e) {
			return false;
		}

		$n = 0;
		foreach ($mots as $mot) {
			bobs_qm()->query($this->db, 'esp_index_noms_ins', 'insert into especes_index (id_espece,ordre,mot) values ($1,$2,$3)', array($this->id_espece,$n,$mot));
			$n++;
		}
		return true;
	}

	/**
	 * @brief recherche une espèce dans l'index
	 *
	 */
	public static function index_recherche($db, $nom) {
		return parent::index_recherche($db, $nom, 'especes_index', 'id_espece', 'bobs_espece');
	}

	/**
	 * @todo mettre un itérateur
	 * @deprecated
	 */
	public function get_observations($prechargeEspecesVues=false) {
		$sql = sprintf("select observations.*
					from observations ,citations
					where id_espece = %d
					and observations.id_observation=citations.id_observation
					order by date_observation desc",
				$this->id_espece);
		$t = Array();
		$q = self::query($this->db, $sql);

		$observateurs = array();
		$espaces = array();

		while ($r = self::fetch($q))
			$t[] = new bobs_observation($this->db, $r, $espaces, $observateurs);

		if ($prechargeEspecesVues)
			foreach ($t as $obs)
				$obs->get_especes_vues();
		return $t;
	}

	/**
	 * @brief indice de rareté dans le référentiel régional
	 * @deprecated
	 */
	public function get_indice_rar() {
		if (!empty($this->indice_rar))
			return $this->indice_rar;

		$this->get_referentiel_pn();

		if (!$this->ref_pn)
			return false;

		$this->indice_rar = $this->ref_pn->indice_rar;
		$this->indice_rar = str_replace(' ', '', $this->indice_rar);

		return $this->indice_rar;
	}

	/**
	 * @brief degré de menace dans le référentiel régional
	 * @deprecated
	 */
	public function get_degre_menace() {
		if (!empty($this->degre_menace))
			return $this->degre_menace;

		$this->get_referentiel_pn();
		if (!$this->ref_pn)
			return false;

		$this->degre_menace = $this->ref_pn->categorie;
		$this->degre_menace = str_replace(' ', '', $this->degre_menace);

		return $this->degre_menace;
	}

	public static function get_indice_rar_lib($code) {
		if (empty($code))
			throw new InvalidArgumentException('$code est vide');

		switch($code) {
			case 'D':  return 'disparu';
			case 'TR': return 'très rare';
			case 'R':  return 'rare';
			case 'AR': return 'assez rare';
			case 'PC': return 'peu commun';
			case 'AC': return 'assez commun';
			case 'C':  return 'commun';
			case 'TC': return 'très commun';
			case 'E':  return 'exceptionnel';
			case 'NA': return 'non applicable';
			case 'EX': return 'disparue';
			case 'NE': return 'non évaluée';
		}
		throw new InvalidArgumentException('Pas de correspondance pour indice "'.$code.'"');
	}

	public static function get_degre_menace_lib($code)
	{
		if (empty($code))
			throw new InvalidArgumentException('$code est vide');
		switch($code) {
			case 'RE': return 'éteint(e) au niveau régional';
			case 'CR': return 'en danger critique';
			case 'EN': return 'en danger';
			case 'VU': return 'vulnérable';
			case 'NT': return 'quasi menacé';
			case 'LC': return 'préoccupation mineure';
			case 'DD': return 'données insuffisantes';
			case 'NA': return 'non applicable';
			case 'NE': return 'non évaluée';
		}
		throw new InvalidArgumentException('Pas de correspondance pour indice "'.$code.'"');
	}

	public static function liste_degre_menace() {
		static $liste;
		if (!isset($liste))
			$liste = array('RE','CR','EN','VU','NT','LC','DD','NA','NE');
		return $liste;
	}

	public static function liste_indice_rar() {
		static $liste;
		if (!isset($liste))
			$liste = array('E','D','TR','R','AR','PC','AC','C','TC');
		return $liste;
	}

	public static function liste_statut_origine() {
		static $liste;
		if (!isset($liste))
			$liste = array(
				'archéonaturalisé',
				'naturalisé',
				'naturalisé dangereux',
				'naturalisé dangereux non soutenu',
				'naturalisé dangereux soutenu',
				'naturalisé sans danger non soutenu',
				'naturalisé sans danger soutenu',
				'naturalisé soutenu',
				'sauvage',
				'sauvage soutenu',
				'sauvage réintroduit'
			);
		return $liste;
	}

	public static function liste_statut_bio() {
		return array(
			'erratique',
			'reproducteur',
			'visiteur',
			'données insuffisante',
			'inconnu'
		);
	}

	public static function liste_niveau_conservation() {
		return array(
			'indéterminable',
			'moyennement satisfaisant',
			'peu satisfaisant',
			'satisfaisant'
		);
	}

	public static function liste_fiabilite()
	{
		return array(
			'bonne',
			'incertitude',
			'moyenne'
		);
	}

	public static function liste_etat_conservation()
	{
		return array(
			'défavorable',
			'favorable',
			'mauvais'
		);
	}

	public static function liste_priorite_conservation()
	{
		return array(
			'fortement prioritaire',
			'fortement prioritaire conservé',
			'non prioritaire',
			'prioritaire',
			'très fortement prioritaire',
			'moyennement prioritaire',
			'moyennement prioritaire conservé'
		);
	}

	public static function liste_fiabilite_prio_conservation()
	{
		return array('bonne','incertitude','moyenne');
	}

	/**
	 * @brief Test si l'espèce fait partie du référentiel régionale
	 * @return boolean vrai s'il en fait parti
	 */
	public function referentiel_regionale_existe()
	{
		$sql = sprintf("select count(*) as n from referentiel_regional
			where id_espece = %d", $this->id_espece);
		$q = self::query($this->db, $sql);
		$r = self::fetch($q);
		return $r['n'] == 1;
	}

	const sql_rech_par_nom_xp = 'select distinct * from bob_recherche_espece_nom_f($1) order by nom_f';
	const sql_rech_par_nom = 'select distinct r.* from bob_recherche_espece_nom_f($1) r,especes e where r.id_espece=e.id_espece and e.expert=false order by r.nom_f';

	public static function recherche_par_nom($db, $nom, $expert=true) {
		if ($expert)
			$q = bobs_qm()->query($db, 'rech_esp_nom_xp', self::sql_rech_par_nom_xp, array($nom));
		else
			$q = bobs_qm()->query($db, 'rech_esp_nom', self::sql_rech_par_nom, array($nom));
		return self::fetch_all($q);
	}

	public static function recherche_par_code($db, $code)
	{
	    self::cls($code);
	    $sql = 'select distinct * from especes where trim(lower(espece))=trim(lower($1))';
	    $q = bobs_qm()->query($db, 'search_species_by_code', $sql, array($code));
	    return self::fetch_all($q);
	}

	public static function get_especes_dans_referentiel($db, $tri='statut_origine')
	{
	    switch ($tri) {
		    case 'statut_origine':
		    case 'statut_bio':
		    case 'indice_rar':
		    case 'niveau_con':
			break;
		    default:
			throw new Exception('clé de tri inconnue');
	    }

		$sql = 'select especes.* from referentiel_regional,especes
			where especes.id_espece=referentiel_regional.id_espece
			order by referentiel_regional.'.$tri;
		$q = bobs_qm()->query($db, 'esp_ref_reg_t_'.$tri, $sql, array());
		$t = array();
		while ($r = self::fetch($q)) {
			$t[] = new bobs_espece($db, $r);
			end($t)->get_referentiel_regional();
		}
		return $t;
	}

	public static function insert($db, $args) {
		$cols = array('espece','classe','type_fiche','systematique',
			'ordre','commentaire','famille','nom_f','nom_s','nom_a',
			'taxref_inpn_especes');
		$ti = array();
		foreach ($cols as $k) {
			if (array_key_exists($k, $args))
				$v = $args[$k];
			else
				$v = null;
			$ti[$k] = self::cls($v);
		}
		$ti['id_espece'] = self::nextval($db, 'especes_id_espece_seq');
	  // 20171103 - Francois
	  // Pour eviter les erreurs d'insertion si nom_f ne rentre pas dans le VARCHAR(100);
	  $ti['nom_f'] = substr($ti['nom_f'],0,99);
		parent::insert($db, 'especes', $ti);
		$esp = get_espece($db, $ti['id_espece']);
		$esp->indexer_nom_scientifique_espece();
		return $esp->id_espece;
	}

	/**
	 * @todo refaire les requetes
	 */
	public function modifier_date_nidif($jd, $md, $jf, $mf) {
		if (!$this->prep_espece_up_nidif) {
			@pg_prepare($this->db, 'espece_up_nidif',"
				update especes
				set
					jour_debut_nidif = $1,
					jour_fin_nidif = $2,
					mois_debut_nidif = $3,
					mois_fin_nidif = $4
				where
					id_espece = $5");
			$this->prep_espece_up_nidif = true;
		}

		self::cli($jd);
		self::cli($jf);
		self::cli($md);
		self::cli($mf);

		if ($jd <= 0 or $jd > 31 or $md <= 0 or $md > 12)
			throw new InvalidArgumentException('date de debut $jd ou $md invalide');

		if ($jf <= 0 or $jf > 31 or $mf <= 0 or $mf > 12)
			throw new InvalidArgumentException('date de fin $jf ou $mf invalide');

		pg_execute($this->db, 'espece_up_nidif', array($jd, $jf, $md, $mf, $this->id_espece));
	}

	public function modifier($args) {
		if (empty($args))
			throw new Exception('$args est vide');

		$champs = array(
			'classe','espece','type_fiche','systematique',
			'ordre','commentaire','famille','nom_f','nom_s',
			'nom_a', 'determinant_znieff', 'habitat', 'menace',
			'invasif','action_conservation','commentaire_statut_menace',
			'superficie_max','exclure_restitution','expert',
			'nom_pic','absent_region','sinp_sensibilite_national',
			'sinp_sensibilite_local','remarquable','categorie_arbo');

		foreach ($champs as $champ) {
			if (isset($args[$champ])) {
				$r = $this->update_field($champ, $args[$champ]);
			}
		}

		$this->indexer_nom_scientifique_espece();
		return true;
	}

	/**
	 * @brief liste les étiquettes utilisées par cette espèce
	 */
	public function tags_utilisees() {
		$sql = 'select tags.id_tag,tags.lib,count(citations.id_citation)
			from tags,citations_tags,citations
			where citations.id_citation=citations_tags.id_citation
			and citations_tags.id_tag=tags.id_tag
			and citations.id_espece=$1
			group by tags.id_tag,tags.lib
			order by count(citations.id_citation) desc';
		$q = bobs_qm()->query($this->db, 'tags_from_citations', $sql, array($this->id_espece));
		return self::fetch_all($q);
	}

	/**
	 * @brief ajout l'objet tag a tags_utilisees
	 */
	public function tags_utilisees_obj() {
		$tab = $this->tags_utilisees();
		foreach ($tab as $k => $ttag) {
			$tab[$k]['obj'] = new bobs_tags($this->db, $ttag['id_tag']);
		}
		return $tab;
	}

	public function est_dans_date_nidif($yyyy_mm_dd) 	{
	    $tstamp = strtotime($yyyy_mm_dd);
	    $year = strftime('%Y', $tstamp);
	    $d1 = strtotime(sprintf('%s-%02d-%02d', $year, $this->mois_debut_nidif, $this->jour_debut_nidif));
	    $d2 = strtotime(sprintf('%s-%02d-%02d', $year, $this->mois_fin_nidif, $this->jour_fin_nidif));
	    return (($d1 <= $tstamp) and ($tstamp <= $d2));
	}

	public function est_dans_date_hivernage($yyyy_mm_dd) 	{
		$tstamp = strtotime($yyyy_mm_dd);
		$month = intval(strftime('%m', $tstamp));
		if ($this->ordre == 'Chiroptères') {
			// du 15 novembre au 15 avril
			$j = intval(strftime('%d', $tstamp));
			if ($month == 12) return true;
			if ($month == 11 && $j >= 15) return true;
			if ($month < 4) return true;
			if ($month == 4 && $j<= 15) return true;
			return false;
	  } else if ($this->classe == 'O') { // les Oiseaux
			if ($month == 12 or $month == 1) {
	 			return true;
			}
		} else {
			// du 1er décembre au 20 février
			if ($month == 12 || $month == 1) {
				return true;
			} elseif ($month == 2) {
				return intval(strftime('%d', $tstamp)) <= 20;
			}
		}
	}

	public function get_carres_hivernant($annee) 	{
		self::cli($annee);
		$date_deb = sprintf("%04d-12-01", $annee);
		$date_fin = sprintf("%04d-01-31", $annee+1);

		$sql = '
			select distinct espace_l93_10x10.nom, astext(espace_l93_10x10.the_geom) as wkt
			from observations,citations,espace_point,espace_l93_10x10
			where date_observation between $1 and $2
				and observations.brouillard = false
				and observations.id_observation=citations.id_observation
				and id_espece=$4
				and observations.id_espace=espace_point.id_espace
				and espace_table=$3
				and espace_point.l93_10x10_id_espace=espace_l93_10x10.id_espace
				and citations.id_citation not in (select id_citation from citations_tags where id_tag in (591,126))
				and ((citations.indice_qualite >= 3) or citations.indice_qualite is null)
				and coalesce(citations.nb,0)>=0
			order by nom';
	    $args = [
				$date_deb, $date_fin,
				'espace_point',
				$this->id_espece
			];
			$q = bobs_qm()->query($this->db, 'espece_hivernant', $sql, $args);
			return self::fetch_all($q);
	}

	public static function liste_oiseaux_hivernant_2009_a_2011($db) {
		$sql = "select distinct especes.*
			from citations,observations,especes
			where citations.id_observation=observations.id_observation
			and especes.id_espece=citations.id_espece
			and classe='O'
			and extract(year from date_observation) in (2009,2010,2011,2012,2013)
			and extract(month from date_observation) in (1,12)
			and date_observation >= '2009-12-01'
			and observations.brouillard = false
			and citations.id_citation not in (select id_citation from citations_tags where id_tag in (591,126))
			and ((citations.indice_qualite >= 3) or citations.indice_qualite is null)
			and coalesce(citations.nb,0)>=0
			order by nom_f";
		$q = bobs_qm()->query($db, "l_hiv_2009_11", $sql, array());
		return self::fetch_all($q);
	}

	/**
	 * @brief liste les ordres pour une classe
	 * @param $db ressource
	 * @param $classe la lettre le la classe
	 * @deprecated
	 * @return un tableau
	 *
	 * les colonnes du tableau retourné : ordre,md5
	 */
	public static function get_ordres_for_classe($db, $classe) {
		$classe = new bobs_classe($db, $classe);
		return $classe->get_ordres();
	}

	public static function get_especes_for_ordre_classe($db, $md5)
	{
	    $sql = 'select * from especes where md5(coalesce(ordre,\'NULL\')||classe)=$1 order by nom_f, nom_s';
	    $q = bobs_qm()->query($db, 'md5-classe-ordre', $sql, array($md5));
	    return self::fetch_all($q);
	}

	const nb_citations_toutes = 1;
	const nb_citations_valides = 2;
	const sql_n_citations_toutes = 'select count(*) as n from citations where id_espece=$1';
	const sql_n_citations_valides = 'select count(*) as n from citations where id_espece=$1 and id_citation not in (select c.id_citation from citations_tags ct,citations c where id_tag=$2 and ct.id_citation=c.id_citation and c.id_espece=$3)';

	/**
	 * @brief retourne le nombre de citations pour une espèce
	 * @param $restriction self::nb_citations_toutes ou self::nb_citations_valides
	 *
	 * Les citations valides sont les données avec un effectif > -1
	 * et pas associées à un code invalide
	 *
	 * @return integer nombre de citations pour cette espèce
	 */
	public function get_nb_citations($restriction=self::nb_citations_toutes)
	{
	    self::cli($this->id_espece, self::except_si_inf_1);

		switch ($restriction) {
			case self::nb_citations_toutes:
				$q = bobs_qm()->query($this->db, 'espece-nb-cit', self::sql_n_citations_toutes, array($this->id_espece));
				break;
			case self::nb_citations_valides:
				$tag = bobs_tags::by_ref($this->db, 'INV!');
				$q = bobs_qm()->query($this->db, 'espece-nb-cit-v', self::sql_n_citations_valides, array($this->id_espece, $tag->id_tag, $this->id_espece));
				break;
			default:
				throw new Exception('cas non prévu');
				break;
		}

		$r = self::fetch($q);
		return intval($r['n']);
	}

	/**
	 * @brief mise a jour du champ nb_citations
	 */
	public function set_nb_citations() {
		$n = $this->get_nb_citations(self::nb_citations_valides);
		return $this->update_field("n_citations", $n);
	}

	const sql_del1 = 'delete from especes where id_espece=$1';
	const sql_del2 = 'delete from especes_index where id_espece=$1';

	/**
	 * @brief suppression d'une espèce
	 */
	public function supprimer() {
		self::cli($this->id_espece);
		if ($this->get_nb_citations(self::nb_citations_toutes) == 0) {
			bobs_qm()->query($this->db, 'espece-del1', self::sql_del1, [$this->id_espece]);
			bobs_qm()->query($this->db, 'espece-del2', self::sql_del2, [$this->id_espece]);
			return true;
		}
		return false;
	}

	/**
	 * @brief remplace sur les citations l'id_espece de celui d'une autre
	 *
	 * Cette fonction est utilisée pour déplacer les citations d'une espèce
	 * vers une autre.
	 *
	 * @param int $id_espece nouveau id_espece
	 * @return boolean
	 */
	public function change_citations_id_espece($id_espece) {
		self::cli($this->id_espece);
		self::cli($id_espece);
		if ($this->id_espece == $id_espece) {
			throw new \Exception('identiques !');
		}
		self::query($this->db, 'begin');
		try {
			$sql = 'select * from citations where id_espece=$1 for update nowait';
			$q = bobs_qm()->query($this->db, 'change_id_sp_s', $sql, array($id_espece));
			while ($r = self::fetch($q)) {
				bobs_log("maj_id_espece from {$this->id_espece} to {$id_espece} on citation {$r['id_citation']}");
			}
			$sql = 'update citations set id_espece=$2 where id_espece=$1';
			$q = bobs_qm()->query($this->db, 'change_id_sp_u', $sql, array($this->id_espece, $id_espece));

			$sql = "update referentiel_especes_tiers set id_espece=$2 where id_espece=$1";
			$q = bobs_qm()->query($this->db, 'change_id_sp_u2', $sql, array($this->id_espece, $id_espece));
		} catch (\Exception $e) {
			self::query($this->db, 'rollback');
			throw $e;
		}
		self::query($this->db, 'commit');
		return true;
	}

	/**
	 * @brief Liste la première et la dernière entre lesquels un oiseau a été noté chanteur
	 *
	 * @return array
	 */
	public function bornes_chanteurs() {
		$sql = "select  min(date_observation) as premiere_date,
        				max(date_observation) as derniere_date,
        				extract('year' from date_observation) as annee
				from observations o,citations c,citations_tags ct,tags t
				where o.id_observation=c.id_observation
				and c.id_espece=$1
				and ct.id_citation=c.id_citation
				and t.id_tag=ct.id_tag
				and t.ref in ('2100')
				and extract('year' from date_observation) between extract('year' from now())-15 and extract('year' from now())-1
				group by extract('year' from date_observation)
				order by extract('year' from date_observation)";
		$q = bobs_qm()->query($this->db, 'esp_borne_chant', $sql, [$this->id_espece]);
		return self::fetch_all($q);
	}

	public function bornes_moyenne_chanteurs() {
		$sql = "select avg(premiere_date) as moy_premiere_date ,avg(derniere_date) as moy_derniere_date
				from
				(select trunc(extract('doy' from min(date_observation))) as premiere_date,
				        trunc(extract('doy' from max(date_observation))) as derniere_date,
				        extract('year' from date_observation) as annee
					from observations o,citations c,citations_tags ct,tags t
					where o.id_observation=c.id_observation
					and o.brouillard=false
					and c.nb>=0
					and c.id_espece=$1
					and ct.id_citation=c.id_citation
					and t.id_tag=ct.id_tag
					and t.ref in ('2100')
					and extract('year' from date_observation) between extract('year' from now())-15 and extract('year' from now())-1
					group by extract('year' from date_observation)
					order by extract('year' from date_observation)
				) as a";
		$q = bobs_qm()->query($this->db, 'esp_moy_chant', $sql, array($this->id_espece));
		return self::fetch($q);
	}

	public function citations_chanteurs_par_semaine() {
		$sql = "select trunc(extract('doy' from date_observation)/7)+1 as semaine,count(distinct c.id_citation) as n
					from observations o,citations c,citations_tags ct,tags t
					where o.id_observation=c.id_observation
					and c.nb>=0
					and o.brouillard=false
					and c.id_espece=$1
					and ct.id_citation=c.id_citation
					and t.id_tag=ct.id_tag
					and t.ref in ('2100')
					and extract('year' from date_observation) between extract('year' from now())-15 and extract('year' from now())-1
					group by trunc(extract('doy' from date_observation)/7) order by trunc(extract('doy' from date_observation)/7)";
		$q = bobs_qm()->query($this->db, 'esp_nc_chant', $sql, array($this->id_espece));
		return self::fetch_all($q);
	}

	public function citations_n_moyen_par_semaine() {
		$sql = "select avg(n) as moy from
					(select  trunc(extract('doy' from date_observation)/7) as dw,
        				count(distinct c.id_citation) as n
						from observations o,citations c,citations_tags ct,tags t
						where o.id_observation=c.id_observation
						and o.brouillard=false
						and c.nb>=0
						and c.id_espece=$1
						and ct.id_citation=c.id_citation
						and t.id_tag=ct.id_tag
						and t.ref in ('2100')
						and extract('year' from date_observation) between extract('year' from now())-15 and extract('year' from now())-1
						group by trunc(extract('doy' from date_observation)/7)
					) as a";
		$q = bobs_qm()->query($this->db, 'esp_moy_chant_s', $sql, array($this->id_espece));
		$r = self::fetch($q);
		return $r['moy'];
	}

	const sql_doc_add = 'insert into especes_documents (id_espece,document_ref) values ($1,$2)';
	const sql_doc_list = 'select * from especes_documents where id_espece=$1';
	const sql_doc_del = 'delete from especes_documents where id_espece=$1 and document_ref=$2';

	/**
	 * @brief associe un nouveau document à cette espèce
	 * @todo tester si le doc existe
	 */
	public function document_associer($doc_id) {
		return bobs_qm()->query($this->db, 'esp_assoc_doc', self::sql_doc_add, array($this->id_espece, $doc_id));
	}

	/**
	 * @brief liste les documents associé
	 * @return array un tableau d'instances bobs_document
	 */
	public function documents_liste() {
		$q = bobs_qm()->query($this->db, 'esp_assoc_doc_liste', self::sql_doc_list, array($this->id_espece));
		$tr = [];
		while ($r = self::fetch($q)) {
			$doc = bobs_document::getInstance($r['document_ref']);
			if ($doc)
				$tr[] = $doc;
		}
		return $tr;
	}

	/**
	 * @brief retire un document de l'espèce
	 */
	public function document_enlever($doc_id) {
		return bobs_qm()->query($this->db, 'esp_del_doc', self::sql_doc_del, array($this->id_espece, $doc_id));
	}

	const sql_l_esp_sensib = 'select * from especes where niveaux_restitutions&$1!=$2 and length(nom_s)>0 order by classe,ordre,nom_f';

	/**
	 * @brief liste les especes sensibles
	 * @deprecated
	 * @return un tableau
	 *
	 * liste les espèces qui ne sont pas en restitution public
	 */
	public static function liste_especes_sensibles($db) {
		$q = bobs_qm()->query($db, 'l_esp_sensib', self::sql_l_esp_sensib, array(self::restitution_public, self::restitution_public));
		return self::fetch_all($q);
	}

	const sql_especes = 'select id_espece from especes';

	/**
	 * @brief liste toutes les especes
	 * @return clicnat_iterateur_espece
	 */
	public function especes($db) {
		$q = bobs_qm()->query($db, 'liste_esp', self::sql_especes, array());
		$r = self::fetch_all($q);
		return new clicnat_iterateur_especes($db, array_column($r ,'id_espece'));
	}

	const sql_sensibles = 'select id_espece from especes where niveaux_restitutions&$1!=$2 and length(nom_s)>0 order by classe,ordre,nom_f';

	/**
	 * @brief liste des espèces sensibles
	 * @return clicnat_iterateur_especes
	 */
	public static function liste_sensibles($db) {
		$q = bobs_qm()->query($db, 'liste_sensibles', self::sql_sensibles, array(self::restitution_public, self::restitution_public));
		$r = self::fetch_all($q);
		return new clicnat_iterateur_especes($db, array_column($r , 'id_espece'));
	}

	const sql_l_esp_invasives = 'select * from especes where invasif=true and length(nom_s)>0 order by classe,ordre,nom_f';

	/**
	 * @brief liste les invasifs
	 * @deprecated
	 * @see liste_invasives()
	 * @return un tableau
	 */
	public static function liste_especes_invasives($db) {
		$q = bobs_qm()->query($db, 'l_esp_invasives', self::sql_l_esp_invasives, array());
		return self::fetch_all($q);
	}

	const sql_invasives = 'select id_espece from especes where invasif=true and length(nom_s)>0 order by classe,ordre,nom_f';

	/**
	 * @brief liste des espèces invasives
	 * @return clicnat_iterateur_especes
	 */
	public static function liste_invasives($db) {
		$q = bobs_qm()->query($db, 'liste_invasives', self::sql_invasives, array());
		$r = self::fetch_all($q);
		return new clicnat_iterateur_especes($db, array_column($r, 'id_espece'));
	}

	const sql_determinant_znieff = 'select id_espece from especes where determinant_znieff=true and length(nom_s)>0 order by classe,ordre,nom_f';

	/**
	 * @brief liste des espèces déterminante ZNIEFF
	 * @return clicnat_iterateur_especes
	 */
	public static function liste_determinantes_znieff($db) {
		$q = bobs_qm()->query($db, 'liste_det_znieff_', self::sql_determinant_znieff, array());
		$r = self::fetch_all($q);
		return new clicnat_iterateur_especes($db, array_column($r, 'id_espece'));
	}


	public function get_reseau() {
		return clicnat_reseau::get_reseau_espece($this->db, $this->id_espece);
	}

	const sql_distrib_obs_semaine = "
		select count(*) as n,extract('week' from date_observation) as semaine
		from citations,observations
		where id_espece=$1 and
		coalesce(precision_date,0)<=1 and
		observations.id_observation=citations.id_observation
		group by extract('week' from date_observation)
		order by extract('week' from date_observation)";

	public function distribution_observations_semaines() {
		$t = self::fetch_all(bobs_qm()->query($this->db, 'esp_distrib_annee', self::sql_distrib_obs_semaine, array($this->id_espece)));
		$total = 0;
		foreach ($t as $v) {
			$total += $v['n'];
		}
		foreach ($t as $k=>$v) {
			$t[$k]['p'] += 100*$v['n']/$total;
		}
		return $t;
	}

	const sql_index_repartition_sel = 'select x0,y0,annee,n from repartitions.repartition_especes where id_espece=$1 and srs=$2 and pas=$3';
	const sql_index_repartition_derniere_annee = 'select x0,y0,max(annee) as annee_max from repartitions.repartition_especes where id_espece=$1 and srs=$2 and pas=$3 group by x0,y0';
	const sql_index_repartition_kml = 'select ST_AsKML(clicnat_atlas_poly(srs,pas,x0,y0)) as kml,annee,x0,y0 from repartitions.repartition_especes where id_espece=$1 and srs=$2 and pas=$3 group by x0,y0,annee,clicnat_atlas_poly(srs,pas,x0,y0) order by annee';
	const sql_index_repartition_existe = 'select count(*) as n from repartitions.repartition_especes where id_espece=$1 and srs=$2 and pas=$3 and x0=$4 and y0=$5 and annee>$6';

	/**
	 * @brief Liste des carrès occupés par l'espèce
	 * @param $srid numéro de projection la grille
	 * @param $pas pas de la grille
	 * @return array (x0,y0,n,annee)
	 */
	public function get_index_atlas_repartition($srid, $pas) {
		$q = bobs_qm()->query($this->db, "idx_altas_rep_s", self::sql_index_repartition_sel, [$this->id_espece, $srid, $pas]);
		return self::fetch_all($q);
	}

	/**
	 * @brief Liste des carrès occupés par l'espèce avec la dernière année
	 * @param $srid numéro de projection la grille
	 * @param $pas pas de la grille
	 * @return array (x0,y0,annee_max)
	 */
	public function get_index_atlas_repartition_derniere_annee($srid, $pas) {
		$q = bobs_qm()->query($this->db, "idx_atlas_rep_ly", self::sql_index_repartition_derniere_annee, [$this->id_espece, $srid, $pas]);
		return self::fetch_all($q);
	}

	public function atlas_repartition_kml($srid, $pas) {
		$q = bobs_qm()->query($this->db, "idx_atlas_kml", self::sql_index_repartition_kml, [$this->id_espece,$srid,$pas]);
		$doc = new \DOMDocument();
		$doc->formatOutput = true;
		$kml = $doc->createElement('kml');
		$kml->setAttribute('xmlns', "http://www.opengis.net/kml/2.2");
		$document = $doc->createElement('Document');
		$folder = $doc->createElement('Folder');
		$name = $doc->createElement('name');
		$name->appendChild($doc->createCDATASection(html_entity_decode($this->__toString(),ENT_COMPAT, 'UTF-8')));
		$folder->appendChild($name);
		$schema = $doc->createElement('Schema');
		$schema->setAttribute('name', 'attributs');
		$schema->setAttribute('id', 'attributs_id');
		$simplefield = $doc->createElement('SimpleField');
		$simplefield->setAttribute("type", "int");
		$simplefield->setAttribute("name", "year");
		$simplefield->appendChild($doc->createElement('displayName', "Année"));
		$schema->appendChild($simplefield);

		$simplefield = $doc->createElement('SimpleField');
		$simplefield->setAttribute("type", "int");
		$simplefield->setAttribute("name", "x0");
		$simplefield->appendChild($doc->createElement('displayName', "X0"));
		$schema->appendChild($simplefield);

		$simplefield = $doc->createElement('SimpleField');
		$simplefield->setAttribute("type", "int");
		$simplefield->setAttribute("name", "y0");
		$simplefield->appendChild($doc->createElement('displayName', "Y0"));
		$schema->appendChild($simplefield);

		$document->appendChild($schema);
		while ($r = self::fetch($q)) {
			$placemark = $doc->createElement('Placemark');
			$name = $doc->createElement('name', "E{$r['x0']}N{$r['y0']}");
			$placemark->appendChild($name);
			$doc_geom = new \DOMDocument();
			$doc_geom->loadXML($r['kml']);
			$placemark->appendChild($doc->importNode($doc_geom->firstChild, true));
			$exd = $doc->createElement("ExtendedData");
			$sdata = $doc->createElement('SchemaData');
			$sdata->setAttribute("schemaUrl", "#attributs_id");

			$data = $doc->createElement("SimpleData", $r['annee']);
			$data->setAttribute("name", "year");
			$sdata->appendChild($data);

			$data = $doc->createElement("SimpleData", $r['x0']);
			$data->setAttribute("name", "x0");
			$sdata->appendChild($data);

			$data = $doc->createElement("SimpleData", $r['y0']);
			$data->setAttribute("name", "y0");
			$sdata->appendChild($data);

			$exd->appendChild($sdata);
			$placemark->appendChild($exd);
			$folder->appendChild($placemark);
		}
		$document->appendChild($folder);
		$kml->appendChild($document);
		$doc->appendChild($kml);
		return $doc;
	}

	/**
	 * @brief Test si l'espèce a été vue dans un carré
	 * @param $srid numéro de projection de la grille
	 * @param $pas pas de la grille
	 * @param $x
	 * @param $y
	 * @param $ymin annee minimum
	 * @return boolean
	 */
	public function get_index_atlas_repartition_x_y($srid, $pas, $x, $y, $ymin) {
		$q = bobs_qm()->query($this->db, "ix_rep_xy_ymax", self::sql_index_repartition_existe, [$this->id_espece, $srid, $pas, $x,$y,$ymin]);
		$r = self::fetch($q);
		return isset($r['n']);
	}

	const sql_sel_rep_logs = 'select * from repartitions.log order by date_deb desc limit 100';

	/**
	 * @brief historique des maj des cartes de répartitions (mailles)
	 * @param $db connection à la base de données
	 * @return array (date_deb, date_fin,n_espece,max_id_citation)
	 */
	public static function atlas_repartitions_logs($db) {
		$q = bobs_qm()->query($db, "sql_sel_rep_logs", self::sql_sel_rep_logs, array());
		return self::fetch_all($q);
	}


	const sql_maj_eff_moyen_1 = 'delete from stats_validation.effectifs where id_espece=$1';
	const sql_maj_eff_moyen_2 = 'insert into stats_validation.effectifs (id_espece,moyenne) values ($1,$2)';
	const sql_sel_eff_moyen = 'select moyenne from stats_validation.effectifs where id_espece=$1';

	public function atlas_repartition_maj() {
		$resolutions = array(
			array($this->id_espece,5000,2154),
			array($this->id_espece,10000,2154),
			array($this->id_espece,10000,3035)
		);
		foreach ($resolutions as $params) {
			if (!bobs_qm()->query($this->db, 'maj_atlas5', "select repartition_espece($1, $2, $3);", $params))
				throw new \Exception("échec maj atlas $this #{$this->id_espece} - {$params[1]} {$params[2]}");
		}
	}

	private $__validation_moyenne = null;

	/**
	 * @brief Mise à jour de l'effectif moyen pour les données de validation
	 */
	public function validation_maj_effectif_moyen() {
		$t = [];
		$extraction  = new bobs_extractions($this->db);
		$extraction->ajouter_condition(new bobs_ext_c_espece($this->id_espece));
		$compte = $extraction->compte();
		foreach ($extraction->get_citations() as $citation) {
			if ($citation->invalide())
				continue;
			if ($citation->nb < 1)
				continue;
			$t[] = $citation->nb;
		}
		self::query($this->db, "begin");
		bobs_qm()->query($this->db, 'valid_maj_eff_moyen_1', self::sql_maj_eff_moyen_1, array($this->id_espece));
		$n = count($t);
		if ($n > 0) {
			$moy = array_sum($t)/$n;
			bobs_qm()->query($this->db, 'valid_maj_eff_moyen_2', self::sql_maj_eff_moyen_2, array($this->id_espece, $moy));
		}
		self::query($this->db, "commit");
		return true;
	}

	/**
	 * @brief effectif moyen précalculé pour la validation
	 */
	public function validation_effectif_moyen() {
		if (is_null($this->__validation_moyenne)) {
			$q = bobs_qm()->query($this->db, 'sv_sel_eff_moy', self::sql_sel_eff_moyen, array($this->id_espece));
			$r = self::fetch($q);
			$this->__validation_moyenne = $r['moyenne'];
		}
		return $this->__validation_moyenne;
	}

	const sql_maj_periode_1 = 'delete from stats_validation.periodes_especes where id_espece=$1';
	const sql_maj_periode_2 = 'insert into stats_validation.periodes_especes (id_espece,decade) values ($1,$2)';

	/**
	 * @brief Mise à jour des périodes d'observation pour les données de validation
	 */
	public function validation_maj_periodes_observ($n_decades=10) {
		$data_decades = $this->validation_extraction_decades($n_decades);
		$periodes = $this->validation_extraction_periodes($data_decades);
		self::query($this->db, "begin");
		bobs_qm()->query($this->db, 'valid_maj_per_1', self::sql_maj_periode_1, array($this->id_espece));
		if (!empty($periodes)) {
			foreach ($periodes as $periode) {
				for ($i=$periode[0];$i<=$periode[1];$i++) {
					bobs_qm()->query($this->db, 'valid_maj_per_2', self::sql_maj_periode_2, array($this->id_espece, $i));
				}
			}

		}
		self::query($this->db, "commit");
	}

	/**
	 * @brief produit un tableau du nombre d'observations par décade
	 * @param $n_decades nombres de décades a extraire jusqu'a y-1
	 * @return array : t[annee][decade] = n
	 */
	private function validation_extraction_decades($n_decades) {
		try {
			$extraction = new bobs_extractions($this->db);
			$extraction->ajouter_condition(new bobs_ext_c_espece($this->id_espece));
			$annee_max = strftime("%Y");
			for ($i=1;$i<=$n_decades+1;$i++) {
				$extraction->ajouter_condition(new bobs_ext_c_annee($annee_max-$i));
			}
			$t = [];
			foreach($extraction->get_citations() as $citation) {
				if($citation->invalide()) {
					continue;
				}

				$observation = $citation->get_observation();
				// passe si la précision de la date d'observation est supérieur à +- 5 jours
				if ($observation->precision_date >= 5) {
					continue;
				}

				$annee = strftime("%Y",strtotime($observation->date_observation));

				$decade = self::decade($observation->date_observation);

				if (!isset($t[$annee][$decade])) {
					$t[$annee][$decade] = 0;
				}

				$t[$annee][$decade]++;
			}
			return $t;
		} catch (Exception $e) {
			return false;
		}
	}

	/**
	 * @brief liste les périodes d'observation de l'espèce
	 */
	private function validation_extraction_periodes($t) {
		$n_decades = 10;
		try {
			$cpt_zero=0;
			$cpt_nb_cases=370;
			$i=0;

			// parcourt les cases du tableau et compte nb cases indefinies.
			foreach ($t as $k=>$v) {
				for($i=0;$i<=36;$i++){
					if(!isset($v[$i]))
						$cpt_zero++;
				}
			}

			// somme des n. obs par decades
			$som=0;
			$somme=array();
			foreach($t as $decades){
				foreach($decades as $num_decade=>$nb){
					if(!isset($somme[$num_decade]))
						$somme[$num_decade]=0;
					$somme[$num_decade]+=$nb;
				}
			}

			// calcul moyennes des n. obs par decades
			$moyenne=array();
			for($i=0;$i<37;$i++){
				if(!isset($somme[$i]))
					$somme[$i]=0;
				$moyenne[$i]=($somme[$i]/$n_decades);
			}

			// moyenne des sommes des obs par decades
			$somme_somme=0;
			foreach($somme as $v)
				$somme_somme+=$v;
			$moyenne_somme=($somme_somme/37);

			//moyenne des moyennes des obs par decades
			$somme_moyenne=0;
			foreach($moyenne as $v)
				$somme_moyenne+=$v;

			if ($somme_moyenne == 0)
				return false;

			$moyenne_moyenne=($somme_moyenne/37);

			if ($cpt_zero <= ((1/3)*$cpt_nb_cases)){             		//si 1/3 des cases ou moins sont a 0
				if($cpt_zero<=((0.1)*$cpt_nb_cases)){     		//10% ou moins de 360 cases a 0.
					//prendre decades jusqu'a 30%.
					//faire pourcentages par rapport a la somme des obs par decades et la moyennes des sommes
					$pourcent=array();
					for($i=0;$i<=36;$i++){
						if (!isset($moyenne[$i]))
							$pourcent[$i]=0;
						else
							$pourcent[$i]=($moyenne[$i]/$moyenne_moyenne)*100;
					}

					//prendre decades >=30%
					$cpt=0;						//le cpt est important il sert a eviter de rentrer une valeur et eviter un test
					$l=0;
					$periode=array(array());

					for($i=0;$i<=36;$i++){              		//rempli un tableau $periode selon certaines conditions
						if(($pourcent[$i]>=30)&&($cpt==0)){ 	//si le pourcentage >= 25 et le compteur a 0
							$periode[$l][0]=$i;  		//met $i dans la case
							$cpt++; 			//ajoute 1 au cpt
						}
						if(($pourcent[$i]<30)&&($cpt>0)){  	//si le pourcentage est < 25 et cpt > 0
							$periode[$l][1]=$i-1; 		//met la valeur $i inferieure
							$cpt=0; 			//remet compteur a 0 pour pouvoir retourner dans la condition superieur
							$l++; 				//change de ligne
						}
					}

					if($cpt>0)
						$periode[$l][1] = 36;
				} else {
					//prendre decades jusqu'a 50%.
					//faire pourcentages par rapport a la somme des obs par decades et la moyennes des somme
					$pourcent=array();
					for($i=0;$i<=36;$i++){
					if (!isset($moyenne[$i]))
							$pourcent[$i]=0;
						else
							$pourcent[$i]=($moyenne[$i]/$moyenne_moyenne)*100;
					}
					//prendre decades >=50%
					$cpt=0;						//le cpt est important il sert a eviter de rentrer une valeur et eviter un test
					$l=0;
					$periode=array(array());
					for($i=0;$i<=36;$i++){              		//rempli un tableau $periode selon certaines conditions
						if(($pourcent[$i]>=50)&&($cpt==0)){ 	//si le pourcentage >= 25 et le compteur a 0
							$periode[$l][0]=$i;  		//met $i dans la case
							$cpt++; 			//ajoute 1 au cpt
						}
						if(($pourcent[$i]<50)&&($cpt>0)){  	//si le pourcentage est < 25 et cpt > 0
							$periode[$l][1]=$i-1; 		//met la valeur $i inferieure
							$cpt=0; 			//remet compteur a 0 pour pouvoir retourner dans la condition superieur
							$l++; 				//change de ligne
						}
					}
					if($cpt>0)
						$periode[$l][1]=36;
				}
			} else {                                            		//plus d'un tier des cases a 0.
				if($cpt_zero>=((0.7)*$cpt_nb_cases)){     		//si au moin 70% des cases sont a 0.
					//faire pourcentages par rapport a la somme des obs par decades et la moyennes des somme
					$pourcent=array();
					for($i=0;$i<=36;$i++){
						if (!isset($somme[$i]))
							$pourcent[$i] = 0;
						else
							$pourcent[$i] = ($somme[$i]/$moyenne_somme)*100;
					}
					//prendre decades >=25%
					$cpt=0;						//le cpt est important il sert a eviter de rentrer une valeur et eviter un test
					$l=0;
					$periode=array(array());
					for($i=0;$i<=36;$i++){              		//rempli un tableau $periode selon certaines conditions
						if(($pourcent[$i]>=25)&&($cpt==0)){ 	//si le pourcentage >= 25 et le compteur a 0
							$periode[$l][0]=$i;  		//met $i dans la case
							$cpt++; 			//ajoute 1 au cpt
						}
						if(($pourcent[$i]<25)&&($cpt>0)){  	//si le pourcentage est < 25 et cpt > 0
							$periode[$l][1]=$i-1; 		//met la valeur $i inferieure
							$cpt=0; 			//remet compteur a 0 pour pouvoir retourner dans la condition superieur
							$l++; 				//change de ligne
						}
					}
					if($cpt>0)
						$periode[$l][1]=36;
				} else {
					//prendre decades >=25% pourcentages par rapport a la moyenne des obs par decades et moyenne generale des moyennes des obs par decades
					//faire pourcentages par rapport a la somme des obs par decades et la moyennes des somme
					$pourcent=array();
					for($i=0;$i<=36;$i++){
						if (!isset($moyenne[$i]))
							$pourcent[$i]=0;
						else
							$pourcent[$i]=($moyenne[$i]/$moyenne_moyenne)*100;
					}
					//prendre decades >=25%
					$cpt=0;						//le cpt est important il sert a eviter de rentrer une valeur et eviter un test
					$l=0;
					$periode=array(array());
					for($i=0;$i<=36;$i++){              		//rempli un tableau $periode selon certaines conditions
						if(($pourcent[$i]>=25)&&($cpt==0)){ 	//si le pourcentage >= 25 et le compteur a 0
							$periode[$l][0]=$i;  		//met $i dans la case
							$cpt++; 			//ajoute 1 au cpt
						}
						if(($pourcent[$i]<25)&&($cpt>0)){  	//si le pourcentage est < 25 et cpt > 0
							$periode[$l][1]=$i-1; 		//met la valeur $i inferieure
							$cpt=0; 			//remet compteur a 0 pour pouvoir retourner dans la condition superieur
							$l++; 				//change de ligne
						}
					}
					if($cpt>0)
						$periode[$l][1]=36;
				}
			}
			return($periode);
		} catch (\Exception $e) {
			return false;
		}
	}

	/**
	 * @todo implémenter
	 * @brief paramètres de validation personalisés pour l'espèce
	 */
	public function get_validation_params($classe) {
		return [];
	}
}
