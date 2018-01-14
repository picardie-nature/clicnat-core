<?php
namespace Picnat\Clicnat;

use InvalidArgumentException;
use Exception;
use DateTime;
use DateInterval;

/**
 * @brief Observation
 *
 * Une observation définit l'heure et lieu d'une observation,
 * elle contient des citations qui listent les espèces observées
 *
 */
class bobs_observation extends bobs_element_commentaire {
	protected $id_observation;
	protected $id_utilisateur;
	protected $date_observation;
	protected $precision_date;
	protected $id_espace;
	protected $date_modif;
	protected $espace_table;
	protected $brouillard;
	protected $heure_observation;
	protected $duree_observation;
	protected $date_creation;
	protected $date_deb;
	protected $date_fin;

	protected $champ_date_maj;

	public $date_obs_tstamp;

	const table_commentaire = 'observations_commentaires';

	public function __construct($db, $id) {
		parent::__construct($db, 'observations', 'id_observation', $id);
		$this->date_obs_tstamp = strtotime($this->date_observation);
		$this->champ_date_maj = 'date_modif';
	}

	public function __get($prop) {
		switch($prop) {
			case 'id_observation':
				return $this->id_observation;
			case 'id_utilisateur':
				return $this->id_utilisateur;
			case 'date_observation':
				return $this->date_observation;
			case 'precision_date':
				return $this->precision_date;
			case 'precision_date_lib':
				switch ($this->precision_date) {
					case 0:
						return "précise";
					case 1:
						return "1 jour près";
					case 7:
						return "+ ou - une semaine";
					case 7*2:
						return "+ ou - deux semaines";
					case 7*6:
						return "+ ou - six semaines";
					case 30*3:
						return "+ ou - trois mois";
					case 30*6:
						list($y,$m,$d) = explode('-', $this->date_observation);
						if (((int)$m == 6 )&& ((int)$d == 31))
							return "dans l'année";
						else
							return "+/- 6 mois";
					default:
						if ($this->precision_date > 365)
							return sprintf("+ ou - %0.2f années", $this->precision_date/365);
						return "+ ou - {$this->precision_date} jours";
				}
			case 'date_deb':
			case 'date_min':
				if (isset($this->date_deb) && !empty($this->date_deb))
					return new DateTime($this->date_deb);
				$dt = new DateTime($this->date_observation);
				if (!empty($this->precision_date))
					$dt->sub(new DateInterval(sprintf("P%0dD", $this->precision_date)));
				return $dt;
			case 'date_fin':
			case 'date_max':
				if (isset($this->date_fin))
					return new DateTime($this->date_fin);
				$dt = new DateTime($this->date_observation);
				if (!empty($this->precision_date))
					$dt->add(new DateInterval(sprintf("P%0dD", $this->precision_date)));
				return $dt;
			case 'id_espace':
				return $this->id_espace;
			case 'date_modif':
				return $this->date_modif;
			case 'espace_table':
				return $this->espace_table;
			case 'heure_observation':
				return $this->heure_observation;
			case 'duree_observation':
				return $this->duree_observation;
			case 'brouillard':
				return $this->brouillard == 't';
			case 'date_creation':
				return $this->date_creation;
			default:
				throw new InvalidArgumentException("prop. $prop");
		}
	}

