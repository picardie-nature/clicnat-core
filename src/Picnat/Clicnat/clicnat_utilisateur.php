<?php
namespace Picnat\Clicnat;

use Picnat\Clicnat\ExtractionsConditions\bobs_ext_c_ordre;
use Picnat\Clicnat\ExtractionsConditions\bobs_ext_c_interval_date;
use Picnat\Clicnat\ExtractionsConditions\bobs_ext_c_esp_comite_homolog;

/**
 * Classe de gestion des utilisateurs et observateurs
 *
 * @property-read $id_utilisateur
 * @property-read $nom
 * @property-read $prenom
 * @property-read $username
 * @property-read $date_naissance
 * @property-read $tel
 * @property-read $port
 * @property-read $fax
 * @property-read $mail
 * @property-read $url
 * @property-read $commentaires
 * @property-read $acces_qg
 * @property-read $acces_poste
 * @property-read $acces_chiros
 * @property-read $reglement_date_sig
 * @property-read $diffusion_restreinte
 * @property-read $last_login
 * @property-read $last_ip
 * @property-read $id_csnp
 * @property-read $peut_ajouter_espece
 * @property-read $expert
 * @property-read $id_extraction_utilisateur_flux
 * @property-read $props
 * @property-read $pseudo
 * @property-read $partage_opts
 */
class clicnat_utilisateur extends bobs_element {
	use clicnat_mini_template;

	protected $id_utilisateur;
	protected $nom;
	protected $prenom;
	protected $username;
	protected $date_naissance;
	protected $password;
	protected $tel;
	protected $port;
	protected $fax;
	protected $mail;
	protected $url;
	protected $commentaires;
	protected $acces_qg;
	protected $acces_poste;
	protected $acces_chiros;
	protected $reglement_date_sig;
	protected $diffusion_restreinte;
	protected $last_login;
	protected $last_ip;
	protected $id_csnp;
	protected $peut_ajouter_espece;
	protected $expert;
	protected $id_extraction_utilisateur_flux;
	protected $props;

	protected $pseudo;
	protected $partage_opts;

	const sql_set_prop = 'update utilisateur set props=coalesce(props,\'\')::hstore||$2::hstore where id_utilisateur=$1';
	const sql_get_prop = 'select props -> $2 as v from utilisateur where id_utilisateur=$1';
	const sql_get_props = 'select sq.* from ( select (each(props)).key , (each(props)).value from utilisateur where id_utilisateur=$1) as sq order by key';

	public function prop($k, $v=null) {
		if (!is_null($v)) {
			bobs_qm()->query($this->db, 'u_set_proper', self::sql_set_prop, [
				$this->id_utilisateur,
				sprintf("\"%s\"=>\"%s\"", pg_escape_string($k), pg_escape_string($v))
			]);
		}
		$q = bobs_qm()->query($this->db, 'u_get_proper', self::sql_get_prop, [$this->id_utilisateur, $k]);
		$r = self::fetch($q);
		return $r['v'];
	}

	public function props() {
		$q = bobs_qm()->query($this->db, 'u_get_propers', self::sql_get_props, [$this->id_utilisateur]);
		$rs = self::fetch_all($q);
		$tr = [];
		foreach ($rs as $r) {
			$tr[$r['key']] = $r['value'];
		}
		return $tr;
	}

	public function gravatar_hash() {
		return md5(trim(strtolower($this->mail)));
	}

	public function gravatar_img() {
		$hash = $this->gravatar_hash();
		return "https://www.gravatar.com/avatar/$hash";
	}

	/**
	 * @brief met a jour le pseudonyme de l'utilisateur
	 */
	public function set_pseudo($pseudo) {
		self::cls($pseudo);
		if (empty($pseudo)) {
			$this->update_field_null('pseudo', true);
		}
		else $this->update_field('pseudo', $pseudo);
	}

	public function partage_opts($opt) {
		if (empty($this->partage_opts)) {
			return false;
		}

		$opts = json_decode($this->partage_opts, true);

		if (!isset($opts[$opt])) {
			return false;
		}

		return $opts[$opt];
	}

	public function partage_opts_set($opts) {
		$save_opts = array();
		foreach ($this->partage_opts_champs() as $k) {
			$save_opts[$k] = isset($opts[$k])?$opts[$k]:false;
		}
		$this->update_field('partage_opts', json_encode($opts), true);

		if ($this->partage_opts('ma_localisation') != false) {
			$this->update_field('localisation_visible', 'tous');
		} else {
			$this->update_field('localisation_visible', 'restreint');
		}
	}

	public function partage_opts_champs() {
		return [
			'transmettre_nom_avec_donnees',
			'diffusion_restreinte',
			'ma_localisation',
			'mes_medias',
			'liste_espece',
			'journal',
			'pas_de_mail_utilisateurs',
			'pas_de_mail_interaction'
		];
	}

	const sql_l_obs_loc_pub = 'select pseudo,id_utilisateur,st_x(the_geom) as x,st_y(the_geom) as y from utilisateur where localisation_visible=\'tous\' and the_geom is not null';

	public static function liste_observateurs_localisation_public($db) {
		$q = bobs_qm()->query($db, 'l_obs_loc_pub', self::sql_l_obs_loc_pub, array());
		return self::fetch_all($q);
	}

	/**
	 * @brief permet un accès en lecture seule aux propriétés
	 */
	public function __get($prop) {
		switch ($prop) {
			case 'pseudo':
				return $this->pseudo;
			case 'id_utilisateur':
				return $this->id_utilisateur;
			case 'nom':
				return $this->nom;
			case 'prenom':
				return $this->prenom;
			case 'username':
				return $this->username;
			case 'date_naissance' :
				return $this->date_naissance;
			case 'password':
				throw new \Exception('pas directement');
			case 'tel':
				return $this->tel;
			case 'port':
				return $this->port;
			case 'fax':
				return $this->fax;
			case 'mail':
				return $this->mail;
			case 'url':
				return $this->url;
			case 'commentaires';
				return $this->commentaires;
			case 'acces_qg':
				return $this->acces_qg;
			case 'acces_poste':
				return $this->acces_poste;
			case 'acces_chiros':
				return $this->acces_chiros;
			case 'reglement_date_sig':
				return $this->reglement_date_sig;
			case 'diffusion_restreinte':
				return $this->diffusion_restreinte;
			case 'last_login':
				return $this->last_login;
			case 'last_ip':
				return $this->last_ip;
			case 'id_csnp':
				return $this->id_csnp;
			case 'id_gdtc':
				return $this->ref_tiers('gdtc');
			case 'peut_ajouter_espece':
				return $this->peut_ajouter_espece == true;
			case 'expert':
				return $this->expert == true;
			case 'id_extraction_utilisateur_flux':
				return $this->id_extraction_utilisateur_flux;
		}
	}

	public function __construct($db, $id) {
		parent::__construct($db, 'utilisateur', 'id_utilisateur', $id);
		if (empty($this->id_utilisateur)) {
			throw new \Exception('id_utilisateur vide (utilisateur inexistant ?)');
		}
		$this->virtuel = !($this->virtuel == 'f');
		$this->diffusion_restreinte = !($this->diffusion_restreinte == 'f');
		if (!is_null($this->reglement_date_sig)) {
			$this->reglement_date_sig_tstamp = strtotime($this->reglement_date_sig);
		}
		$this->acces_poste = $this->acces_poste == 't';
		$this->acces_chiros = $this->acces_chiros == 't';
		$this->acces_qg = $this->acces_qg == 't';
		$this->expert = $this->expert == 't';
		$this->peut_ajouter_espece = $this->peut_ajouter_espece == 't';
	}

	/**
	 * @brief permet de remettre le handler de la connection à la base
	 */
	public function set_db($db) {
		$this->db = $db;
	}


	/**
	 * @brief nom et prénom
	 */
	public function  __toString() {
	    return trim("{$this->prenom} {$this->nom}");
	}

	const sql_obs_ly = "select count(*) as n,date_observation from observations o,observations_observateurs oo where o.id_utilisateur=$1 and oo.id_observation=o.id_observation and date_observation > (now() - interval '52 week') group by date_observation order by date_observation";

	public function observations_derniere_annees() {
		$q = bobs_qm()->query($this->db, 'obs_ly', self::sql_obs_ly, [$this->id_utilisateur]);
		$t = self::fetch_all($q);
		$tt = [];
		foreach ($t as $l) {
			$tt[$l['date_observation']] = (int)$l['n'];
		}
		return $tt;
	}

