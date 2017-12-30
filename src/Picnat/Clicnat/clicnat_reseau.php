<?php
namespace Picnat\Clicnat;

class clicnat_reseau implements i_clicnat_reseau, \JsonSerializable {
	/** @brief critères pour les requêtes sql */
	protected $where;
	/** @brief nom du réseau */
	protected $nom;
	/** @brief discriminant pour les noms de requêtes */
	protected $q_base;
	/** @brief handler db */
	protected $db;
	/** @brief identifiant du réseau dans gdtc */
	protected $id_gdtc;
	/** @brief nom espèce affiché dans les restitutions auto */
	protected $restitution_nom_s;
	/** @brief nombre de jours a remonter dans restitution auto */
	protected $restitution_nombre_jours = 10;
	/** @brief restitution auto active */
	protected $restitution_auto = false;
	/** @brief coordinateurs du réseau */
	protected $coordinateurs = [];
	/** @brief validateurs du réseau */
	protected $validateurs = [];

	const liste_reseaux = 'ar,cs,mt,mm,av,po,ae,co,cr,sc,li,pa,pu,sy,ai,ml,cu,an';
	const sql_l_validateurs = '
		select distinct reseau_validateurs.id_utilisateur
		from reseau_validateurs
		where id_reseau=$1';

	public function jsonSerialize() {
		return [
			"id" => $this->q_base,
			"nom" => $this->nom
		];
	}

