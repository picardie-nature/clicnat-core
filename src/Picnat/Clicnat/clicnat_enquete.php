<?php
namespace Picnat\Clicnat;

class clicnat_enquete extends bobs_element {
	protected $id_enquete;
	protected $nom;
	protected $active;

	const table = 'enquete_def';
	const pk = 'id_enquete';

	const sql_s_versions = 'select * from enquete_def_version where id_enquete=$1 order by version';
	const sql_all = 'select * from enquete_def order by nom';
	const sql_u_version = 'select * from enquete_def_version where id_enquete=$1 and version=$2';
	const sql_enquete_active_esp = 'select ee.id_enquete,max(version) as version
                from enquete_espece ee,enquete_def_version edv,especes esrc,especes eenq
                where esrc.id_espece=$1
                and ee.id_enquete=edv.id_enquete
                and ee.id_espece=eenq.id_espece
                and eenq.borne_a<=esrc.borne_a
                and eenq.borne_b>=esrc.borne_b
                group by ee.id_enquete,eenq.borne_a-eenq.borne_a
                order by eenq.borne_a-eenq.borne_a';
	const sql_nouvelle_version = 'insert into enquete_def_version (id_enquete,version,definition) values ($1,$2,xmlparse(document $3))';
	const sql_l_taxons = 'select id_espece from enquete_espece where id_enquete=$1';
	const sql_i_taxon = 'insert into enquete_espece (id_enquete,id_espece) values ($1,$2)';
	const sql_d_taxon = 'delete from enquete_espece where id_enquete=$1 and id_espece=$2';

	public function __construct($db, $id) {
		parent::__construct($db, self::table, self::pk, $id);
	}


	public static function getInstance($db, $id) {
		static $instances;

		if (!isset($instances))
			$instances = [];

		if (!isset($instances[$id]))
			$instances[$id] = new self($db, $id);

		return $instances[$id];
	}

	public function __toString() {
		return "{$this->nom} #{$this->id_enquete}";
	}

	public function __get($champ) {
		switch ($champ) {
			case 'id_enquete':
				return $this->id_enquete;
			case 'nom':
				return $this->nom;
			case 'active':
				return $this->active=='t';
		}
	}

	public function ajouter_une_version($xml = "<enquete></enquete>") {
		$versions = $this->versions();
		$n = count($versions) + 1;
		$prefix = '<?xml version="1.0"?>';
		$q = bobs_qm()->query($this->db, 'insert_n_version', self::sql_nouvelle_version, [$this->id_enquete, $n, $prefix.$xml]);
	}

	public function versions() {
		$t = array();
		$q = bobs_qm()->query($this->db, 'cenq_s_versions', self::sql_s_versions, array($this->id_enquete));
		while ($r = self::fetch($q)) {
			$t[$r['version']] = new clicnat_enquete_version($this->db, $r['id_enquete'], $r['version'], $r['definition']);
		}
		return $t;
	}

	public function version($n) {
		$q = bobs_qm()->query($this->db, 'cenq_version_u', self::sql_u_version, array($this->id_enquete, $n));
		$r = self::fetch($q);
		return new clicnat_enquete_version($this->db, $r['id_enquete'], $r['version'], $r['definition']);
	}

	public static function enquetes($db) {
		$q = bobs_qm()->query($db, 'cenq_all', self::sql_all, array());
		$t = array();
		while ($r = self::fetch($q)) {
			$t[] = new clicnat_enquete($db, $r);
		}
		return $t;
	}

	public static function enquetes_espece_derniere_version($db, $id_espece) {
		$q = bobs_qm()->query($db, 'enq_act_esp', self::sql_enquete_active_esp, array((int)$id_espece));
		$t = array();
		while ($r = self::fetch($q)) {
			$enq = new clicnat_enquete($db, $r['id_enquete']);
			$t[] = $enq->version($r['version']);
		}
		return $t;
	}

	/**
	 * @brief créer une nouvelle enquête
	 * @param $db ressource base de données
	 * @param $nom nom de la nouvelle enquête
	 * @return instance de l'enquête
	 */
	public static function ajouter($db, $nom) {
		self::cls($nom, self::except_si_vide);
		$id = self::nextval($db, 'enquete_def_id_enquete_seq');
		self::insert($db, self::table, ["id_enquete" => $id, "nom" => $nom]);
		return self::getInstance($db, $id);
	}

	public function taxons() {
		$q = bobs_qm()->query($this->db, 'enq_l_taxons', self::sql_l_taxons, [$this->id_enquete]);
		return new clicnat_iterateur_especes($this->db, array_column( self::fetch_all($q), 'id_espece'));
	}

	public function ajouter_taxon($id_espece) {
		return bobs_qm()->query($this->db, 'enq_i_taxon', self::sql_i_taxon, [$this->id_enquete, $id_espece]);
	}

	public function retirer_taxon($id_espece) {
		return bobs_qm()->query($this->db, 'enq_d_taxon', self::sql_d_taxon, [$this->id_enquete, $id_espece]);
	}
}
