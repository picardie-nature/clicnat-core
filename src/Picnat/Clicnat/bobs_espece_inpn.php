<?php
namespace Picnat\Clicnat;

/**
 * @brief Espèce (référentiel MNHN)
 */
class bobs_espece_inpn extends bobs_abstract_espece {
	public $regne;
	public $phylum;
	public $classe;
	public $ordre;
	public $famille;
	public $cd_nom;
	public $cd_taxsup;
	public $lb_nom;
	public $lb_auteur;
	public $cd_ref;
	public $rang_es;
	public $nom_vern;
	public $nom_vern_eng;
	public $fr;
	public $mar;
	public $gua;
	public $smsb;
	public $gf;
	public $spm;
	public $rev;
	public $may;
	public $taaf;

	protected $protections;

	public function __construct($db, $id) {
		parent::__construct($db, 'taxref_inpn_especes', 'cd_nom', $id);
		$this->protections = [];
		// les protections ne sont pas à jour
		// if (!empty($this->cd_nom))
		//    $this->get_protections();
		//
	}

	public function __toString() {
		return $this->lb_nom.' '.$this->lb_auteur;
	}

	/**
	 *
	 * @param handler $db
	 * @param bobs_espece $obj_espece
	 * @return array
	 */
	public static function recherche_pour_espece_fnat($db, $obj_espece) {
		$tnom = explode(' ',$obj_espece->nom_s);
		$nom = $tnom[0];

		$classe = $obj_espece->get_classe_lib_par_lettre($obj_espece->classe, false);
		self::cls($classe);

		if (empty($classe)) {
			throw new \Exception('Classe est vide nouvelle classe ?');
		}
		$sql = "select * from taxref_inpn_especes
				where lower(lb_nom) like lower('%'||$1||'%')
				and classe = $2
				and regne = 'Animalia'
				order by lb_nom
				limit 100";
		$q = bobs_qm()->query($db, 'recherche_e_p_fnat', $sql, array($nom, $classe));
		$t = self::fetch_all($q);
		return $t;
	}

	/**
	 * @brief Les noms des propriétés
	 *
	 * retoune un tableau associatif qui a pour clé le nom d'une propriété
	 * de l'objet et son "étiquette" en valeurs.
	 *
	 * @return array un tableau associatif
	 */
	public static function get_prop_libs() {
		return array(
			'regne' => 'Nom scientifique du règne du taxon',
			'phylum' => 'Nom scientifique de l\'embranchement du taxon',
			'classe' => 'Nom scientifique de la classe du taxon',
			'ordre' => 'Nom scientifique de l\'ordre du taxon',
			'famille' => 'Nom scientifique de la famille du taxon',
			'cd_nom' => 'Identifiant unique',
			'lb_nom' => 'Nom scientifique du taxon',
			'lb_auteur' => 'autorité',
			'cd_ref' => 'renvoi à Identifiant unique du taxon de référence ',
			'rang_es' => 'rang taxonomique',
			'nom_vern' => 'Nom vernaculaire du taxon en français',
			'nom_vern_eng' => 'Nom vernaculaire du taxon en anglais',
			'fr' => 'Présence / indigénat du taxon en France métropolitaine',
			'mar' => 'Présence / indigénat du taxon en Martinique',
			'gua' => 'Présence / indigénat du taxon en Guadeloupe',
			'smsb' => 'Présence / indigénat du taxon à Saint-Martin et Saint-Barthélémy',
			'gf' => 'Présence / indigénat du taxon en Guyane française',
			'spm' => 'Présence / indigénat du taxon à Saint-Pierre et Miquelon',
			'rev' => 'Présence / indigénat du taxon à la Réunion',
			'may' => 'Présence / indigénat du taxon à Mayotte',
			'taaf' => 'Présence / indigénat du taxon aux Terres australes et antarctiques françaises'
		);
	}
	const sql_insert_index = "insert into taxref_inpn_especes_index (id_taxref_inpn_especes, ordre, mot) values ($1, $2, $3)";
	/**
	 * @brief Reconstruction de l'index
	 *
	 * Vide la table taxref_inpn_especes_index et la reconstruit
	 */
	public static function index_rebuild($db) {
		self::query($db,'begin');
		self::query($db,'delete from taxref_inpn_especes_index');
		$q = self::query($db,'select lb_nom,cd_nom from taxref_inpn_especes');
		$pos = 0;
		while ($r = self::fetch($q)) {
			$esp = new bobs_espece_inpn($db, $r);
			try {
			    $mots = self::index_nom($esp->lb_nom);
			} catch (\Exception $e) {
			    echo "ATTENTION NE PEUT INDEXER ESPECE {$esp->cd_nom}\n";
			    continue;
			}
			$n = 0;
			foreach ($mots as $mot) {
				bobs_qm()->query($db, "ins_tax_inpn_esp", self::sql_insert_index, array($esp->cd_nom, $n, $mot));
				$n++;
			}
			$pos++;
			printf("Index INPN espèces : %05d\r", $pos);
			flush();
		}
		self::query($db,'commit');
		echo "\n";
	}