	/**
	 * @brief constructeur
	 * @see get_reseau()
	 * @param $db handler db
	 * @param $nc nom court du réseau
	 */
	public function __construct($db, $nc) {
		switch ($nc) {
			case 'sy':
				$this->nom = 'Syrphes';
				$this->where = "famille ilike 'syrph%' and classe='I'";
				$this->id_gdtc = 30;
				$this->coordinateurs = [663];
				$this->restitution_nom_s = true;
				$this->restitution_auto = true;
				break;
			case 'cs':
				$this->nom = 'Chiroptères';
				$this->where = "classe='M' and ordre ilike 'chiropt%'";
				$this->id_gdtc = 5;
				$this->coordinateurs = [2093];
				$this->restitution_nom_s = false;
				break;
			case 'ar':
				$this->nom = 'Amphibiens - reptiles';
				$this->where = "(classe='R' or classe='B')";
				$this->id_gdtc = 2;
				$this->coordinateurs = [955];
				$this->restitution_nom_s = false;
				$this->restitution_auto = true;
				break;
			case 'sc':
				$this->nom = 'Criquets - sauterelles';
				$this->where = "classe='I' and (ordre ilike 'orthopt%' or ordre ilike 'Dermaptera' or especes.id_espece in (96,4184,4648,4646,4647,4649,4616,49,4785,4913,5441,4785,4616,49))";
				$this->id_gdtc = 3;
				$this->coordinateurs = [147];
				$this->restitution_nom_s = false;
				$this->restitution_auto = true;
				break;
			case 'li':
				$this->nom = 'Libellules';
				$this->where = "classe='I' and ordre ilike 'odonat%'";
				$this->id_gdtc = 4;
				$this->coordinateurs = [310];
				$this->restitution_nom_s = false;
				$this->restitution_auto = true;
				break;
			case 'mm':
				$this->nom = 'Mammifères marins';
				$this->where = "classe='M' and (ordre ilike 'pinni%' or ordre ilike 'c%tac%')";
				$this->id_gdtc = 9;
				$this->coordinateurs = [2420];
				$this->restitution_nom_s = false;
				break;
			case 'mt':
				$this->nom = 'Mammifères terrestres';
				$this->where = "classe='M' and ordre not ilike 'chiro%' and ordre not ilike 'pinni%' and ordre not ilike 'c%tac%'";
				$this->id_gdtc = 8;
				$this->coordinateurs = [252,32];
				$this->restitution_nom_s = false;
				$this->restitution_auto = true;
				break;
			case 'ml':
				$this->nom = 'Mollusques';
				$this->where = "classe in ('L','G')";
				$this->id_gdtc = 6;
				$this->coordinateurs = [113];
				$this->restitution_nom_s = false;
				break;
			case 'av':
				$this->nom = 'Oiseaux';
				$this->where = "classe='O'";
				$this->id_gdtc = 7;
				$this->coordinateurs = [17,119,955];
				$this->restitution_nom_s = false;
				$this->restitution_auto = true;
				break;
			case 'pa':
				$this->nom = 'Papillons';
				$this->where = "classe='I' and ordre ilike 'l%pidopt%'";
				$this->id_gdtc = 17;
				$this->coordinateurs = [828];
				$this->restitution_nom_s = true;
				$this->restitution_auto = true;
				break;
			case 'ae':
				$this->nom = 'Araignées';
				$this->where = "classe='A'";
				$this->id_gdtc = '27';
				$this->coordinateurs = [2075];
				$this->restitution_nom_s = true;
				$this->restitution_auto = true;
				break;
			case 'co':
				$this->nom = 'Coccinelles';
				$this->where = 'especes.id_espece in (select id_espece from listes_especes_data where id_liste_espece=21)';
				$this->id_gdtc = 26;
				$this->coordinateurs = [2109];
				$this->restitution_nom_s = false;
				$this->restitution_auto = true;
				break;
			case 'cr':
				$this->nom = 'Coléoptères';
				$this->where = "classe='I' and ordre ilike 'col%opt%'";
				$this->id_gdtc = null;
				$this->coordinateurs = [];
				$this->restitution_nom_s = false;
				break;
			case 'po':
				$this->nom = 'Poissons';
				$this->where = "classe='P'";
				$this->id_gdtc = null;
				$this->coordinateurs = [];
				$this->restitution_nom_s = false;
				break;
			case 'cu':
				$this->nom = 'Crustacés';
				$this->where = "classe='C'";
				$this->id_gdtc = null;
				$this->coordinateurs = [];
				$this->restitution_nom_s = false;
				break;
			case 'an':
				$this->nom = 'Annélides';
				$this->where = "classe='N'";
				$this->id_gdtc = null;
				$this->coordinateurs = [];
				$this->restitution_nom_s = false;
				break;
			case 'pu':
				$this->nom = 'Punaises';
				$this->where = 'especes.id_espece in (select id_espece from listes_especes_data where id_liste_espece=456)';
				$this->id_gdtc = null;
				$this->coordinateurs = [2910];
				$this->restitution_nom_s = true;
				break;
			case 'ai':
				$this->nom = 'Autre insectes';
				$this->id_gdtc = null;
				$this->coordinateurs = [];
				$this->restitution_nom_s = false;
				$this->where = "
					classe='I' and
					especes.id_espece not in (select id_espece from especes where
						classe='I' and (ordre ilike 'orthopt%' or ordre ilike 'Dermaptera' or especes.id_espece in (96,4184,4648,4646,4647,4649,4616,49,4785,4913,5441,4785,4616,49))
						or
						(classe='I' and ordre ilike 'odonat%')
						or
						(classe='I' and ordre ilike 'l%pidopt%')
						or
						(classe='I' and ordre ilike 'col%opt%')
						or
						(especes.id_espece in (select id_espece from listes_especes_data where id_liste_espece=21))
						or
						(famille ilike 'syrph%' and classe='I')
						or
						(especes.id_espece in (select id_espece from listes_especes_data where id_liste_espece=456))
					)";
				break;
			case 'tous':
				$this->nom = 'Tous les taxons';
				$this->id_gdtc = null;
				$this->coordinateurs = [];
				$this->restitution_nom_s = false;
				$this->where = '1=1';
				break;
			default:
				throw new Exception('réseau inconnu '.$nc);
				break;
		}
		$this->q_base = $nc;
		$this->db = $db;
		$this->validateurs = $this->validateurs();
	}