	/**
	 * @brief création d'un nouvel utilisateur
	 * @param $db connection à la base de données
	 * @param $args un tableau associatif
	 *
	 * $args peut contenir les clés suivantes :
	 * - nom (obligatoire)
	 * - prenom
	 * - username
	 * - date_naissance
	 * - pwd1 et pwd2 (doivent être identique)
	 * - tel
	 * - port
	 * - fax
	 * - mail
	 */
	public static function nouveau($db, $args) {
		$args['nom'] = trim($args['nom']);
		if (empty($args['nom'])) {
			throw new \InvalidArgumentException('pas de nom');
		}
		$champs = ['nom','prenom','username','tel','port','mail'];
		$values = [];
		foreach ($champs as $c) {
			$val = trim($args[$c]);
			$val = empty($val)?null:$val;
			$values[] = $val;
		}
		$sql = "insert into utilisateur (
				nom,prenom,username,tel,port,mail
			) values (
				$1,$2,$3,$4,$5,$6
			)";

		static $prepared;

		if (!isset($prepared)) {
			pg_prepare($db, 'utilisateur_nouveau', $sql);
			$prepared = true;
		}
		return pg_execute($db, 'utilisateur_nouveau', $values);
	}

	public function modifier($args) {
		$args_ok = ['nom','prenom','username','date_naissance','port','mail','tel','id_csnp','acces_chiros','acces_qg'];

		foreach ($args_ok as $k) {
			switch ($k) {
				case 'date_naissance':
					if (empty($args[$k])) {
						$this->update_field_null($k, true);
					} else {
						$date = bobs_element::date_fr2sql($args[$k]);
						$this->update_field($k, $date);
					}
					break;
				default:
					$this->update_field($k, $args[$k]);
					break;
			}
		}
		if (!empty($args['pwd1'])) {
			if ($args['pwd1'] == $args['pwd2']) {
				$this->set_password($args['pwd1']);
			}
		}
		return true;
	}

	const sql_recherche_utilisateur = "select * from bob_recherche_observateur_nom($1) order by levenshtein(trim(lower($2)), trim(lower(nom||' '||prenom)))";

	public static function rechercher2($db, $str) {
		self::cls($str);
		$q = bobs_qm()->query($db, 'urcherche2.1', self::sql_recherche_utilisateur, [$str,$str]);
		$resultat = [];
		while ($r = self::fetch($q)) {
			$resultat[] = get_utilisateur($db, $r);
		}
		return $resultat;
	}

	public static function rechercher_import($db, $str) {
		self::cls($str);
		if (preg_match('/.*\((.*)\)/',$str,$m)) {
			$str = str_replace("({$m[1]})", "", $str);
		}
		$str = str_replace(
			["(",")","'"],
			[" "," "," "],
			$str
		);
		$q = bobs_qm()->query($db,'urechercher2', 'select * from bob_recherche_import_observateur_nom($1)', array($str));
		$resultat = [];
		while ($r = self::fetch($q)) {
			$resultat[] = get_utilisateur($db, $r);
		}
		return $resultat;
	}


	const sql_obs_proche = 'select utilisateur.id_utilisateur,nom,prenom,count(id_observation) from observations_observateurs,utilisateur where observations_observateurs.id_observation in (select id_observation from observations_observateurs where id_utilisateur=$1) and observations_observateurs.id_utilisateur=utilisateur.id_utilisateur and utilisateur.id_utilisateur!=$2 group by utilisateur.id_utilisateur,nom,prenom having count(id_observation) > 2 order by count(id_observation) desc';

	public function observateurs_proche() {
		$q = bobs_qm()->query($this->db, 'u_observ_prox', self::sql_obs_proche, [$this->id_utilisateur, $this->id_utilisateur]);
		return self::fetch_all($q);
	}

	/**
	 * @brief liste les utilisateurs en diffusion restreinte
	 * @param ressource $db
	 * @return array
	 */
	public static function restreint($db) {
		$q = self::query($db,
				'select * from utilisateur where diffusion_restreinte=true '.
				'order by nom,prenom');
		$resultat = [];
		while ($r = self::fetch($q)) {
			$resultat[] = get_utilisateur($db, $r);
		}
		return $resultat;
	}

	/**
	 * @brief liste les utilisateurs ayant signé le réglement intérieur
	 * @param ressource $db
	 * @return array
	 */
	public static function liste_reglement_ok($db) {
		$sql = 'select * from utilisateur where reglement_date_sig is not null order by nom,prenom';
		return self::fetchAllAsUtilisateur(
			bobs_qm()->query($db, 'ul_reglmt_ok', $sql, [])
		);
	}

	private static function fetchAllAsUtilisateur($q) {
		$tr = [];
		while ($r = self::fetch($q)) {
			$tr[] = get_utilisateur(get_db(), $r);
		}
		return $tr;
	}

	static public function derniers_comptes($db, $limite=30) {
		self::cli($limite);
		$sql = 'select * from utilisateur order by id_utilisateur desc limit $1';
		$q = bobs_qm()->query($db, 'ul_derniers', $sql, [$limite]);
		return $this->fetchAllAsUtilisateur($q);
	}

	const sql_select_uname = 'select * from utilisateur where username=$1';

	/**
	 * @brief retourne une instance utilisateur en fonction du login
	 * @return clicnat_utilisateur
	 */
	static public function par_identifiant($db, $login) {
		self::cls($login, self::except_si_vide);

		$q = bobs_qm()->query($db, 'u_by_login', self::sql_select_uname, [$login]);
		$r = self::fetch($q);

		if (!$r) {
			bobs_log('pas trouvé '.$login);
			return false;
		}

		return get_utilisateur($db, $r);
	}

	const sql_by_mail = 'select * from utilisateur where mail=$1';

	/**
	 * @brief retourne une instance utilisateur en fonction du mail
	 * @return bobs_utilisateur
	 */
	static public function by_mail($db, $mail) {
		if (!is_resource($db)) {
			throw new \Exception('$db est pas une ressource');
		}
		self::cls($mail, self::except_si_vide);

		$q = bobs_qm()->query($db, 'u_by_mail',self::sql_by_mail, [$mail]);
		$r = self::fetch($q);

		if (!$r) {
			throw new clicnat_exception_pas_trouve("pas de compte a cette adresse");
		}
		return get_utilisateur($db, $r);
	}

	const sql_by_id_tiers = 'select utilisateur.* from utilisateur,referentiel_utilisateur_tiers where referentiel_utilisateur_tiers.id_utilisateur=utilisateur.id_utilisateur and tiers=$1 and id_tiers=$2';

	/**
	 * @brief retourne une instance utilisateur en fonction de l'identifiant d'une structure tiers
	 * @return bobs_utilisateur
	 */
	 # tiers : structure tiers id : id tiers
	static public function by_id_tiers($db, $tiers, $id) {
		#except_si_inf_1 from tests.php
		self::cli($id, self::except_si_inf_1);
		self::cls($tiers, self::except_si_vide);

		#bobs_qm dans element.php
		$q = bobs_qm()->query($db, 'u_by_id_tiers', self::sql_by_id_tiers, array($tiers, $id));
		#cherche le résultat contenu dans $q
		$r = self::fetch($q);

		if (empty($r)) {
			return false;
		}

		#retourne un id
		return get_utilisateur($db, $r);
	}

	/**
	 * @brief retourne la liste de tous les utilisateurs
	 * @return un tableau d'objet bobs_utilisateurs
	 */
	static public function tous($db) {
		$q = self::query($db, 'select * from utilisateur order by nom,prenom');
		return $this->fetchAllAsUtilisateur($q);
	}

	const sql_update_last_login = 'update utilisateur set last_login=now() where id_utilisateur=$1';

	public function crypte_mot_de_passe($mdp) {
		return hash('sha256', $mdp.'§'.$this->id_utilisateur);
	}


	/**
	 * @brief Verification du mot de passe de l'utilisateur
	 * @param $pwd le mot de passe de l'utilisateur
	 * @return boolean
	 */
	public function verifier_mot_de_passe($pwd) {
		self::cls($pwd);

		$pwd = self::crypte_mot_de_passe($pwd);

		if ($this->password == $pwd) {
			bobs_qm()->query($this->db, 'last_login', self::sql_update_last_login, array($this->id_utilisateur));
			return true;
		}
		bobs_log("login failed {$this->id_utilisateur} db:{$this->password} arg:{$pwd}");
		sleep(2);
		return false;
	}

	public function acces_poste_ok() {
		return $this->acces_poste;
	}

	public function acces_qg_ok() {
		return $this->acces_qg;
	}

	public function acces_chiros_ok() {
		return $this->acces_chiros;
	}

	public static function liste_qg($db) {
		$sql = "select * from utilisateur where acces_qg=true order by nom,prenom";
		return self::query_fetch_all($db, $sql);
	}

	public static function liste_chiro($db) {
		$sql = 'select * from utilisateur where acces_chiros=true order by nom,prenom';
		return self::query_fetch_all($db, $sql);
	}

	/**
	 * @brief Retourne le nombre d'observations de l'utilisateur
	 * @return integer
	 */
	public function get_nb_observations($brouillard=false) {
		if (!empty($this->nb_observations))
			return $this->nb_observations;
		if (!$brouillard) {
			$q = bobs_qm()->query($this->db, 'util_nb_obs','
				select count(*) as n
				from observations_observateurs oo, observations o
				where oo.id_observation=o.id_observation
				and oo.id_utilisateur=$1',
				array(
					$this->id_utilisateur
				));
		} else {
			$q = bobs_qm()->query($this->db, 'util_nb_obs_b','
				select count(*) as n
				from observations o
				where o.id_utilisateur=$1
				and o.brouillard=true',
				array(
					$this->id_utilisateur
				));
		}
		$r = self::fetch($q);
		return $r['n'];
	}

	public function stats() {
		return [
			"n_citations_attente" => $this->get_n_citations_en_attente(),
			"n_citations_auteur" => $this->get_n_citations_auteur(),
			"n_citations_observateur" => $this->get_n_citations_observateur(),
			"premiere_obs" => $this->get_premiere_date_obs(),
			"derniere_obs" => $this->get_derniere_date_obs(),
			"n_especes_vues" => count($this->get_especes_vues()),
			"n_selections" => $this->selections_n(),
			"n_liste_espaces" => count($this->listes_espaces()),
			"n_liste_especes" => count($this->listes_especes()),
			"n_fichiers" => $this->fichiers()->count()
		];

	}
	const sql_nb_observateur = 'select count(distinct id_citation)
		from citations c,observations o,observations_observateurs oo
		where c.id_observation=o.id_observation and  oo.id_utilisateur=$1 and oo.id_observation=o.id_observation';
	const sql_nb_saisie = 'select count(distinct id_citation)
	       from citations c,observations o
	       where c.id_observation=o.id_observation and o.id_utilisateur=$1';
	public function get_n_citations_auteur() {
		$q = bobs_qm()->query($this->db, 'u_n_auteur', self::sql_nb_saisie, [$this->id_utilisateur]);
		$r = self::fetch($q);
		return (int)$r['count'];
	}
	public function get_n_citations_observateur() {
		$q = bobs_qm()->query($this->db, 'u_n_observ', self::sql_nb_observateur, [$this->id_utilisateur]);
		$r = self::fetch($q);
		return (int)$r['count'];
	}

	const sql_nb_saisie_attente = 'select count(*) from citations c,citations_tags ct,observations o
		where o.id_utilisateur=$1 and o.id_observation=c.id_observation and ct.id_citation=c.id_citation
		and brouillard=false and ct.id_tag=579';


	/**
	 * @brief Retourne le nombre d'observations saisie non validées
	 * @return integer
	 */
	public function get_n_citations_en_attente() {
		$q = bobs_qm()->query($this->db, 'u_n_en_attente', self::sql_nb_saisie_attente, array($this->id_utilisateur));
		$r = self::fetch($q);
		return (int)$r['count'];
	}

	/**
	 * @brief Retourne le timestamp d'une date d'observation
	 * @param 'min' pour la premiere 'max' pour la derniere
	 * @return le timestamp de la date
	 */
	private function get_date_obs($min_ou_max) {
		$sql = sprintf("select %s(date_observation) as d
				from observations,observations_observateurs
				where observations_observateurs.id_utilisateur=%d
				and observations_observateurs.id_observation=observations.id_observation",
				$min_ou_max, $this->id_utilisateur);
		$q = self::query($this->db, $sql);
		$r = self::fetch($q);
		return strtotime($r['d']);
	}

	/**
	 * @brief Retourne la date de la permiere observation
	 * @return le timestamp de la date
	 */
	public function get_premiere_date_obs() {
		if (empty($this->date_premiere_obs))
			$this->date_premiere_obs = $this->get_date_obs('min');
		return $this->date_premiere_obs;
	}

	/**
	 * @brief Retourne la date de la derniere observation
	 * @return le timestamp de la date
	 */
	public function get_derniere_date_obs() {
		if (empty($this->date_derniere_obs))
			$this->date_derniere_obs = $this->get_date_obs('max');
		return $this->date_derniere_obs;
	}

	/**
	 * @brief Liste des especes vues
	 * @return un tableau des especes vues
	 */
	public function get_especes_vues() {
		if (!empty($this->especes_vues))
			return $this->especes_vues;

		$this->especes_vues = array();

		$sql = sprintf("select distinct es.*
				from
					especes es,
					citations ci,
					observations os,
					observations_observateurs oo
				where oo.id_utilisateur=%d
				and oo.id_observation=os.id_observation
				and ci.id_espece = es.id_espece
				and os.id_observation=ci.id_observation
				order by es.nom_f",
				$this->id_utilisateur);
		$q = self::query($this->db, $sql);
		while ($r = self::fetch($q))
			$this->especes_vues[] = $r;

		$this->nb_especes_vues = count($this->especes_vues);

		return $this->especes_vues;
	}

	/**
	 * @brief Définit comme un nouvel observateur
	 * @param $vrai_faux boolean
	 * @return vrai si l'opération a fonctionné
	 */
	public function set_junior($vrai_faux) {
		$f = BOBS_JUNIOR_PATH.'/'.$this->id_utilisateur;
		if ($vrai_faux)
			return file_put_contents($f,1) !== false;
		else
			if (file_exists($f))
				return unlink($f);
		return false;
	}

	/**
	 * @brief Détermine si l'observateur est un nouveau
	 * @return boolean vrai si junior
	 */
	public function junior() {
		return file_exists(BOBS_JUNIOR_PATH.'/'.$this->id_utilisateur);
	}

	/**
	 * @brief Liste les nouveaux observateurs
	 * @return iterateur d'objets bobs_utilisateur
	 */
	public static function juniors($db) {
		$t = array();
		foreach (glob(BOBS_JUNIOR_PATH.'/*') as $path)
			$t[] = basename($path);
		return new clicnat_iterateur_utilisateurs($db, $t);
	}

	public function set_id_gdtc($id) {
		self::cli($id);
		if (empty($id)) {
			$this->id_gdtc = null;
			$sql = "update utilisateur
					set id_gdtc=null
					where id_utilisateur=$1";
			return bobs_qm()->query($this->db, 'set_id_gdtc_null', $sql, array($this->id_utilisateur));
		}
		$sql = "update utilisateur
				set id_gdtc=$2
				where id_utilisateur=$1";
		$this->id_gdtc = $id;
		return bobs_qm()->query($this->db, 'set_id_gdtc', $sql, array($this->id_utilisateur, $this->id_gdtc));
	}

	const sql_mad_vide = 'delete from utilisateur_citations_ok where id_utilisateur=$1';

	/**
	 * @brief Remise à zéro de la MAD de l'utilisateur
	 */
	public function mise_a_dispo_vide() {
		return bobs_qm()->query($this->db, 'u_mad_vide', self::sql_mad_vide, array($this->id_utilisateur));
	}


	const sql_mad_position = 'select coalesce(max(id_citation),0) as position from utilisateur_citations_ok where id_utilisateur=$1';
	const sql_mad_insert  = 'insert into utilisateur_citations_ok (id_utilisateur,id_citation)';
	const sql_select_own = 'select distinct id_utilisateur,id_citation from observations_observateurs,citations
					where observations_observateurs.id_utilisateur = $1
					and observations_observateurs.id_observation=citations.id_observation
					and id_citation>$2';
	const sql_select_auteur = 'select distinct id_utilisateur,id_citation from citations,observations
					where citations.id_observation=observations.id_observation
					and id_utilisateur=$1
					and id_citation>$2
					and not exists(
						select 1 from utilisateur_citations_ok
						where utilisateur_citations_ok.id_citation=citations.id_citation
						and utilisateur_citations_ok.id_utilisateur=observations.id_utilisateur
					)';
	const sql_select_selection = 'select distinct selection.id_utilisateur,id_citation
					from selection, selection_data
					where selection.id_selection=selection_data.id_selection
					and selection.id_utilisateur=$1
					and id_citation>$2
					and not exists(
						select 1 from utilisateur_citations_ok
						where utilisateur_citations_ok.id_citation=selection_data.id_citation
						and utilisateur_citations_ok.id_utilisateur=selection.id_utilisateur
					)';
	public function mise_a_disposition() {
		$q = bobs_qm()->query($this->db, 'u_mad_pos', self::sql_mad_position, array($this->id_utilisateur));
		$r = self::fetch($q);
		$position = $r['position'];
		self::query($this->db, 'begin');
		try {
			$q = bobs_qm()->query($this->db, 'u_mad_s_own', self::sql_mad_insert.' '.self::sql_select_own, array($this->id_utilisateur, $position));
			$q = bobs_qm()->query($this->db, 'u_mad_s_aut', self::sql_mad_insert.' '.self::sql_select_auteur, array($this->id_utilisateur, $position));
			$q = bobs_qm()->query($this->db, 'u_mad_s_sel', self::sql_mad_insert.' '.self::sql_select_selection, array($this->id_utilisateur, $position));
			$this->mise_a_dispo_autre($position);
		} catch (Exception $e) {
			self::query($this->db, 'rollback');
			throw $e;
		}
		self::query($this->db, 'commit');
	}


	private function mise_a_dispo_autre($position) {
		// Accès aux données chiros pas visible depuis le QG
		if ($this->acces_qg_ok()) {
			$extraction = new bobs_extractions($this->db);
			$extraction->ajouter_condition(new bobs_ext_c_ordre('Chiroptères'));
			$extraction->ajouter_condition(new bobs_ext_c_ordre('chiroptères'));
			$extraction->autorise_utilisateur($this->id_utilisateur, $position);
		}

		foreach (bobs_chr::get_list($this->db) as $t_chr) {
			$chr = get_chr($this->db, $t_chr);
			$membre = false;
			foreach($chr->get_members() as $membre_chr) {
				if ($membre_chr->id_utilisateur == $this->id_utilisateur) {
					$membre = true;
					break;
				}
			}
			if ($membre) {
				$extraction = new bobs_extractions($this->db);
				$extraction->ajouter_condition(new bobs_ext_c_interval_date('2008-01-01', '2030-12-31'));
				$extraction->ajouter_condition(new bobs_ext_c_esp_comite_homolog($t_chr['id_chr']));
				$extraction->autorise_utilisateur($this->id_utilisateur, $position);
			}
		}
	}

	/**
	 * @brief retourne le nombre d'observations par mois pour une année
	 * retourne un tableau avec les clés suivantes :
	 *   - mois
	 *   - n
	 * @param int $annee
	 * @return array
	 */
	public function get_n_obs_par_mois($annee) {
	    self::cli($annee);
	    $sql = 'select count(distinct observations.id_observation) as n,
			date_part(\'month\', date_observation) as mois
		    from observations_observateurs,observations
		    where observations_observateurs.id_utilisateur=$1
		    and observations_observateurs.id_observation=observations.id_observation
		    and date_part(\'year\', date_observation) = $2
		    group by date_part(\'month\', date_observation)
		    order by date_part(\'month\', date_observation)';
	    $q = bobs_qm()->query($this->db, 'utl_obs_p_m', $sql, array($this->id_utilisateur, $annee));
	    return self::fetch_all($q);
	}

	public function get_n_obs_par_annee() {
	    $sql = 'select count(distinct observations.id_observation) as n,
			date_part(\'year\', date_observation) as annee
		    from observations_observateurs,observations
		    where observations_observateurs.id_utilisateur=$1
		    and observations_observateurs.id_observation=observations.id_observation
		    group by date_part(\'year\', date_observation)
		    order by date_part(\'year\', date_observation)
		    ';
	    $q = bobs_qm()->query($this->db, 'utl_obs_p_y', $sql, array($this->id_utilisateur));
	    return self::fetch_all($q);
	}

	const sql_citation_authok = 'select bob_citation_ok($1,$2)'; // $1=id_utilisateur,$2=id_citation

	/**
	 * @brief autorise un utilisateur a accéder à une citation
	 *
	 * @param $id_citation citation id
	 * @return db query result
	 */
	public function add_citation_authok($id_citation) {
	    return bobs_qm()->query($this->db, 'add_1_citation_auth_ok',self::sql_citation_authok ,[$this->id_utilisateur,$id_citation]);
	}

	/**
	 * @brief Nombre de citations que l'utilisateur peut voir
	 * @return un entier
	 */
	public function get_nb_citations_authok() {
		if (!empty($this->nb_citations_authok))
			return $this->nb_citations_authok;

		$sql = sprintf(
			"select count(*) as n from utilisateur_citations_ok where id_utilisateur=%d",
			$this->id_utilisateur
		);
		$q = self::query($this->db, $sql);
		$r = self::fetch($q);
		$this->nb_citations_authok = $r['n'];
		return $this->nb_citations_authok;
	}

	const sql_select_citations_ok = 'select * from citations where citations.id_citation=$1
			and exists(
				select 1 from utilisateur_citations_ok
				where id_utilisateur=$2
				and utilisateur_citations_ok.id_citation=citations.id_citation
			)';

	public function get_observation_authok($observation) {
		$n_ok = 0;
		try {
			foreach ($observation->get_citations() as $citation) {
				if ($this->get_citation_authok($citation->id_citation)) {
					$n_ok++;
				}
			}
		} catch (Exception $e) {
		}
		if ($n_ok > 0) {
			return $observation;
		}
		return false;
	}

	/**
	 * @brief obtenir une citation que l'utilisateur peut voir
	 * @param int $id_citation
	 * @return bobs_citation
	 */
	public function get_citation_authok($id_citation) {
		if ($this->acces_qg)
			return get_citation($this->db, (int)$id_citation);

		$q = bobs_qm()->query($this->db, 'u_citation_auth_ok', self::sql_select_citations_ok, array(
			(int)$id_citation,
			(int)$this->id_utilisateur
		));
		$r = self::fetch($q);

		if (!is_array($r)) {
			$citation = get_citation($this->db, (int)$id_citation);
			if ($citation->id_utilisateur == $this->id_utilisateur) {
				$this->add_citation_authok($citation->id_citation);
				return $citation;
			} else {
				$observateurs = $citation->get_observation()->get_observateurs();
				foreach ($observateurs as $observateur) {
					if ($observateur['id_utilisateur'] == $this->id_utilisateur) {
						$this->add_citation_authok($citation->id_citation);
						return $citation;
					}
				}
			}
			throw new Exception('id_citation pas trouvé ou inaccessible (1)');
		}

		if (!array_key_exists('id_citation', $r))
			throw new Exception('id_citation pas trouvé ou inaccessible (2)');

		return get_citation($this->db, $r);
	}

	/**
	 * @brief retourne les observations de l'utilisateur
	 */
	public function get_observations_authok() {
		$observations = array();
		$sql = sprintf("select * from observations
				where id_observation in (
					select id_observation
					from citations
					where exists(select 1 from utilisateur_citations_ok
							where utilisateur_citations_ok.id_utilisateur=%d
							and utilisateur_citations_ok.id_citation=citations.id_citation)
					group by id_observation
				) order by date_observation desc",
				$this->id_utilisateur);
		$q = self::query($this->db, $sql);


		while ($r = self::fetch($q)) {
			$observations[$r['id_observation']] = new bobs_observation($this->db, $r);
		}
		return $observations;
	}

	/**
	 * @brief créer une nouvelle sélection pour l'utilisateur
	 * le numéro de la nouvelle sélection sera renvoyé
	 * @see bobs_selection::nouvelle
	 * @return int
	 */
	public function selection_creer($nom) {
		return bobs_selection::nouvelle($this->db, $this->id_utilisateur, $nom);
	}

	/**
	 * @brief retourne la liste des sélections
	 * @param $tri la colonne de tri
	 * @see bobs_selection::liste
	 */
	public function selections($tri = 'id_selection') {
	    $r = array();
	    $t = bobs_selection::liste($this->db, $this->id_utilisateur, $tri);
	    foreach ($t as $s) {
			$r[] = new bobs_selection($this->db, $s);
	    }
	    return $r;
	}

	public function listes_especes() {
		$r = [];
		$t = clicnat_listes_especes::liste($this->db, $this->id_utilisateur);
		foreach ($t as $l) {
			$r[] = new clicnat_listes_especes($this->db, $l);
		}
		return $r;
	}

	public function listes_espaces() {
		$r = [];
		$t = clicnat_listes_espaces::liste($this->db, $this->id_utilisateur);
		foreach ($t as $l) {
			$r[] = new clicnat_listes_espaces($this->db, $l);
		}
		return $r;

	}

	public function selections_n() {
		return count($this->selections());
	}

	/**
	 * @brief ajouter les citations accessible a l'utilisateur pour une espèce dans une selection
	 * @param $id_espece le numéro de l'espèce
	 * @param $id_selection le numéro de la sélection
	 */
	public function selection_ajouter_espece($id_espece, $id_selection) {
		self::cli($id_espece, self::except_si_inf_1);
		self::cli($id_selection, self::except_si_inf_1);

		$sql = sprintf("insert into selection_data (id_selection,id_citation)
				select distinct %d,citations.id_citation from citations, utilisateur_citations_ok
				where citations.id_espece=%d
				and utilisateur_citations_ok.id_citation=citations.id_citation",
				$id_selection, $id_espece);

		return self::query($this->db, $sql);
	}

	/**
	 * @brief recupére une sélection de l'utilisateur
	 * @param $id_selection le numéro de la sélection
	 * @return un objet bobs_selection
	 */
	public function selection_get($id_selection) {
		$id_selection = sprintf("%d", $id_selection);

		if (empty($id_selection)) {
			throw new InvalidArgumentException();
		}
		$selection = new bobs_selection($this->db, sprintf("%s", $id_selection));
		if ($selection->get_id_utilisateur() != $this->id_utilisateur) {
			throw new exception('pas propriétaire de la sélection');
		}

		return $selection;
	}


	const sql_snew_trash = 'select * from selection where nom_selection=$1 and id_utilisateur=$2';


	/**
	 * @brief Création d'une nouvelle sélection ou vide une existante à ce nom
	 *
	 * retourne le numéro de la sélection
	 *
	 * (utilisé pour la mise à disposition des données des carrés atlas)
	 *
	 * @param str $nom
	 * @return int
	 */
	public function selection_cree_ou_vide($nom) {
		self::cls($nom, self::except_si_vide);

		$q = bobs_qm()->query($this->db, 'u_snew_trash', self::sql_snew_trash, array($nom, $this->id_utilisateur));
		$r = self::fetch($q);

		if (empty($r['id_selection'])) {
			return $this->selection_creer($nom);
		} else {
			$selection = new bobs_selection($this->db, $r['id_selection']);
			$selection->vider();
			return $selection->id_selection;
		}
	}

	/**
	 * @deprecated
	 */
	public function migration_observations_owned() {
		if ($this->virtuel)
			throw new Exception('Ne fonctionne pas sur les utilisateurs virtuel');

		$sql = sprintf("insert into observations_observateurs (id_observation,id_utilisateur)
					select o.id_observation, o.id_utilisateur
					from observations o
					where o.id_utilisateur=%d
					and o.id_observation not in (
						select id_observation
						from observations_observateurs
						where id_utilisateur=%d
					)",
				$this->id_utilisateur, $this->id_utilisateur);
		self::query($this->db, $sql);
		$sql = sprintf("update observations set id_utilisateur = null from observations_observateurs
			where observations_observateurs.id_observation=observations.id_observation and observations.id_utilisateur=%d",
			$this->id_utilisateur);
		self::query($this->db, $sql);
	}

	/**
	 * @deprecated
	 */
	public function migration_observation_dispatch() {
		if (!$this->virtuel)
			throw new Exception('Ne fonctionne que sur les utilisateurs virtuel');
		$sql = sprintf("select * from utilisateur where %d = any(associations)", $this->id_utilisateur);
		$q = self::query($this->db, $sql);
		while ($r = self::fetch($q)) {
			$sql = sprintf("insert into observations_observateurs (id_observation,id_utilisateur)
				select id_observation,%d
				from observations o
				where o.id_utilisateur = %d
				and o.id_observation not in (
					select id_observation
					from observations_observateurs
					where id_utilisateur=%d)",
				$r['id_utilisateur'], $this->id_utilisateur, $r['id_utilisateur']);
			self::query($this->db, $sql);
			$sql = sprintf("insert into observations_observateurs (id_observation,id_utilisateur)
				select oo.id_observation, %d
				from observations_observateurs oo
				where oo.id_utilisateur = %d
				and oo.id_observation not in (
					select id_observation
					from observations_observateurs
					where id_utilisateur = %d)",
				$r['id_utilisateur'], $this->id_utilisateur, $r['id_utilisateur']);
			self::query($this->db, $sql);
		}
		$sql = sprintf("update observations set id_utilisateur = null from observations_observateurs
			where observations_observateurs.id_observation=observations.id_observation and observations.id_utilisateur=%d",
			$this->id_utilisateur);
		self::query($this->db, $sql);
		$sql = sprintf("delete from observations_observateurs where id_utilisateur=%d", $this->id_utilisateur);
		self::query($this->db, $sql);
		$sql = sprintf("delete from utilisateur where id_utilisateur=%d", $this->id_utilisateur);
		self::query($this->db, $sql);
	}

	const sql_select_c_atlas = 'select id_espace from espace_l93_10x10 where nom=$1';
	const sql_insert_c_atlas = 'insert into utilisateur_espace_l93_10x10 (id_utilisateur, id_espace, decideur_aonfm) values ($1, $2, $3)';
	const sql_delete_c_atlas = 'delete from utilisateur_espace_l93_10x10 where id_utilisateur=$1 and id_espace=$2';

	/**
	 * @brief ajoute un carré atlas à l'observateur
	 */
	public function ajoute_carre_atlas($nom_zone, $resp_aonfm=false) {
		self::cls($nom_zone, self::except_si_vide);

		$q = bobs_qm()->query($this->db, 'utilisateur_sel_c_atlas', self::sql_select_c_atlas, array($nom_zone));
		$r = self::fetch($q);

		if (empty($r['id_espace']))
			throw new Exception('Carré atlas inconnu '.$nom_zone);

		$this->l93_10x10_id_espace = $r['id_espace'];

		return bobs_qm()->query($this->db, 'utilisateur_set_c_atlas', self::sql_insert_c_atlas, array($this->id_utilisateur, $r['id_espace'],$resp_aonfm?'true':'false'));
	}

	/**
	 * @brief enlève un carré atlas à l'observateur
	 */
	public function supprime_carre_atlas($id_espace) {
		self::cli($id_espace, self::except_si_inf_1);

		return bobs_qm()->query($this->db, 'utilisateur_del_c_atlas', self::sql_delete_c_atlas, array($this->id_utilisateur, $id_espace));
	}

	public function liste_carre_atlas() {
		$sql = 'select espace_l93_10x10.* ,utilisateur_espace_l93_10x10.decideur_aonfm
			from espace_l93_10x10,utilisateur_espace_l93_10x10
			where utilisateur_espace_l93_10x10.id_utilisateur=$1
			and espace_l93_10x10.id_espace=utilisateur_espace_l93_10x10.id_espace';
		$q = bobs_qm()->query($this->db, 'utilisateur_sel_atlas', $sql, array($this->id_utilisateur));
		return self::fetch_all($q);
	}

	public function liste_observations_brouillard() {
		$sql = 'select id_observation,id_utilisateur,to_char(date_observation,\'dd-mm-yyyy\') as date_observation
			from observations where id_utilisateur=$1 and brouillard=true';
		$qm = bobs_qm();
		$q = $qm->query($this->db, 'utilisateur_sel_obs_brou', $sql, array($this->id_utilisateur));
		return self::fetch_all($q);
	}

	public function observation_brouillard($id_observation) {
		self::cli($id_observation);
		$obs = new bobs_observation($this->db, $id_observation);

		if ($this->id_utilisateur == $obs->id_utilisateur && $obs->brouillard)
			return $obs;
		else
			throw new Exception('pas dans le brouillard ou pas auteur');
		return false;
	}

	public function citation_brouillard($id_citation) {
		self::cli($id_citation);
		$cit = new bobs_citation($this->db, $id_citation);
		$obs = $this->observation_brouillard($cit->id_observation);
		if (!$obs)
			throw new Exception('pas dans le brouillard ou pas auteur');
		return $cit;
	}

	public function accept_rules($diffusion_restreinte) {
		self::cli($this->id_utilisateur);
		$this->reglement_date_sig = strftime('%Y-%m-%d %T', mktime());
		$this->diffusion_restreinte = $diffusion_restreinte == 1;
		bobs_qm()->query($this->db, 'utl_accept_rules',
			'update utilisateur set reglement_date_sig=now(), diffusion_restreinte=$2 where id_utilisateur=$1',
			array($this->id_utilisateur, $diffusion_restreinte==1?'true':'false'));
	}

	public function agreed_the_rules() {
		self::cli($this->id_utilisateur);
		return !is_null($this->reglement_date_sig);
	}

	public function session_var_save($var, $value) {
	    $_SESSION[md5($var.$this->id_utilisateur)] = $value;
	}

	public function session_var_get($var) {
		if (array_key_exists(md5($var.$this->id_utilisateur), $_SESSION))
	    		return $_SESSION[md5($var.$this->id_utilisateur)];
		return null;
	}

	public function get_imports() {
	    $sql = 'select * from imports where id_utilisateur=$1';
	    $q = bobs_qm()->query($this->db, 'utl_l_import', $sql, array($this->id_utilisateur));
	    return self::fetch_all($q);
	}

	const sql_set_pwd  = 'update utilisateur set password=$2 where id_utilisateur=$1';

	/**
	 * @brief Changer le mot de passe
	 * @param string $newpwd
	 * @return handle le résultat de la requête
	 */
	public function set_password($newpwd) {
		self::cls($newpwd);
		if (strlen($newpwd) > 5) {
			$newpwd = $this->crypte_mot_de_passe($newpwd);
			bobs_log("set pwd {$newpwd} pour {$this->id_utilisateur}");
			$q = bobs_qm()->query($this->db, 'utl_set_pwd', self::sql_set_pwd, array($this->id_utilisateur, $newpwd));
			if (!$q) throw new Exception('problème mise à jour du mot de passe');
			return true;
		} else {
			throw new InvalidArgumentException('mot de passe trop court');
		}
		throw new Exception('mot passe pas changé');
	}

	/**
	 * @brief Changer l'adresse mail
	 * @param string $mail
	 * @return handle le résultat de la requête
	 */
	public function set_mail($mail) {
		self::cls($mail);

		if (strpos($mail, '@') <= 0)
			throw new InvalidArgumentException('pas de @');

		if (!empty($mail)) {
			$this->update_field('mail', $mail);
		}
		return false;
	}


	/**
	 * @brief Envoi demande d'un nouveau mot de passe
	 * @param $base_url début de l'url pour le lien confirmation
	 * @param $mail_support adresse d'expéditeur
	 */
	public function envoi_mail_confirmation_demande($base_url, $mail_support, $signature) {
		$vars = [
			"nom" => $this->nom,
			"prenom" => $this->prenom,
			"base_url" => $base_url,
			"mail_support" => $mail_support,
			"mail" => $this->mail
		];
		$vars['ticket'] = hash('sha1', sprintf("%d%d%d%s",mktime(),rand(),$this->id_utilisateur,$this->mail));
		$this->update_field('ticket_mot_de_passe', $vars['ticket']);

		$headers = "From: $mail_support\r\nContent-Type: text/plain; charset=utf-8\r\n\r\n";
		$sujet_tpl = clicnat_textes::par_nom($this->db, "base/inscription/mail_demande_mdp_sujet")->texte;
		$msg_tpl = clicnat_textes::par_nom($this->db, "base/inscription/mail_demande_mdp")->texte;

		$sujet = self::mini_template($sujet_tpl, $vars);
		$msg = self::mini_template($msg_tpl, $vars);

		if (!mail($this->mail, $sujet, $msg, $headers, "-f$mail_support")) {
			throw new Exception('Message pas envoyé');
	    	}

		return true;
	}

	/**
	 * @brief création et envoi du mot de passe a l'utilisateur
	 * @param $base_url url de base pour les liens
	 * @param $mail_support adresse de réponse au message
	 * @param $signature signature texte du mail
	 * @deprecated
	 */
	public function send_password($base_url, $mail_support, $signature) {
		return $this->envoi_mot_de_passe($base_url, $mail_support, $signature);
	}

	/**
	 * @brief création et envoi du mot de passe a l'utilisateur
	 * @param $base_url url de base pour les liens
	 * @param $mail_support adresse de réponse au message
	 * @param $signature signature texte du mail
	 */
	public function envoi_mot_de_passe($base_url, $mail_support, $signature) {
		$pwgen = new \PWGen();
		$vars = [
			"username" => $this->username,
			"nom" => $this->nom,
			"prenom" => $this->prenom,
			"base_url" => $base_url,
			"mot_de_passe" => $pwgen->generate()
		];

		$this->set_password($vars['mot_de_passe']);


		if (empty($vars['mot_de_passe']) || empty($this->username)) {
			throw new \Exception('Identifiant ou mot de passe vide');
		}

		$headers = "From: $mail_support\r\nContent-Type: text/plain; charset=utf-8\r\n\r\n";
		$msg_tpl = clicnat_textes::par_nom($this->db, 'base/inscription/mail_mdp')->texte;
		$sujet_tpl = clicnat_textes::par_nom($this->db, 'base/inscription/mail_mdp_sujet')->texte;

		$sujet = self::mini_template($sujet_tpl, $vars);
		$msg = self::mini_template($msg_tpl, $vars);

		if (!mail($this->mail, $sujet, $msg, $headers, "-f$mail_support")) {
			throw new \Exception('Message pas envoyé');
		}

		return true;
	}

	/**
	 * @brief liste les observateurs avec quelques élements statistiques
	 */
	public static function liste_observateurs_stats() {
	    $sql = "select nom,prenom,count(distinct o.id_observation) as n_obs,
			max(date_part('year', o.date_observation)) as derniere_annee
		    from observations_observateurs oo, utilisateur u,observations o
		    where oo.id_utilisateur=u.id_utilisateur
		    and o.id_observation=oo.id_observation
		    group by nom,prenom";
	    $q = bobs_qm()->query($this->db, 'l_obs_stats', $sql, array());
	    return self::fetch_all($q);
	}

	public function structure() {
		switch ($this->id_utilisateur) {
			case 2033:
			case 849:
				return 'dreal';
			case 2093:
				return 'cenp';
		}
		return false;
	}

	/**
	 * @brief retourne le nombre d'observateurs dans la base
	 * @param ressource $db
	 * @param integer $depuis_annee de Y a aujourd'hui
	 * @return integer
	 */
	public static function nombre_observateurs($db, $depuis_annee=false) {
		$sql = "
			select count(distinct observations_observateurs.id_utilisateur) as n
			from observations,observations_observateurs
			where observations.id_observation=observations_observateurs.id_observation
			";
		if ($depuis_annee) {
			$sql .= sprintf(" and extract('year' from date_observation) >= %04d", $depuis_annee);
		}
		$q = self::query($db, $sql);
		$r = self::fetch($q);
		return $r['n'];
	}

	/**
	 * @brief ajoute un objet au répertoire de l'utilisateur
	 * @param string $table_espace
	 * @param integer $id_espace
	 */
	public function repertoire_ajoute($table_espace, $id_espace) {
		// TODO vérifier que l'observateur a créé l'espace
		return bobs_utilisateur_repertoire::insert($this->db, $this->id_utilisateur, $table_espace, $id_espace);
	}

	public function repertoire_supprime($table_espace, $id_espace) {
		return bobs_utilisateur_repertoire::supprime($this->db, $this->id_utilisateur, $table_espace, $id_espace);
	}

	public function repertoire_ajoute_polygone($nom, $wkt) {
		$data = array(
			'id_utilisateur' => $this->id_utilisateur,
			'reference' => '',
			'nom' => $nom,
			'wkt' => $wkt
		);

		$id_espace = bobs_espace_polygon::insert_wkt($this->db, $data);

		if (!$id_espace) {
			throw new Exception('problème pour créer le polygone');
		}

		return $this->repertoire_ajoute('espace_polygon', $id_espace);
	}

	public function repertoire_ajoute_ligne($nom, $wkt) {
		$data = array(
			'id_utilisateur' => $this->id_utilisateur,
			'reference' => '',
			'nom' => $nom,
			'wkt' => $wkt
		);

		$id_espace = bobs_espace_ligne::insert_wkt($this->db, $data);

		if (!$id_espace) {
			throw new Exception('problème pour créer le polygone');
		}

		return $this->repertoire_ajoute('espace_line', $id_espace);
	}


	public function repertoire_ajoute_point($nom, $x, $y) {
		$data = array(
			'id_utilisateur' => $this->id_utilisateur,
			'reference' => '',
			'nom' => $nom,
			'x' => $x,
			'y' => $y
		);
		$id_espace = bobs_espace_point::insert($this->db, $data);
		if (!$id_espace) {
			throw new Exception('problème pour créer le point');
		}
		return $this->repertoire_ajoute('espace_point', $id_espace);
	}

	public function repertoire_liste($tri=bobs_utilisateur_repertoire::tri_par_nom) {
		return bobs_utilisateur_repertoire::liste_utilisateur($this->db, $this->id_utilisateur);
	}

	/**
	 * @brief liste les commentaires sur les obs de cette observateur
	 */
	public function get_obs_commentaires($limite=50) {
		$sql = "select * from (
				select
					id_commentaire, c.id_citation as ele_id, type_commentaire, date_commentaire, cc.commentaire, 'citations_commentaires' as tble
				from citations_commentaires cc, citations c,observations_observateurs oo
				where cc.id_citation=c.id_citation
				and c.id_observation=oo.id_observation
				and oo.id_utilisateur=$1
				union
				select id_commentaire, oc.id_observation as ele_id, type_commentaire, date_commentaire, commentaire, 'observations_commentaires' as tble
				from observations_commentaires oc, observations_observateurs as oo
				where oo.id_observation=oc.id_observation and oo.id_utilisateur=$2) as sq
			order by date_commentaire desc limit $limite";
		$q = bobs_qm()->query($this->db, 'u_ctr_liste_'.$limite, $sql, array($this->id_utilisateur, $this->id_utilisateur));
		$t = self::fetch_all($q);
		/*for ($i=0; $i<count($t); $i++) {
			if ($t[$i]['tble'] == 'citations_commentaires') {
				$citation = get_citation($this->db, $t[$i]['ele_id']);
				$commentaires = $citation->get_commentaires();
				foreach ($commentaires as $comtr) {
					if ($comtr->id_commentaire == $t[$i]['id_commentaire']) {
						$t[$i]['commentaire'] = $comtr->commentaire;
					}
				}
			}
		}*/
		return $t;
	}

	public function get_obs_docs() {
		$sql = "select cd.*,c.id_espece,date_observation ,e.nom_f,e.nom_s
				from
					citations_documents cd ,
					citations c,
					observations_observateurs oo,
					observations o,
					especes e
				where oo.id_utilisateur=$1
				and oo.id_observation=c.id_observation
				and cd.id_citation=c.id_citation
				and o.id_observation=c.id_observation
				and c.id_espece=e.id_espece
				order by date_observation desc";
		$q = bobs_qm()->query($this->db, 'u_docs_liste', $sql, array($this->id_utilisateur));
		return self::fetch_all($q);
	}

	const sql_set_localisation = 'update utilisateur set the_geom=setsrid(geomfromtext($1),4326) where id_utilisateur=$2';

	/**
	 * @brief enregistre la localisation de l'observateur
	 * @param $wkt la geometrie en WGS84
	 */
	public function set_localisation($wkt) {
		self::cls($wkt, self::except_si_vide);
		try {
			return bobs_qm()->query($this->db, 'utl_set_loc', self::sql_set_localisation, array($wkt, $this->id_utilisateur));
		} catch (Exception $e) {
			throw $e;
		}
	}
	const sql_set_date_naissance = 'update utilisateur set date_naissance = $1::Date where id_utilisateur=$2';
	public function set_date_naissance($date_naissance){
		self::cls($date_naissance, self::except_si_vide);
		try{
			$q = bobs_qm()->query($this->db,'utl_set_date_naiss', self::sql_set_date_naissance, array(bobs_element::date_fr2sql($date_naissance), $this->id_utilisateur));
			if ($q) {
				$this->date_naissance = bobs_element::date_fr2sql($date_naissance);
				return true;
			}
		}
		catch (Exception $e) {
			throw $e;
		}
		return false;

	}

	/** @see bobs_utilisateur::get_reseaux */
	private $reseaux_nc;

	const gdtc_sql_adresse = 'select address1,address2,address3,zip_code,city from person where actor_id=:actor_id';

	/**
	 * GDTC database
	 * @return \PDO
	 */
	private static function gdtc_db() {
		static $db;

		if (!isset($db)) {
			$db = new \PDO('mysql:host='.GDTC_MYSQL_HOST.';dbname='.GDTC_MYSQL_DB, GDTC_MYSQL_USER, GDTC_MYSQL_PASSWD);
		}

		return $db;
	}

	public function get_adresse() {
		try {
			$query = $this->gdtc_db()->prepare(self::gdtc_sql_adresse);
			$query->execute([
				":actor_id" => (int)$this->actor_id
			]);
			return $query->fetchAll(\PDO::FETCH_ASSOC);
		} catch (\Exception $e) {
			error_log("gdtc: failed to get actor_id {$this->actor_id}");
			return [];
		}
	}

	private $reseaux_cache;

	public function reseaux() {
		// TODO après avoir déconnecté gdtc retirer le merge
		return array_merge(clicnat2_reseau::liste_reseaux_membre($this->db, $this), $this->reseaux_gdtc());
	}

	const sql_gdtc_reseau_actor = 'select distinct group_id from groupes where actor_id=:actor_id and date_end is null and en_attente=0';

	public function reseaux_gdtc() {
		if (!isset($this->reseaux_cache)) {
			$tr = [];
			$tiers_id_gdtc = $this->ref_tiers('gdtc');

			if ($tiers_id_gdtc)
				$this->id_gdtc = $tiers_id_gdtc;

			if (empty($this->id_gdtc)) {
				return [];
			}

			$query = $this->gdtc_db()->prepare(self::sql_gdtc_reseau_actor);
			$query->execute([
				":actor_id" => (int)$this->actor_id
			]);

			while ($r = $query->fetchAll(\PDO::FETCH_ASSOC)) {
				$nom_reseau = '';
				switch ($r['group_id']) {
					case 5: $nom_reseau = 'cs'; break;
					case 2:	$nom_reseau = 'ar'; break;
					case 3: $nom_reseau = 'sc'; break;
					case 4: $nom_reseau = 'li'; break;
					case 9: $nom_reseau = 'mm'; break;
					case 8: $nom_reseau = 'mt'; break;
					case 6: $nom_reseau = 'ml'; break;
					case 7: $nom_reseau = 'av'; break;
					case 17: $nom_reseau = 'pa'; break;
					case 26: $nom_reseau = 'co'; break;
					case 27: $nom_reseau = 'ae'; break;
					case 30: $nom_reseau = 'sy'; break;
				}
				if (!empty($nom_reseau)) {
					$tr[] = get_bobs_reseau($this->db, $nom_reseau);
					$this->reseaux_nc .= $nom_reseau.',';
				}
			}

			$this->reseaux_nc = trim($this->reseaux_nc, ',');
			$this->reseaux_cache = $tr;
		}
		return $this->reseaux_cache;
	}

	public function membre_reseau($reseau) {
		if (empty($reseau)) {
			throw new \InvalidArgumentException('quel reseau ?');
		}
		if (strlen($reseau) != 2) {
			throw new \InvalidArgumentException('nom sur 2 caractères');
		}
		if (empty($this->reseaux_nc)) {
			$this->get_reseaux();
		}
		return !(strpos($this->reseaux_nc, $reseau) === false);
	}

	const sql_test_identifiant_libre = 'select count(*) as n from utilisateur where username=$1';

	/**
	 * @brief Création d'un nouveau nom d'utilisateur
	 * @param $nom Nom de l'utilisateur
	 * @param $prenom Prénom de l'utilisateur
	 * @return string une proposition de nom d'utilisateur
	 */
	public static function genere_nom_utilisateur_libre($db, $nom, $prenom) {
		bobs_tests::cls($nom, bobs_tests::except_si_vide);
		bobs_tests::cls($prenom);

		$identifiant = strtolower($prenom).'.'.strtolower($nom);
		$identifiant = str_replace(' ','_',$identifiant);
		$q = bobs_qm()->query($db, 'utl_test_id', self::sql_test_identifiant_libre, array($identifiant));
		$r = self::fetch($q);
		$n = 0;
		$identifiant_base = $identifiant;
		while ($r['n'] >= 1) {
			$n++;
			if ($n > 20) // fort peut probable
				throw new Exception('impossible 20 homonymes ?');
			$identifiant = $identifiant_base.$n;
			$q = bobs_qm()->query($db, 'utl_test_id', self::sql_test_identifiant_libre, array($identifiant));
			$r = self::fetch($q);
		}
		return $identifiant;
	}

	const sql_liste_ref_tiers = 'select * from referentiel_utilisateur_tiers where id_utilisateur=$1';
	const sql_ajout_ref_tiers = 'insert into referentiel_utilisateur_tiers (tiers,id_utilisateur,id_tiers)
					values ($1,$2,$3)';
	const sql_suppr_ref_tiers = 'delete from referentiel_utilisateur_tiers where id_utilisateur=$1
					and tiers=$2 and id_tiers=$3';
	const sql_ref_tiers = 'select id_tiers from referentiel_utilisateur_tiers where id_utilisateur=$1 and tiers=$2';

	/**
	 * @brief ajouter une référence utilisateur dans un référentiel tiers
	 * @param $tiers identifiant du référentiel (gdtc)
	 */
	public function ref_tiers($tiers) {
		$q = bobs_qm()->query($this->db, 'sl_ref_tiersx', self::sql_ref_tiers, [$this->id_utilisateur, $tiers]);
		$r = self::fetch($q);
		if (!$r) {
			return false;
		}
		return $r['id_tiers'];
	}

	/**
	 * @brief ajouter une référence utilisateur dans un référentiel tiers
	 * @param $tiers identifiant du référentiel (gdtc)
	 * @param $ref l'identifiant de l'utilisateur
	 */
	public function ajout_reference_tiers($tiers, $ref) {
		self::cls($tiers, self::except_si_vide);
		self::cli($ref, self::except_si_inf_1);
		return bobs_qm()->query($this->db, 'add_ref_tiers_u', self::sql_ajout_ref_tiers, [$tiers, $this->id_utilisateur, $ref]);
	}

	/**
	 * @brief liste des références tiers de l'utilisateur
	 */
	public function liste_references_tiers() {
		$q = bobs_qm()->query($this->db, 'sel_ref_tiers_u', self::sql_liste_ref_tiers, [$this->id_utilisateur]);
		return self::fetch_all($q);
	}

	/**
	 * @brief Supprimer une référence tiers pour un utilisateur
	 * @param $tiers identifiant du référentiel (gdtc)
	 * @param $ref l'identifiant de l'utilisateur
	 */
	public function supprime_reference_tiers($tiers, $ref) {
		self::cls($tiers, self::except_si_vide);
		self::cli($ref, self::except_si_inf_1);
		$q = bobs_qm()->query($this->db, 'del_ref_tiers_u', self::sql_suppr_ref_tiers, [$this->id_utilisateur, $tiers, $ref]);
	}

	const sql_ext_liste = 'select * from utilisateur_extractions where id_utilisateur=$1 and pour_mad=$2';
	const sql_ext_ajoute = 'insert into utilisateur_extractions (id_utilisateur,xml,pour_mad) values ($1,$2,$3)';
	const sql_ext_suppr = 'delete from utilisateur_extractions where id_utilisateur=$1 and id_extraction=$2';
	const sql_ext_charge = 'select * from utilisateur_extractions where id_utilisateur=$1 and id_extraction=$2';

	const ext_pour_mad = true;
	const ext_sauv_utl = false;

	/**
	 * @brief Liste les extractions
	 * @return array
	 *
	 * une colonne nom est ajoutée aux colonnes de la table
	 */
	public function extraction_liste($pour_mad=self::ext_sauv_utl) {
		$pmad = $pour_mad?'true':'false';
		$q = bobs_qm()->query($this->db, 'u_ext_liste', self::sql_ext_liste, array($this->id_utilisateur,$pmad));
		$ex = self::fetch_all($q);
		foreach ($ex as $k=>$e) {
			$ex[$k]['nom'] = bobs_extractions::extrait_nom_xml($e['xml']);
		}
		return $ex;
	}

	public function extraction_liste_pour_mad() {
		return $this->extraction_liste(self::ext_pour_mad);
	}

	/**
	 * @brief Enregistre la définition une extraction
	 * @param $xml définition xml de l'extraction
	 * @return le résultat de la requête
	 */
	public function extraction_ajoute($xml, $mad=false) {
		$pmad = $mad?'true':'false';
		return bobs_qm()->query($this->db, 'u_ext_ajoute', self::sql_ext_ajoute, array($this->id_utilisateur, $xml, $pmad));
	}

	/**
	 * @brief Supprime la définition d'une extraction
	 * @param $id le numéro de l'extraction a supprimer
	 * @return le résultat de la requête
	 */
	public function extraction_supprime($id) {
		self::cli($id, self::except_si_inf_1);
		return bobs_qm()->query($this->db, 'u_ext_suppr', self::sql_ext_suppr, array($this->id_utilisateur, $id));
	}

	/**
	 * @brief Charge une extraction enregistrée (bobs_extractions_poste)
	 * @param $id le numéro de l'extraction a charger
	 * @return la nouvelle extraction
	 */
	public function extraction_charge($id) {
		$q = bobs_qm()->query($this->db, 'u_ext_charge', self::sql_ext_charge, array($this->id_utilisateur, $id));
		$ex_def = self::fetch($q);
		return bobs_extractions_poste::charge_xml($this->db, $ex_def['xml'], $this->id_utilisateur);
	}

	/**
	 * @brief Charge une extraction enregistrée (bobs_extractions_poste)
	 * @param $id le numéro de l'extraction a charger
	 * @return la nouvelle extraction
	 */
	public function extraction_charge_sans_restrictions($id) {
		$q = bobs_qm()->query($this->db, 'u_ext_charge', self::sql_ext_charge, array($this->id_utilisateur, $id));
		$ex_def = self::fetch($q);
		return bobs_extractions::charge_xml($this->db, $ex_def['xml']);
	}

	const sql_set_expert = 'update utilisateur set expert=$2 where id_utilisateur=$1';

	public function set_expert($val) {
		bobs_qm()->query($this->db, 'u_set_expert', self::sql_set_expert, array($this->id_utilisateur,$val==true?'true':'false'));
		$this->expert = $val==true;
		return true;
	}

	const sql_set_peut_ajouter_esp = 'update utilisateur set peut_ajouter_espece=$2 where id_utilisateur=$1';

	public function set_peut_ajouter_espece($val) {
		bobs_qm()->query($this->db, 'u_set_ajout_esp', self::sql_set_peut_ajouter_esp, array($this->id_utilisateur,$val==true?'true':'false'));
		$this->peut_ajouter_espece = $val == true;
		return true;
	}

	public function taches_en_attente() {
		return clicnat_tache::en_attente($this->db, $this->id_utilisateur);
	}

	public function taches_en_cours() {
		return clicnat_tache::en_cours($this->db, $this->id_utilisateur);
	}

	public function taches_terminees() {
		return clicnat_tache::dernieres_terminees($this->db, $this->id_utilisateur);
	}

	public function taches_planifiees() {
		return clicnat_tache::planif($this->db, $this->id_utilisateur);
	}

	public function fichiers() {
		$entrepot = entrepot::db();
		$grid = $entrepot->getGridFs();
		$t = [];

		$curseur = $grid->find([
			"id_utilisateur" => $this->id_utilisateur,
			"zone" => "fichiers_utilisateur"
		]);
		$curseur->sort(array("date_creation" => -1));
		return $curseur;
	}

	public function fichier_enregistrer($fichier, $lib, $content_type) {
		$entrepot = entrepot::db();
		$grid = $entrepot->getGridFs();
		$id = $grid->storeFile($fichier, array(
			"id_utilisateur" => $this->id_utilisateur,
			"filename" => basename($fichier),
			"lib" => $lib,
			"date_creation" => strftime('%Y-%m-%d %H:%M:%S',mktime()),
			"zone" => "fichiers_utilisateur",
			"content_type" => $content_type
		));
	}

	public function fichier($id) {
		$entrepot = entrepot::db();
		$grid = $entrepot->getGridFs();
		$fichier = $grid->findOne(array(
			"_id" => new MongoId($id)
		));

		if (!$fichier) {
			throw new Exception('le fichier existe pas');
		}

		if ($fichier->file['id_utilisateur'] != $this->id_utilisateur)
			throw new Exception('pas propriétaire');
		return $fichier;
	}

	const sql_doc_inbox_insert = 'insert into utilisateur_inbox (id_utilisateur,doc_id) values ($1,$2)';
	const sql_doc_inbox_list = 'select * from utilisateur_inbox where id_utilisateur=$1 order by date_creation desc';
	const sql_doc_inbox_del = 'delete from utilisateur_inbox where id_utilisateur=$1 and doc_id=$2';

	public function inbox_doc_insert($doc_id) {
		return $q = bobs_qm()->query($this->db, 'd_ibox_in', self::sql_doc_inbox_insert, [$this->id_utilisateur,$doc_id]);
	}

	public function inbox_docs() {
		$q = bobs_qm()->query($this->db, 'd_ibox_ls', self::sql_doc_inbox_list, [$this->id_utilisateur]);
		$docs = self::fetch_all($q);
		foreach ($docs as $k=>$doc) {
			$docs[$k]['doc'] = bobs_document::get_instance($doc['doc_id'], $this->db);
		}
		return $docs;
	}

	public function inbox_doc_suppr($doc_id) {
		self::cls($doc_id, self::except_si_vide);
		return $q = bobs_qm()->query($this->db, 'd_ibox_del', self::sql_doc_inbox_del, [$this->id_utilisateur,$doc_id]);
	}
}