	/**
	 * @brief Recherche une espèces dans l'index
	 *
	 * @see bobs_abstract_espece::index_recherche
	 */
	public static function index_recherche($db, $nom) {
		$r = parent::__index_recherche($db, $nom, 'taxref_inpn_especes_index', 'id_taxref_inpn_especes', 'bobs_espece_inpn');
		$ks = [];
		$es = [];
		foreach ($r['especes'] as $k => $v) {
			$ks[$k] = levenshtein($nom,$r['especes'][$k]->lb_nom);
		}
		asort($ks);
		$r['levs'] = $ks;
		foreach ($r['levs'] as $k => $v) {
			$es[] = $r['especes'][$k];
		}
		$r['especes'] = $es;
		return $r;
	}

	const sql_protect_inpn = 'select pa.* from
					inpn.protections_articles pa,
					inpn.protections_especes pe,
					taxref_inpn_especes esp_a,
					taxref_inpn_especes esp_b
				where pe.cd_protection=pa.cd_protection
				and esp_a.cd_nom = $1
				and esp_a.cd_ref = esp_b.cd_ref
				and esp_b.cd_nom = pe.cd_nom';


	/**
	 * @brief Liste les protections pour cette espèce (et ses synonymes)
	 * @deprecated pas à jour
	 *
	 * Recherche les protections
	 *
	 * @return array un tableau associatif
	 */
	public function get_protections() {
	    return self::fetch_all(bobs_qm()->query($this->db, 'esp_inpn_prot', self::sql_protect_inpn, array($this->cd_nom)));
	}

	const sql_ref = 'select * from taxref_inpn_especes where cd_ref=$1';

	/**
	 * @brief retoune les taxons faisant référence à celui dans ce référentiel
	 * @return array un tableau associatif
	 */
	public function get_references() {
		$q = bobs_qm()->query($this->db, 'taxref_g_ref', self::sql_ref, array($this->cd_ref));
		return self::fetch_all($q);
	}

	/**
	 * @brief insert l'espece dans le referentiel de la base
	 * @return integer le numéro de la nouvelle espèce
	 */
	public function insert_in_especes() {
		$classes = array(
			'Insecta' => 'I',
			'Arachnida' => 'A',
			'Aves' => 'O',
			'Bivalvia' => 'L',
			'Annelida' => 'N',
			'Malacostraca' => 'C',
			'Actinopterygii' => 'P',
			'Branchiopoda' => 'C',
			'Mammalia' => 'M',
			'Diplopoda' => 'I',
			'Hydrozoa' => 'H',
			'Reptilia' => 'R',
			'Chilopoda' => 'S',
			'Cephalaspidomorphi' => 'P',
			'Diplopoda' => 'D',
			'Amphibia' => 'B',
			'Chordata' => '_',
			'Animalia' => '_',
			'Gastropoda' => 'G'
		);

		if (empty($this->classe))
			$this->classe = $this->phylum;

		$args = array(
			'classe' => $classes[$this->classe],
			'ordre' => $this->ordre,
			'famille' => $this->famille,
			'nom_f' => $this->nom_vern,
			'nom_s' => $this->lb_nom,
			'nom_a' => $this->nom_vern_eng,
			'commentaire' => '',
			'systematique' => '',
			'type_fiche' => ''
		);

		if (empty($args['classe']))
			$args['classe'] = '_';
//			throw new Exception('pas de classe pour '.$this->classe);

		$id_espece = bobs_espece::insert($this->db, $args);
		$espece = get_espece($this->db, $id_espece);
		$espece->ajoute_reference_tiers('taxref', $this->cd_nom);
		return $id_espece;
	}

	const sql_especes = '
		select * from especes,referentiel_especes_tiers ref,taxref_inpn_especes
		where ref.id_espece=especes.id_espece and ref.tiers=\'taxref\'
		and taxref_inpn_especes.cd_nom=ref.id_tiers
		and taxref_inpn_especes.cd_ref=$1
	';
	/**
	 * @brief retourne un tableau d'objets bobs_espece correspondant
	 * @todo mettre un iterateur
	 */
	public function get_especes() {
		$sql =  "select * from especes where taxref_inpn_especes in
				(select cd_nom from taxref_inpn_especes where cd_ref=$1)";
		$q = bobs_qm()->query($this->db, 'inpn_esp_by_cdref', self::sql_especes, [$this->cd_ref]);
		$t = array();
		while ($r = self::fetch($q)) {
			$t[] = get_espece($this->db, $r);
		}
		return $t;
	}
}