	public function __get($prop) {
		switch ($prop) {
			case 'id':
				return $this->q_base;
			case 'id_gdtc':
				return $this->id_gdtc;
			case 'where':
				return $this->where;
			case 'restitution_nombre_jours':
				return $this->restitution_nombre_jours;
			case 'restitution_nom_s':
				return $this->restitution_nom_s;
			case 'restitution_f_ec':
				return "/tmp/{$this->id}_ec.html";
			case 'restitution_f_ce':
				return "/tmp/{$this->id}_ce.html";
			case 'restitution_f_li':
				return "/tmp/{$this->id}_li.html";
			case 'restitution_auto':
				return $this->restitution_auto;
			case 'nom':
				return $this->nom;
			case 'coordinateurs':
				return new clicnat_iterateur_utilisateurs($this->db, $this->coordinateurs);
		}
	}
	private function validateurs() {
		if (!$this->db)
			$this->db = get_db();
		$q = bobs_qm()->query($this->db, 'reseau_l_valid', self::sql_l_validateurs, [$this->q_base]);
		$r = bobs_element::fetch_all($q,'id_utilisateur');
		return $r ;
	}

	public static function liste_reseaux($db) {
		$resaux = array();
		foreach (explode(',', self::liste_reseaux) as $reseau) {
			$reseaux[] = get_bobs_reseau($db, $reseau);
		}
		return $reseaux;
	}

	/**
	 * @brief retourne le nombre d'espèces du réseau
	 */
	public function get_n_especes() {
		$sql = "select count(*) as n from especes where {$this->where}";
		$q = bobs_qm()->query($this->db, 'bob_n_e_'.$this->q_base, $sql, array());
		$r = bobs_element::fetch($q);
		return $r['n'];
	}

	/**
	 * @brief retourne la liste des espèces du réseau
	 */
	public function get_liste_especes() {
		$sql = "select * from especes where {$this->where} order by ordre,famille,nom_s";
		$q = bobs_qm()->query($this->db, 'bob_l_e_'.$this->q_base, $sql, array());
		return bobs_element::fetch_all($q);
	}

	public function __toString() {
		return $this->nom;
	}

	public function get_id() {
		return $this->q_base;
	}

	public function espece_dans_le_reseau($id_espece) {
		bobs_tests::cli($id_espece, bobs_tests::except_si_inf_1);
		$sql = "select count(*) as n from especes where {$this->where} and id_espece=$1";
		$q = bobs_qm()->query($this->db, 'bob_l_eir_'.$this->q_base, $sql, array($id_espece));
		$r = bobs_element::fetch($q);
		return $r['n'] == 1;
	}

	public static function get_reseau_espece($db, $id_espece) {
		foreach (explode(',', self::liste_reseaux) as $res) {
			$reseau = get_bobs_reseau($db, $res);
			if ($reseau->espece_dans_le_reseau($id_espece))
				return $reseau;
		}
		return false;
	}

