<?php
namespace Picnat\Clicnat;

class clicnat_tache extends bobs_element {
	use clicnat_mini_template;
	protected $id_tache;
	protected $date_exec;
	protected $date_exec_prevue;
	protected $date_fin;
	protected $id_utilisateur;
	protected $code_retour;
	protected $message_retour;

	protected $date_creation;
	protected $date_maj;
	protected $classe;
	protected $nom;
	protected $args;

	public function __get($c) {
		switch ($c) {
			case 'date_exec':
				return $this->date_exec;
			case 'date_exec_prevue':
				return $this->date_exec_prevue;
			case 'date_fin':
				return $this->date_fin;
			case 'code_retour':
				return $this->code_retour;
			case 'message_retour':
				return $this->message_retour;
			case 'date_creation':
				return $this->date_creation;
			case 'date_maj':
				return $this->date_maj;
			case 'classe':
				return $this->classe;
			case 'args':
				return $this->args;
			case 'id_tache':
				return $this->id_tache;
		}
	}

	const __table__ = 'taches';
	const __prim__ = 'id_tache';
	const __seq__ = 'taches_id_tache_seq';

	const ok = 0;
	const erreur = 1;
	const annul = 2;

	const sql_planif = 'select id_tache from taches where date_exec_prevue > now() and date_exec is null order by date_exec_prevue';
	const sql_en_attente = 'select id_tache from taches where date_exec_prevue <= now() and date_exec is null order by date_exec_prevue';
	const sql_en_cours = 'select id_tache from taches where date_exec is not null and date_fin is null';
	const sql_n_terminee = 'select id_tache from taches where date_fin is not null order by date_fin desc limit 200';

	const sql_u_planif = 'select id_tache from taches where id_utilisateur=$1 and date_exec_prevue > now() and date_exec is null order by date_exec_prevue';
	const sql_u_en_attente = 'select id_tache from taches where id_utilisateur=$1 and date_exec_prevue <= now() and date_exec is null order by date_exec_prevue';
	const sql_u_en_cours = 'select id_tache from taches where id_utilisateur=$1 and date_exec is not null and date_fin is null';
	const sql_u_n_terminee = 'select id_tache from taches where id_utilisateur=$1 and date_fin is not null order by date_fin desc limit 200';

	public function __construct($db, $id) {
		parent::__construct($db, self::__table__, self::__prim__, $id);
		$this->champ_date_maj = 'date_maj';
	}

	private static function liste($db, $req, $id_utilisateur=null) {
		$sql = false;
		switch ($req) {
			case 'planif':
				$sql = is_null($id_utilisateur)?self::sql_planif:self::sql_u_planif;
				break;
			case 'en_attente':
				$sql = is_null($id_utilisateur)?self::sql_en_attente:self::sql_u_en_attente;
				break;
			case 'en_cours':
				$sql = is_null($id_utilisateur)?self::sql_en_cours:self::sql_u_en_cours;
				break;
			case 'terminee':
				$sql = is_null($id_utilisateur)?self::sql_n_terminee:self::sql_u_n_terminee;
				break;
		}
		$q = bobs_qm()->query($db, md5($sql), $sql, is_null($id_utilisateur)?array():array($id_utilisateur));
		$r = self::fetch_all($q);
		return new clicnat_iterateur_taches($db, array_column($r, 'id_tache'));
	}

	public static function en_attente($db, $id_utilisateur=null) {
		return self::liste($db, 'en_attente', $id_utilisateur);
	}

	public static function en_cours($db, $id_utilisateur=null) {
		return self::liste($db, 'en_cours', $id_utilisateur);
	}

	public static function dernieres_terminees($db, $id_utilisateur=null) {
		return self::liste($db, 'terminee', $id_utilisateur);
	}

	public static function planif($db, $id_utilisateur=null) {
		return self::liste($db, 'planif', $id_utilisateur);
	}

	const sql_annuler = "update taches set date_exec=now(),date_fin=now(),code_retour=$2 where id_tache=$1";

	public function annuler() {
		$q = bobs_qm()->query($this->db, 'maj_date_exec_annul', self::sql_annuler, [$this->id_tache,self::annul]);
	}

	const sql_update_dexe_now ='update taches set date_exec_prevue=now() where id_tache=$1';
	public function exec_now() {
		$q = bobs_qm()->query($this->db, 'maj_date_exec_now', self::sql_update_dexe_now, [$this->id_tache]);
	}

	public function executer() {
		$this->update_field_now('date_exec');
		$classe = $this->classe;
		try {
			if (!class_exists($classe)) {
				throw new Exception("$classe existe pas");
			}
			$tr = new $classe($this->db,$this->args);
			$retour = $tr->executer();
		} catch (Exception $e) {
			$retour = array(self::erreur, $e->getMessage());
		}
		$this->update_field('code_retour', $retour[0], true);
		$this->update_field('message_retour', $retour[1], true);
		$this->update_field_now('date_fin');
		if (!empty($this->id_utilisateur)) {
			$vars = [
				"nom_tache" => $this->__toString(),
				"nom" => $this->utilisateur()->nom,
				"prenom" => $this->utilisateur()->prenom
			];
			$message = new clicnat_mail();
			$message->from('ne-pas-repondre@clicnat.fr');
			$sujet_tpl = clicnat_textes::par_nom(get_db(), 'base/tache/notification_sujet')->texte;
			$msg_tpl = clicnat_textes::par_nom(get_db(), 'base/tache/notification')->texte;
			$message->sujet(self::mini_template($sujet_tpl, $vars));
			$message->message(self::mini_template($msg_tpl, $vars));
			$message->envoi($this->utilisateur()->mail);
		}
	}

	public function __toString() {
		return $this->nom;
	}

	public static function ajouter($db, $date_exec_prevue, $id_utilisateur, $nom, $classe, $params) {
		$id_tache = self::nextval($db, self::__seq__);
		$data = array(
			'id_tache' => $id_tache,
			'id_utilisateur' => $id_utilisateur,
			'nom' => $nom,
			'classe' => $classe,
			'date_exec_prevue' => $date_exec_prevue,
			'args' => json_encode($params)
		);
		parent::insert($db, self::__table__, $data);
		return $id_tache;
	}

	public function utilisateur() {
		return get_utilisateur($this->db, $this->id_utilisateur);
	}
}