	/**
	 * @brief obtenir l'observation par un numéro de citation
	 * @param $id_citation le numéro de citation
	 * @return bobs_observation|false
	 */
	public static function by_citation($db, $id_citation) {
		$id_citation = sprintf('%d', $id_citation);

		if (empty($id_citation)) {
			throw new InvalidArgumentException('$id_citation ne peut être vide');
		}

		$sql = sprintf("select observations.*
						from observations obs,citations ci
						where obs.id_observation=ci.id_observation
						and ci.id_citation=%d limit 1", $id_citation);

		$q = self::query($db, $sql);
		$r = self::fetch($q);

		if (empty($r)) {
			return false;
		}

		return get_observation($db, $r['id_observation']);
	}

	/**
	 * @brief Liste les espèces observées
	 *
	 * Un tableau associatif est retourné avec ces clés :
	 *   - id_espece (ref espèce interne)
	 *   - nom_s (nom scientifique)
	 *   - nom_f (nom français)
	 *   - total (effectif total observé (peut être faussé par les obs à nb=0))
	 *   - obj (l'objet bobs_espece associé)
	 *
	 * @return array un tableau associatif
	 */
	public function get_especes_vues() {
		$sql = 'select citations.id_espece,nom_s,nom_f,sum(nb) as total
				from citations,especes
				where id_observation=$1
				and especes.id_espece=citations.id_espece
				and nb >= 0
				group by citations.id_espece,nom_s,nom_f,systematique
				order by systematique';
		$q = bobs_qm()->query($this->db, 'obs_get_species', $sql, array($this->id_observation));
		$t = [];

		while ($r = self::fetch($q)) {
			$r['obj'] = get_espece($this->db, $r['id_espece']);
			$t[] = $r;
		}

		return $t;
	}

	public static function get_statistique_saisie($db, $famille=false,$departement=false) {
		if ($famille) {
			$sql = sprintf("select count(*) as n, to_char(date_modif,'MM') as mois,to_char(date_modif,'YYYY') as annee
					from observations o, citations c,especes e
					where o.id_observation = c.id_observation
					and c.id_espece=e.id_espece
					and e.classe = '%s'
					group by to_char(date_modif,'MM'),to_char(date_modif,'YYYY')
					order by to_char(date_modif,'YYYY'),to_char(date_modif,'MM')",
					self::escape($famille));
		} else if ($departement) {
			$sql = sprintf("select count(*) as n, to_char(date_modif,'MM') as mois,to_char(date_modif,'YYYY') as annee
					from observations o, citations c,especes e,espace_point ep,espace_departement ed
					where o.id_observation = c.id_observation
					and c.id_espece = e.id_espece
					and ep.id_espace = o.id_espace
					and ed.id_espace = %d
					and contains(ed.the_geom, ep.the_geom)
					group by to_char(date_modif,'MM'),to_char(date_modif,'YYYY')
					order by to_char(date_modif,'YYYY'),to_char(date_modif,'MM')",
					$departement);
		} else {
			$sql = "select count(*) as n,to_char(date_modif,'MM') as mois,to_char(date_modif,'YYYY') as annee
					from observations o, citations c
					where o.id_observation = c.id_observation
					group by to_char(date_modif,'MM'),to_char(date_modif,'YYYY')
					order by to_char(date_modif,'YYYY'),to_char(date_modif,'MM')";
		}
		$q = self::query($db, $sql);
		$t = array();
		while ($r = self::fetch($q)) {
			if (!empty($r['annee'])) {
				if (empty($t[$r['annee']]))
					$t[$r['annee']] = array();
				$t[$r['annee']][$r['mois']] = $r['n'];
			}
		}
		foreach ($t as $an => $ta)
			for ($i = 1; $i <= 12; $i++)
				$t[$an][sprintf("%02d",$i)] = empty($ta[sprintf("%02d",$i)])?'0':$ta[sprintf("%02d",$i)];


		return $t;
	}

	/**
	 * @brief retourne un tableau des observateurs
	 * @deprecated
	 *
	 * Clés pour les lignes du tableau
	 *   - id_utilisateur
	 *   - nom
	 *   - prenom
	 *
	 * @return array un tableau
	 */
	public function get_observateurs() {
		self::cli($this->id_observation);
		return self::query_fetch_all($this->db, sprintf("
			select u.id_utilisateur, u.nom, u.prenom
			from utilisateur u, observations_observateurs oo
			where oo.id_utilisateur = u.id_utilisateur
			and oo.id_observation = %d order by u.nom, u.prenom",
			$this->id_observation));
	}

	const sql_l_observateurs = "
		select u.id_utilisateur, u.nom, u.prenom
		from utilisateur u, observations_observateurs oo
		where oo.id_utilisateur = u.id_utilisateur
		and oo.id_observation = $1
		order by u.nom,u.prenom
	";

	public function observateurs() {
		$q = bobs_qm()->query($this->db, 'l_observat2', self::sql_l_observateurs, [$this->id_observation]);
		return new clicnat_iterateur_utilisateurs($this->db, array_column(self::fetch_all($q),'id_utilisateur'));
	}

	public function est_observateur($utilisateur){
		$id_utilisateur = is_object($utilisateur)?$utilisateur->id_utilisateur:(int)$utilisateur;
		$observateurs = $this->observateurs();
		return $observateurs->in_array($id_utilisateur);
	}

	/**
	 * @brief personne qui a saisie l'observation
	 * @return bobs_utilisateur
	 */
	public function get_auteur() {
		return get_utilisateur($this->db, $this->id_utilisateur);
	}

	public function get_observateurs_str() {
		$s = '';
		foreach ($this->get_observateurs() as $obs) {
			$s.= " {$obs['nom']} {$obs['prenom']},";
		}
		return str_replace('"',' ',trim($s,', '));
	}

	public function ajoute_observateur(clicnat_utilisateur $utilisateur) {
		return $this->add_observateur($utilisateur->id_utilisateur);
	}

	/**
	 * @brief Ajoute un observateur
	 * @param int $id id_utilisateur
	 * @return handler le résultat de la requête
	 */
	public function add_observateur($id) {
		self::cli($id);
		self::cli($this->id_observation);
		try {
		    $q = bobs_qm()->query($this->db, 'obs_add_observer',
			'insert into observations_observateurs (id_utilisateur,id_observation) values ($1,$2)',
			array($id, $this->id_observation));
		} catch (Exception $e) {
		    bobs_log(sprintf('ERROR: try to add user %d to observation %d', $id, $this->id_observation));
		    throw $e;
		}
		bobs_log(sprintf('add user %d to observation %d', $id, $this->id_observation));
		return $q;
	}

	public function retire_observateur(clicnat_utilisateur $utilisateur) {
		return $this->del_observateur($utilisateur->id_utilisateur);
	}

	/**
	 * @brief Retire un observateur
	 * @param int $id id_utilisateur
	 * @return handler le résultat de la requête
	 */
	public function del_observateur($id) {
		self::cli($id);
		self::cli($this->id_observation);
		try {
		    $q = bobs_qm()->query($this->db, 'obs_del_observer',
			'delete from observations_observateurs where id_utilisateur=$1 and id_observation=$2',
			array($id, $this->id_observation));
		} catch (Exception $e) {
		    bobs_log(sprintf("Can't remove user %d from observation %d", $id, $this->id_observation));
		    throw $e;
		}
		bobs_log(sprintf('remove user %d from observation %d', $id, $this->id_observation));
		return $q;

	}
	/**
	 * @brief Objet cartographique associé
	 * @deprecated
	 * @return bobs_espace
	 */
	public function get_espace() {
		return $this->espace();
	}

	/**
	 * @brief Objet cartographique associé
	 * @return bobs_espace
	 */
	public function espace() {
		return get_espace($this->db, $this->espace_table, $this->id_espace);
	}

	/**
	 * @brief Création d'une nouvelle observation
	 *
	 * data est un tableau associatif qui doit contenir les clés
	 * suivante :
	 *   - id_utilisateur
	 *   - date_observation (yyyy-mm-dd)
	 *   - id_espace le numéro de l'objet géographique
	 *   - table_espace le nom de la table ou est stocké l'objet
	 *   - precision_date
	 *
	 * @param ressource $db
	 * @param array $data
	 * @return integer Numéro de l'observation
	 */
	public static function insertObservation($db, $data) {
		if (defined('MAINT_ANCIENNE_DATE') && isset($data['date_observation'])) {
			// ancien traitement de la date
			$date_observation = $data['date_observation'];
			if (array_key_exists('precision_date', $data)) {
				$precision_date = $data['precision_date'];
			} else {
				$precision_date = 0;
			}

			// nouveau traitement
			if ($precision_date == 0) {
				$data['datedeb'] = $data['date_observation'];
				$data['datefin'] = $data['date_observation'];
			} else {
				$dn = strtotime($data['date_observation']);
				$data['datedeb'] = strftime("%Y-%m-%d", $dn-86400*(int)$data['precision_date']);
				$data['datefin'] = strftime("%Y-%m-%d", $dn-86400*(int)$data['precision_date']);
			}
		} else if (defined('MAINT_ANCIENNE_DATE') && isset($data['datedeb'])) {
			if (!isset($data['datefin'])) {
				$data['datefin'] = $data['datedeb'];
			}

			if ($data['datedeb'] == $data['datefin']) {
				$date_observation = $data['datedeb'];
				$precision_date = 0;
			} else {
				list($deb_y,$deb_m,$deb_d) = explode('-', $data['datedeb']);
				list($fin_y,$fin_m,$fin_d) = explode('-', $data['datefin']);
				$deb = new DateTime();
				$deb->setDate($deb_y, $deb_m, $deb_d);
				$deb->setTime(0,0);
				$fin = new DateTime();
				$fin->setDate($fin_y, $fin_m, $fin_d);
				$fin->setTime(0,0);
				$diff = $fin->diff($deb);
				$diff_2 = new DateInterval(sprintf("P%dD",$diff->days/2));
				$inter = clone $deb;
				$inter->add($diff_2);
				$date_observation = $inter->format("Y-m-d");
				$precision_date = (int)($diff->days/2);
			}
		}

		$id_utilisateur = $data['id_utilisateur'];
		$id_espace = $data['id_espace'];
		$table_espace = $data['table_espace'];

		/** @todo tester les valeurs possibles table_espace */

		self::cli($id_utilisateur);
		self::cls($date_observation);
		self::cli($id_espace);
		self::cls($table_espace);
		self::cli($precision_date);

		if (empty($date_observation))
			throw new InvalidArgumentException("date_observation ne peut être vide date_observation={$data['date_observation']} datedeb={$data['datedeb']} datefin={$data['datefin']}");
		if (empty($id_utilisateur))
			throw new InvalidArgumentException('$id_utilisateur ne peut être vide');
		if (empty($id_espace))
			throw new InvalidArgumentException('$id_espace ne peut être vide');
		if (empty($table_espace))
			throw new InvalidArgumentException('$table_espace ne peut être vide');


		$id_observation = self::nextval($db, 'observations_id_observation_seq');

		parent::insert($db, 'observations', array(
			'id_observation' => $id_observation,
			'id_utilisateur' => $id_utilisateur,
			'date_observation' => $date_observation,
			'id_espace' => $id_espace,
			'espace_table' => $table_espace,
			'date_creation' => strftime('%Y-%m-%d %H:%M:%S',mktime()),
			'precision_date' => $precision_date,
			'date_deb' => $data['datedeb'],
			'date_fin' => $data['datefin']
		));
		bobs_log(sprintf('create new observation %d by user %d', $id_observation, $id_utilisateur));
		return $id_observation;
	}

	/**
	 * @brief Ajoute une citation a l'observation
	 * @param int $id_espece
	 * @return int le numéro de la nouvelle citation
	 */
	public function add_citation($id_espece) {
		self::cli($id_espece);
		self::cli($this->id_observation);

		$id_citation = self::nextval($this->db, 'citations_id_citation_seq');
		$data = array(
			'id_citation' => $id_citation,
			'id_observation' => $this->id_observation,
			'id_espece' => $id_espece
		);
		bobs_citation::insert($this->db, 'citations', $data);
		bobs_log(sprintf('add citation %d to obs %d', $id_citation, $this->id_observation));
		return $id_citation;
	}

	const sql_s_citations_ids = 'select id_citation from citations where id_observation=$1 order by id_citation desc';

	/**
	 * @brief fourni un tableau des id de citations associés
	 * @return un tableau
	 */
	public function get_citations_ids() {
		$q = bobs_qm()->query($this->db, 'obs_citations_ids', self::sql_s_citations_ids, array($this->id_observation));
		$ids = array();
		while ($r = self::fetch($q)) {
			$ids[] = $r['id_citation'];
		}
		return $ids;
	}

	/**
	 * @brief liste les citations associées
	 * @return clicnat_iterateur_citations
	 */
	public function get_citations() {
		return new clicnat_iterateur_citations($this->db, $this->get_citations_ids());
	}

	const sql_suppr_observation = 'delete from observations where id_observation=$1';
	const sql_suppr_observateurs = 'delete from observations_observateurs where id_observation=$1';
	const sql_suppr_commtrs = 'delete from observations_commentaires where id_observation=$1';

	/**
	 * @brief supprime cette observation (cascade)
	 *
	 * Supprime l'observation avec ses citations et les observateurs associés
	 * Utilise une transaction et lève une exception en cas d'erreur
	 */
	public function delete() {
		if (empty($this->id_observation)) {
			throw new Exception('$this->id_observation vide');
		}

		try {
			pg_query($this->db, 'begin');

			// supprime toutes ses citations
			$citations_ids = $this->get_citations_ids();
			if (count($citations_ids) > 0)
			foreach ($citations_ids as $citation_id) {
				$citation = new bobs_citation($this->db, $citation_id);
				$citation->delete();
			}

			// supprime ses auteurs
			bobs_qm()->query($this->db, 'obs_del_auteurs', self::sql_suppr_observateurs, array($this->id_observation));

			// supprime ses commentaires
			bobs_qm()->query($this->db, 'obs_del_commtrs', self::sql_suppr_commtrs, array($this->id_observation));

			// ce supprime
			bobs_qm()->query($this->db, 'obs_del', self::sql_suppr_observation, array($this->id_observation));

			pg_query($this->db, 'commit');
			bobs_log(sprintf("observation %d deleted", $this->id_observation));
		} catch (Exception $e) {
			pg_query($this->db, 'rollback');
			bobs_log(sprintf("ERROR : can't drop observation %d", $this->id_observation));
			throw $e;
		}
	}

	/**
	 * @brief retourne une citation associée
	 * @param int $id numéro de citation
	 * @return bobs_citation
	 *
	 * lève une exception si la citation demandée n'appartient pas à cette observation
	 */
	public function get_citation($id) {
		self::cli($id);
		$c = new bobs_citation($this->db, $id);

		if ($this->id_observation != $c->id_observation) {
			bobs_log($this->db, sprintf('ERROR : try to associate citation %d with observation %d', $id, $this->id_observation));
			throw new InvalidArgumentException('$id not in obs '.$this->id_observation.' ?');
		}
		return $c;
	}

	/**
	 * @brief Envoi de l'observation en validation
	 *
	 * - un tag ATTV est ajouté sur chaque citation et le flag brouillard est retiré
	 * - tous les observateurs associés et l'auteur sont immédiatement autorisés a voir la citation
	 */
	public function send() {
		self::cli($this->id_observation);
		if ($this->brouillard != 't') {
			throw new Exception("brouillard = false");
		}
		$tag_attv = bobs_tags::by_ref($this->db, TAG_ATTENTE_VALIDATION);
		$tag_junior = bobs_tags::by_ref($this->db, TAG_NOUVEL_OBSERVATEUR);
		$id_citations = $this->get_citations_ids();

		$u = get_bobs_utilisateur($this->db, $this->id_utilisateur);

		if (count($id_citations) == 0 or !is_array($id_citations))
			throw new Exception("il n'y a pas de citations d'espèces sur cette observation");
		try {
			pg_query($this->db, 'begin');
			if (count($id_citations) > 0)
			foreach ($id_citations as $id_citation) {
				$citation = new bobs_citation($this->db, $id_citation);
				$citation->ajoute_tag($tag_attv->id_tag);
				if ($u->junior()) {
					bobs_log('junior ! ajoute le tag');
					$citation->ajoute_tag($tag_junior->id_tag);
				}
				foreach ($this->get_observateurs() as $observateur) {
					$uobs = get_bobs_utilisateur($this->db, $observateur['id_utilisateur']);
					$uobs->add_citation_authok($citation->id_citation);
				}
				if ($u) $u->add_citation_authok($citation->id_citation);
			}
			$this->update_field('brouillard', 'f');
			bobs_log(sprintf('observation %d send for validation', $this->id_observation));
			pg_query($this->db, 'commit');
		} catch (Exception $e) {
			pg_query($this->db, 'rollback');
			bobs_log(sprintf("ERROR : can't send observation %d for validation", $this->id_observation));
			throw $e;
		}
	}

	public function autorise_modification($utilisateur) {
		// un administrateur peut la modifier
		if ($utilisateur->acces_qg_ok() == true) {
			return true;
		}

		// l'utilisateur qui l'a saisie
		if (($this->id_utilisateur == $utilisateur->id_utilisateur)) {
			return true;
		}

		// un des utilisateur mentionné comme observateur
		foreach ($this->get_observateurs() as $observateur) {
			if ($observateur['id_utilisateur'] == $this->id_utilisateur)
				return true;
		}

		return false;
	}

	/**
	 * @brief définit l'heure de l'observation ou le début
	 * @param int $h heure
	 * @param int $m minute
	 * @param int $s seconde
	 * @return handler le résultat de la requête
	 */
	public function set_heure($h,$m,$s=0) {
		self::cli($this->id_observation);
		self::cli($h, false);
		self::cli($m, false);
		self::cli($s, false);
		try {
			$this->update_field("heure_observation", sprintf('%02d:%02d:%02d',$h,$m,$s));
		} catch (Exception $e) {
			bobs_log(sprintf("ERROR : can't set observation hour for observation %d", $this->id_observation));
			throw $e;
		}
		bobs_log(sprintf('observation %d hour set to %02d:%02d:%02d', $this->id_observation, $h, $m, $s));
		$this->heure_observation = sprintf('%02d:%02d:%02d',$h,$m,$s);
		return true;
	}

	/**
	 * @brief définit l'heure de fin de l'observation
	 * @param int $h nombre d'heures
	 * @param int $m nombre de minutes
	 * @param int $s nombre de secondes
	 * @return handler le résultat de la requête
	 */
	public function set_duree($h,$m,$s=0) {
		self::cli($this->id_observation);
		self::cli($h, false);
		self::cli($m, false);
		self::cli($s, false);
		try {
			$this->update_field("duree_observation", sprintf('%02d:%02d:%02d',$h,$m,$s));
		} catch (Exception $e) {
			bobs_log(sprintf("ERROR : can't set observation duration for observation %d", $this->id_observation));
			throw $e;
		}
		bobs_log(sprintf('observation %d duration set to %02d:%02d:%02d', $this->id_observation, $h, $m, $s));
		return true;
	}

	const sql_set_heure_fin = 'update observations set duree_observation=$2-heure_observation where id_observation=$1';

	/**
	 * @brief définit la durée a partir de l'heure de fin
	 *
	 * Si l'heure de fin est après l'heure de début (pas de changement de jour
	 *
	 * @param int $h
	 * @return handler le résultat de la requête
	 */
	public function set_heure_fin($h, $m, $s) {
		self::cli($this->id_observation);
		self::cli($h, false);
		self::cli($m, false);
		self::cli($s, false);

		$heure_deb = $this->get_heure();
		$heure_fin = new bobs_time(sprintf("%02d:%02d:%02d", $h, $m, $s));
		if (bobs_time::compare($heure_deb, $heure_fin) == bobs_time::lower) {
			$q = bobs_qm()->query($this->db, 'obs_set_dhf', self::sql_set_heure_fin, array($this->id_observation, $heure_fin->get_str()));
			$this->update_date_maj_field();
		}
	}

	public function get_duree() {
	    if (empty($this->duree_observation))
		    return null;
	    else
		    return new bobs_time($this->duree_observation);

	}

	/**
	 * @brief retourne l'heure
	 * @return bobs_time
	 */
	public function get_heure()
	{
	    if (empty($this->heure_observation))
		    return null;
	    else
		    return new bobs_time($this->heure_observation);

	}

	public function get_tags() {
	    $where = 'and id_observation=$1';
	    return $this->__get_tags(BOBS_TBL_TAG_OBSERVATION, $this->id_observation, $where);
	}

	public function ajoute_tag($id_tag, $intval=null, $textval=null) {
	    return $this->__ajoute_tag(BOBS_TBL_TAG_OBSERVATION, 'id_observation', $id_tag, $this->id_observation, $intval, $textval);
	}

	public function supprime_tag($id_tag, $id_utilisateur=false) {
		$this->__supprime_tag(BOBS_TBL_TAG_OBSERVATION, 'id_observation', $id_tag, $this->id_observation);
		if (is_int($id_utilisateur)) {
			$this->ajoute_commentaire('attr', $id_utilisateur, "tag -$id_tag");
		}
		$this->get_tags();
	}

	public function dupliquer($date_observation) {
		$data = [
			'id_observation' => $this->id_observation,
			'id_utilisateur' => $this->id_utilisateur,
			'date_observation' => $date_observation,
			'id_espace' => $this->id_espace,
			'table_espace' => $this->espace_table
		];
		self::query($this->db, 'begin');
		$new_id_observation = self::insertObservation($this->db, $data);
		$observation = new bobs_observation($this->db, $new_id_observation);

    $observateurs = $this->get_observateurs();
    if (count($observateurs) > 0) {
			foreach ($observateurs as $observateur) {
				$observation->add_observateur($observateur['id_utilisateur']);
			}
		}

		$citations = $this->get_citations_ids();
		if (count($citations) > 0) {
			foreach ($citations as $citation_id) {
				$citation = $this->get_citation($citation_id);
				$observation->add_citation($citation->id_espece);
			}
		}
		self::query($this->db, 'commit');
		return $observation->id_observation;
	}

	const distance_cache_path = '/tmp/cache_distance';

	public function get_distance($observation_ou_citation) {
		switch (get_class($observation_ou_citation)) {
			case 'bobs_citation':
				$obs = $observation_ou_citation->get_observation();
				$espace_b = $obs->espace_table;
				$id_b = $obs->id_espace;
				break;
			case 'bobs_observation':
				$espace_b = $observation_ou_citation->espace_table;
				$id_b = $observation_ou_citation->id_espace;
				break;
			default:
				throw new Exception('que faire de '.get_class($observation_ou_citation));
		}
		$d = cache_distance()->get($this->id_espace, $id_b);
		if ($d === false) {
			// on va le calculer
			$sql = "select
					ST_Distance_sphere(
						espace_a.the_geom,
						espace_b.the_geom
					) as distance
					from {$this->espace_table} espace_a, $espace_b espace_b
					where espace_a.id_espace=$1
					and espace_b.id_espace=$2";

			$q = bobs_qm()->query($this->db, "de_{$this->espace_table}_{$espace_b}", $sql, array(
					$this->id_espace, $id_b));
			$r = bobs_element::fetch($q);
			$d = $r['distance'];
			// et l'enregistrer
			cache_distance()->set($this->id_espace, $id_b, $d);
		}
		return $d;
	}

	public function get_commentaires() {
		return $this->__get_commentaires(self::table_commentaire, 'id_observation', $this->id_observation);
	}

	public function ajoute_commentaire($type_c, $id_utilisateur, $commtr) {
		return $this->__ajoute_commentaire(self::table_commentaire, 'id_observation', $this->id_observation, $type_c, $commtr, $id_utilisateur);
	}

	public function modification($id_utilisateur, $champ, $valeur) {
		switch ($champ) {
			case 'date_observation':
				$ancienne_date = $this->date_observation;
				$nouvelle_date = self::date_fr2sql($valeur);
				if ($this->set_date_observation($nouvelle_date))
					$this->ajoute_commentaire('attr',$id_utilisateur,sprintf('date_observation %s => %s', $ancienne_date, $nouvelle_date));
				break;
			case 'heure_observation':
				$ancienne_heure = $this->heure_observation;
				$nouvelle_heure = sprintf("%02d:%02d:%02d",$valeur['h'],$valeur['m'],$valeur['s']);
				$this->set_heure($valeur['h'], $valeur['m'], $valeur['s']);
				$this->ajoute_commentaire('attr', $id_utilisateur, sprintf('heure_observation %s => %s', $ancienne_heure, $nouvelle_heure));
				break;
			case 'ajoute_observateur':
				try {
					$this->add_observateur($valeur);
				} catch (Exception $e) {
					return false;
				}
				$observ = get_utilisateur($this->db, $valeur);
				$this->ajoute_commentaire('attr', $id_utilisateur, sprintf('observateur +%s (%s)', $valeur, $observ));
				break;
			case 'retire_observateur':
				try {
					$this->del_observateur($valeur);
				} catch (Exception $e) {
					return false;
				}
				$observ = get_utilisateur($this->db, $valeur);
				$this->ajoute_commentaire('attr', $id_utilisateur, sprintf('observateur -%s (%s)', $valeur, $observ));
				break;
		}
		return true;
	}

	public function set_date_observation($date) {
		self::cls($date);

		if (empty($date))
			return false;

		if ($this->date_observation == $date)
			return false;

		$q = $this->update_field('date_observation', $date);
		if (!$q) {
			return false;
		}

		$this->date_observation = $date;
		$this->date_obs_tstamp = strtotime($this->date_observation);

		return true;
	}

	/**
	 * @brief interval depuis la durée
	 * @return DateInterval
	 */
	public function get_observation_dateinterval() {
		if (empty($this->duree_observation)) {
			return new DateInterval('PT0S');
		}
		list($h,$m,$s) = explode(':', $this->duree_observation);
		return new DateInterval(sprintf('PT%dH%dM%dS', $h, $m, $s));

	}

	/**
	 * @brief DateTime de la date de début d'observation
	 * @return DateTime
	 */
	public function get_observation_deb_datetime() {
		if (empty($this->heure_observation))
			return new DateTime($this->date_observation);
		return new DateTime("{$this->date_observation} {$this->heure_observation}");
	}

	/**
	 * @brief DateTime de la date de fin d'observation
	 * @return DateTime
	 */
	public function get_observation_fin_datetime() {
		$date =  $this->get_observation_deb_datetime();
		list($v_maj,$v_min,$v_rel) = explode('.',PHP_VERSION);
		if (($v_maj >= 5) && ($v_min >= 3)) {
			$date->add($this->get_observation_dateinterval());
			return $date;
		}
		list($h,$m,$s) = explode(':', $this->duree_observation);
		return new DateTime(strftime('%d-%m-%Y %H:%M:%S', strtotime($date->format('d-m-Y G:i:s')) + $h*3600 + $m*60 + $s));
	}
}



?>