	public function maj_stats_nb_esp_par_maille($pas=10000, $crs=2154) {
		$demipas = $pas/2;
		$schema = "repartitions";
		$table = "{$this->q_base}_nspecies_crs{$crs}res{$pas}m";
		$requetes = array();
		$requetes[] = "drop table if exists $schema.$table";
		$requetes[] = "create table $schema.$table (id serial, n integer, primary key(id))";
		$requetes[] = "select AddGeometryColumn('$schema', '$table', 'the_geom', $crs, 'POINT', 2)";

		$requetes[] = "insert into $schema.$table (the_geom,n) select geom,sum(n_especes) as n_especes from (
			select
				setsrid(geomfromtext('POINT('||(x0*pas+pas/2)||' '||y0*pas+pas/2||')'),$crs) as geom, count(distinct c.id_espece) as n_especes
			from citations c,especes,espace_index_atlas eia,observations o left join espace_polygon ep on ep.id_espace=o.id_espace
                        where o.brouillard = false
                        and c.id_espece=especes.id_espece
                        and o.id_espace=eia.id_espace
                        and eia.srid=$crs
                        and eia.pas=$pas
                        and coalesce(c.nb,0) != -1
                        and o.id_observation=c.id_observation
                        and (c.indice_qualite > 2 or c.indice_qualite is null)
                        and {$this->where}
                        and c.id_citation not in (
                                select ct.id_citation
                                from citations c,citations_tags ct,especes
                                where ct.id_citation = c.id_citation and ct.id_tag in (591,592) and classe='I' and {$this->where}
                                and c.id_espece=especes.id_espece
                        ) and (coalesce(superficie,0)<=superficie_max or superficie_max=0)
			group by pas, setsrid(geomfromtext('POINT('||(x0*pas+pas/2)||' '||y0*pas+pas/2||')'),2154)) as subq group by geom
			";
		$n = 0;
		foreach ($requetes as $sql) {
			$n++;
			echo "Execute $sql\n";
			bobs_qm()->query($this->db, 'res_n_esp_m_'.$this->q_base.'_'.$n, $sql, array());
		}
	}

	/**
	 * @deprecated
	 */
	public function maj_stats_nb_cit_par_maille($pas=10000, $crs=2154) {
		$schema = "repartitions";
		$table = "{$this->q_base}_nbcitation_crs{$crs}res{$pas}m";
		$requetes = array();
		$requetes[] = "drop table if exists $schema.$table";
		$requetes[] = "create table $schema.$table (id serial, n integer, primary key(id))";
		$requetes[] = "select AddGeometryColumn('$schema', '$table', 'the_geom', $crs, 'POINT', 2)";
		$requetes[] = "insert into $schema.$table (the_geom,n) select geom,sum(n_citation) as n_citation from (
			select
				setsrid(geomfromtext('POINT('||(x0*pas+pas/2)||' '||y0*pas+pas/2||')'),$crs) as geom,
				count(c.id_citation) as n_citation, (coalesce(superficie,0)<=superficie_max or superficie_max=0) as utilisable
			from citations c,especes,espace_index_atlas eia,observations o left join espace_polygon ep on ep.id_espace=o.id_espace
			where o.brouillard = false
			and c.id_espece=especes.id_espece
			and o.id_espace=eia.id_espace
			and eia.srid=$crs
			and eia.pas=$pas
			and coalesce(c.nb,0) != -1
			and o.id_observation=c.id_observation
			and (c.indice_qualite > 2 or c.indice_qualite is null)
			and {$this->where}
			and c.id_citation not in (
				select ct.id_citation
				from citations c,citations_tags ct,especes
				where ct.id_citation = c.id_citation and ct.id_tag in (591,592) and {$this->where}
				and c.id_espece=especes.id_espece
			)
			group by pas,x0*pas,y0*pas,superficie,superficie_max) as subq where utilisable=true group by geom\n";
		$n = 0;
		foreach ($requetes as $sql) {
			$n++;
			echo "Execute $sql\n";
			bobs_qm()->query($this->db, 'res_n_cit_m_'.$this->q_base.'_'.$n, $sql, array());
		}
	}
	/**
	 * @brief test si l'utilisateur est coordinateur sur ce réseau
	 * @param $id_utilisateur l'identifiant de l'utilisateur
	 * @return true
	 */
	public function est_coordinateur($id_utilisateur) {
		return in_array($id_utilisateur, $this->coordinateurs);
	}

	/**
	 * @brief test si l'utilisateur est validateur sur ce réseau
	 * @param $id_utilisateur l'identifiant de l'utilisateur
	 * @return true
	 */
	public function est_validateur($id_utilisateur) {
		foreach ($this->validateurs as $val){
			if($val['id_utilisateur'] == $id_utilisateur)
				return true;
		}
		return false;
	}

	public static function planifier_mad_tete_reseau($db) {
		clicnat_tache::ajouter($db, strftime("%Y-%m-%d %H:%M:%S",mktime()), 0, "MAD tête de réseau", "clicnat_mad_tete_reseau", []);
	}
}
