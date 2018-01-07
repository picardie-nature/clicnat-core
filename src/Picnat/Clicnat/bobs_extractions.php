<?php
namespace Picnat\Clicnat;

use Picnat\Clicnat\ExtractionsConditions\bobs_extractions_conditions;
/**
 * @brief Recherche de citations
 */
class bobs_extractions extends bobs_tests {
	/**
	 *
	 * @var bobs_extractions_conditions[] Conditions de l'extraction
	 */
	public $conditions;
	public $tables;
	protected $limite;
	protected $db;

	/**
	 *
	 * @param ressource $db
	 */
	public function __construct($db) {
		$this->db = $db;
		$this->tables = array('citations', 'observations');
		$this->conditions = array();
		$this->limite = 0;
	}

	/**
	 * @param integer $n
	 */
	public function limite($n) {
		$this->limite = abs((int)$n);
	}

	protected function query($sql) {
		return bobs_element::query($this->db, $sql);
	}

	protected static function fetch($q) {
		return bobs_element::fetch($q);
	}

	protected static function fetch_all($q) {
		return bobs_element::fetch_all($q);
	}

	/**
	 * Ajoute une condition
	 *
	 * @param bobs_extrations_conditions $condition
	 * @return boolean
	 */
	public function ajouter_condition($condition) {
		if ($condition->ready()) {
			$condition->set_extraction($this);
			$this->conditions[] = $condition;
		} else {
			return false;
		}
		return true;
	}

	/**
	 * @brief Supprimer une condition de l'extraction
	 * @param integer $condition_index index de la condition
	 * @return bool
	 */
	public function retirer_condition($condition_index) {
		$n = count($this->conditions);
		unset($this->conditions[$condition_index]);
		return $n > count($this->conditions);
	}

	public function dans_un_tableau($inverse_ordre = false) {
		if (!$this->ready()) {
			return false;
		}

		$sql = "select distinct citations.id_citation, citations.id_observation,date_observation
				from {$this->get_tables()}
				where {$this->get_jointures()}
				{$this->get_conditions()}
				order by observations.date_observation";
		if ($inverse_ordre){
			$sql .= " desc";
		}
		$sql .=" ,citations.id_observation";
		if ($this->limite > 0) {
			$sql .= " limit {$this->limite}";
		}
		$q = $this->query($sql);
		return bobs_element::fetch_all($q);
	}

	public function mois_et_annees() {
		if (!$this->ready()) {
			return false;
		}
		$sql = "select extract(year from date_observation) as y,extract(month from date_observation) as m
				from {$this->get_tables()}
				where {$this->get_jointures()}
				{$this->get_conditions()}
				group by extract(year from date_observation),extract(month from date_observation)
				order by extract(year from date_observation),extract(month from date_observation)";
		$q = $this->query($sql);
		$annees = [];
		while ($r = self::fetch($q)) {
			if (!isset($annees[$r['y']]))
				$annees[$r['y']] = array($r['m']);
			else
				$annees[$r['y']][] = array($r['m']);
		}
		return $annees;
	}

	public function get_citations($inverse_ordre = false) {
		$cits = $this->dans_un_tableau($inverse_ordre);
		$tcit = array_column($cits, 'id_citation');
		unset($cits);
		return new clicnat_iterateur_citations($this->db, $tcit);
	}

	public function dans_selection($selection) {
		if (is_int($selection)) {
			$selection = get_selection($this->db, $selection);
		}
		$selection->change_extraction_xml($this->sauve_xml($selection->nom));

		if (!$this->ready()) {
			return false;
		}

		bobs_element::cli($id_selection);

		$i_commit = false;

		if (pg_transaction_status($this->db) != PGSQL_TRANSACTION_INTRANS) {
			$this->query('begin');
			$i_commit = true;
		}

		$sql = "insert into selection_data
				(id_selection, id_citation)
				select distinct {$selection->id_selection}, citations.id_citation
				from {$this->get_tables()}
				where {$this->get_jointures()}
				{$this->get_conditions()}";

		if ($this->limite > 0) {
			$sql .= " limit {$this->limite}";
		}

		$q = $this->query($sql);

		if ($i_commit) {
			$this->query('commit');
		}
	}

	public function carres($srid, $pas) {
		$sql = "select x0,y0,count(*) as n
			from espace_index_atlas eia,observations o,citations c,
				(select distinct citations.id_citation
					from {$this->get_tables()}
					where {$this->get_jointures()}
					{$this->get_conditions()}
				) as src
			where c.id_citation=src.id_citation
			and o.id_observation=c.id_observation
			and eia.id_espace=o.id_espace
			and srid = $1
			and pas = $2
			group by x0,y0";
		$q = bobs_qm()->query($this->db, md5($sql), $sql, [$srid,$pas]);
		return self::fetch_all($q);
	}


	/**
	 * @brief liste les carrés occupés par l'espèce pour l'extraction
	 * @param $pas pas de la grille
	 * @param $srid système de coordonnées de la grille
	 * @param $espece instance de l'espèce
	 * @return array x0,y0,count_citation
	 */
	public function carres_espece($pas,$srid,$espece) {
		$sql = "select x0,y0,count(distinct c.id_citation) as count_citation
		from citations c,observations o,espace_index_atlas ei,
			(select distinct citations.id_citation
				from {$this->get_tables()}
				where {$this->get_jointures()}
				{$this->get_conditions()}
			) as src
		where c.id_citation=src.id_citation
		and o.id_observation=c.id_observation
		and ei.id_espace=o.id_espace
		and c.id_espece=$1 and srid=$2 and pas=$3 group by x0,y0";

		$q = bobs_qm()->query($this->db, md5($sql), $sql, [$espece->id_espece,$srid,$pas]);
		return self::fetch_all($q);
	}


	/**
	 * @brief Compte le nombre de citations (sans appeler ready()) pour éviter les boucles
	 * @return int
	 */
	protected function compte_ready() {
		$sql = "select count(distinct citations.id_citation) as n
				from {$this->get_tables()}
				where {$this->get_jointures()}
				{$this->get_conditions()}";
		$q = $this->query($sql);
		$r = self::fetch($q);
		return $r['n'];
	}

	/**
	 * @brief retourne le code sql généré
	 */
	public function apercu_sql() {
		$sql = "select count(distinct citations.id_citation) as n
				from {$this->get_tables()}
				where {$this->get_jointures()}
				{$this->get_conditions()}";
		if ($this->limite > 0) {
			$sql .= " limit {$this->limite}";
		}
		return $sql;
	}

	public function compte_citations_par_espece() {
		$sql = "select count(distinct citations.id_citation),citations.id_espece
				from {$this->get_tables()}
				where {$this->get_jointures()}
				{$this->get_conditions()}
				group by citations.id_espece";
		$q = $this->query($sql);
		$t = [];
		while ($r = self::fetch($q)) {
			$t[$r['id_espece']] = $r['count'];
		}
		return $t;
	}

	public function especes() {
		$sql = "select especes.id_espece from (select distinct citations.id_espece
				from {$this->get_tables()}
				where {$this->get_jointures()}
				{$this->get_conditions()}
				group by citations.id_espece) as sq, especes
				where especes.id_espece=sq.id_espece
				order by classe,systematique,nom_f,nom_s";
		$q = $this->query($sql);
		$ids_espece = array();
		while ($r = self::fetch($q)) {
			$ids_espece[] = $r['id_espece'];
		}
		return new clicnat_iterateur_especes($this->db, $ids_espece);
	}

	/**
	 * @brief Compte le nombre de citations
	 * @return int
	 */
	public function compte() {
		if (!$this->ready()) {
			return false;
		}
		return $this->compte_ready();
	}

	/**
	 * Le résultat de l'extraction dans une nouvelle table temporaire
	 *
	 * @param string $table le nom de la nouvelle table
	 */
	public function dans_table_temporaire($table) {
		if (pg_transaction_status($this->db) != PGSQL_TRANSACTION_INTRANS) {
			throw new \Exception('doit être utilisé dans une transaction');
		}

		$sql = "select distinct citations.id_citation into temporary $table
				from {$this->get_tables()}
				where {$this->get_jointures()}
				{$this->get_conditions()}";

		$this->query($sql);
	}

	/**
	 * @brief Mise a disposition de données (insertions dans utilisateur_citations_ok)
	 *
	 * @see bobs_extractions::dans_table_temporaire()
	 *
	 * @param unknown_type $id_utilisateur
	 * @param unknown_type $table
	 */
	public function autorise_utilisateur_table_temporaire($id_utilisateur, $table, $position=0) {
		self::cli($id_utilisateur);
		self::cli($position);

		if (pg_transaction_status($this->db) != PGSQL_TRANSACTION_INTRANS) {
			throw new \Exception('doit être utilisé dans une transaction');
		}

		$sql = "insert into utilisateur_citations_ok (id_utilisateur,id_citation)
				select $id_utilisateur,id_citation from $table
				where not exists(
					select 1 from utilisateur_citations_ok
					where utilisateur_citations_ok.id_citation=$table.id_citation
					and utilisateur_citations_ok.id_utilisateur=$id_utilisateur
				)
				and id_citation>$position";
		$this->query($sql);
	}

	/**
	 * @brief Autorise l'utilisateur a accéder au résultat de cette requête
	 * @param integer $id_utilisateur
	 */
	public function autorise_utilisateur($id_utilisateur,$position=0) {
		self::cli($id_utilisateur);

		if (!$this->ready()) {
			return false;
		}

		$sql = "insert into utilisateur_citations_ok (id_utilisateur,id_citation)
				select $id_utilisateur, citations.id_citation
				from {$this->get_tables()}
				where {$this->get_jointures()}
				{$this->get_conditions()}
				and not exists(
					select 1 from utilisateur_citations_ok
					where utilisateur_citations_ok.id_citation=citations.id_citation
					and utilisateur_citations_ok.id_utilisateur=$id_utilisateur
				)
				and citations.id_citation>$position";

		$this->query($sql);

		return true;
	}

	public function ready($n=0) {
		if (is_array($this->conditions)) {
			if (count($this->conditions) > $n) {
				return true;
			}
		}
		return false;
	}

	/**
	 * en fonction des tables déjà utilisées ajoute les dépendances
	 */
	private function solve_table_deps() {
		$this->tables = ['observations','citations'];

		$deps = [
			'citations' => null,
			'especes' => 'citations',
			'observations' => 'citations',
			'observations_observateurs' => 'observations',
			'utilisateur' => 'observations_observateurs',
			'citations_tags' => 'citations',
			'tags' => 'citations_tags',
			'utilisateur_citations_ok' => 'observations_observateurs',
			'referentiel_regional' => 'citations',
			'espace_point' => 'observations',
			'espace_polygon' => 'observations',
			'espace_chiro' => 'observations',
			'espace_line' => 'observations',
			'espace_commune' => 'observations',
			'espace_l93_10x10' => null,
			'espace_zps' => null,
			'espace_zsc' => null,
			'espace_epci' => null,
			'espace_structure' => null,
			'citations_tags' => 'citations',
			'citations_documents' => 'citations',
			'listes_especes_data' => 'citations',
			'espace_intersect' => 'observations',
			'espace_index_atlas' => 'observations',
			'selection_data' => 'citations'
		];

		foreach ($this->conditions as $cond) {
			$tables =  $cond->get_tables();
			if (!is_array($tables)) {
				throw new Exception('doit etre un tableau (class: '.get_class($cond).')');
			}
			$this->tables = array_merge($tables, $this->tables);
		}

		$this->tables = array_unique($this->tables);
		$not_done = true;
		while ($not_done) {
			$not_done = false;
			foreach ($this->tables as $table) {
				if (!array_key_exists($table, $deps))
				throw new Exception('table inconnue "'.$table.'"');

				$table_dependante = $deps[$table];

				if (empty($table)) {
					throw new \Exception('table vide');
				}
				if (is_null($table_dependante)) {
					continue;
				}

				if (in_array($deps[$table], $this->tables) === false) {
					$not_done = true;
					$this->tables[] = $deps[$table];
					break;
				}
			}
		}
	}

	/**
	 * @brief liste des tables utilisées dans la requête séparée par des virgules
	 * @return string
	 */
	protected function get_tables() {
		$this->solve_table_deps();
		$r = '';
		foreach ($this->tables as $table) {
			$r .= $table.',';
		}
		return trim($r, ',');
	}

	/**
	 * @brief les jointures de la requête
	 * @return string
	 */
	protected function get_jointures() {
		$r = '';
		$this->solve_table_deps();
		foreach ($this->tables as $table) {
			if (!empty($r)) {
				$r .= ' and ';
			}
			$r .= $this->get_table_jointure($table)."\n";
		}
		return $r;
	}

	/**
	 * @brief les conditions de la requête
	 * @return string
	 */
	protected function get_conditions() {
		$t = [];
		foreach ($this->conditions as $condition) {
			$classe = get_class($condition);
			if (!isset($t[$classe]))
			$t[$classe] = array();
			$t[$classe][] = $condition->get_sql();
		}
		$r = '';
		foreach (array_keys($t) as $classe) {
			$s_ou = '';
			foreach ($t[$classe] as $sql_condition) {
				if (!empty($s_ou))
				$s_ou .= ' or ';
				$s_ou .= $sql_condition;
			}
			$r .= ' and ('.$s_ou.') ';
		}
		unset($t);
		return $r;
	}

	private function get_table_jointure($table) {
		switch (trim($table)) {
			case 'citations':
				return '1=1';
			case 'especes':
				return 'citations.id_espece=especes.id_espece';
			case 'observations':
				return 'citations.id_observation=observations.id_observation';
			case 'observations_observateurs':
				return 'observations.id_observation=observations_observateurs.id_observation
						and observations.brouillard = false';
			case 'utilisateur':
				return 'observations_observateurs.id_utilisateur=utilisateur.id_utilisateur';
			case 'citations_tags':
				return 'citations.id_citation=citations_tags.id_citation';
			case 'citations_documents':
				return 'citations.id_citation=citations_documents.id_citation';
			case 'tags':
				return 'citations_tags.id_tag=tags.id_tag';
			case 'utilisateur_citations_ok':
				return 'utilisateur_citations_ok.id_citation=citations.id_citation';
			case 'referentiel_regional':
				return 'citations.id_espece=referentiel_regional.id_espece';
			case 'espace_point':
				return 'observations.id_espace=espace_point.id_espace and observations.espace_table=\'espace_point\'';
			case 'espace_line':
				return 'observations.id_espace=espace_line.id_espace and observations.espace_table=\'espace_line\'';
			case 'espace_polygon':
				return 'observations.id_espace=espace_polygon.id_espace and observations.espace_table=\'espace_polygon\'';
			case 'espace_chiro':
				return 'observations.id_espace=espace_chiro.id_espace and observations.espace_table=\'espace_chiro\'';
			case 'espace_commune':
				return 'observations.id_espace=espace_commune.id_espace and observations.espace_table=\'espace_commune\'';
			case 'listes_especes_data':
				return 'citations.id_espece=listes_especes_data.id_espece';
			case 'espace_intersect':
				return 'observations.id_espace=espace_intersect.id_espace_obs';
			case 'espace_index_atlas':
				return 'observations.id_espace=espace_index_atlas.id_espace';
			case 'selection_data':
				return 'selection_data.id_citation=citations.id_citation';
			default:
				return '2=2';
		}
	}

	/**
	 * Donne la liste des conditions disponnibles
	 * @return array
	 */
	public static function get_conditions_dispo($forcer_chargement_classes=false) {
		static $conditions;
		if (!isset($conditions) || $forcer_chargement_classes) {
			// on veut voir toutes les classes donc on va forcer le chargement
			foreach (glob(__DIR__."/ExtractionsConditions/bobs_ext_c*.php") as $f) {
				require_once($f);
			}

			foreach (get_declared_classes() as $c) {
				// test si visible dans le QG
				$conditionClass = new \ReflectionClass($c);
				if (!$conditionClass->isSubclassOf(bobs_extractions_conditions::class)) {
					continue;
				}
				if ($conditionClass->getConstant("qg")) {
					$get_titre_method = new \ReflectionMethod($c , "get_titre");
					if (!$get_titre_method) {
						$conditions[$c] = "sans titre (missing get_titre())";
					} else {
						$get_titre = $get_titre_method->getClosure();
						$conditions[$c] = $get_titre();
					}
				}
			}
		}
		return $conditions;
	}

	public function get_db() {
		return $this->db;
	}

	public function set_db($db) {
		$this->db = $db;
	}

	public function sauve_xml($nom_extraction) {
		$doc = new \DOMDocument('1.0');
		$doc->formatOutput = true;
		$ext = $doc->createElement('extraction');
		$ext->setAttribute('version', '1.0');
		$ext->appendChild($doc->createElement('nom', $nom_extraction));
		$ext->appendChild($doc->createElement('date_creation', strftime('%Y-%m-%d %H:%M:%S')));
		$conditions = $doc->createElement('conditions');
		foreach ($this->conditions as $condition) {
			$condition->sauve_xml($doc, $conditions);
		}
		$ext->appendChild($conditions);
		$doc->appendChild($ext);
		return $doc->saveXML();
	}

	public function sauve_xml_html_preview() {
		return htmlentities($this->sauve_xml('test'));
	}

	public static function extrait_nom_xml($xml) {
		$doc = new \DOMDocument('1.0');
		$doc->loadXML($xml);

		$xpath = new \DOMXPath($doc);
		$entries = $xpath->query('//extraction/nom');
		foreach ($entries as $e) {
			return $e->nodeValue;
		}
	}

	/**
	 * @brief mise en place des conditions à partir d'un doc xml
	 * @param $db handler db
	 * @param $xml document xml (texte)
	 * @param $id_utilisateur (restrictions compte utilisateur "poste"
	 * @param $extraction instance d'un objet bobs_extractions
	 * @return bobs_extractions
	 */
	public static function charge_xml($db, $xml, $id_utilisateur=false, $extraction=null) {
		if (is_null($extraction)) {
			if (!$id_utilisateur) {
				$extraction = new bobs_extractions($db);
			} else {
				$extraction = new bobs_extractions_poste($db, $id_utilisateur);
			}
		}
		$doc = new \DOMDocument('1.0');
		$doc->loadXML($xml);

		$xpath = new \DOMXPath($doc);
		$entries = $xpath->query('//extraction/conditions/condition');
		foreach ($entries as $entry) {
			$classe = null;
			$arguments = [];
			foreach ($entry->childNodes as $prop) {
				if ($prop->nodeName == 'classe') {
					$classe = $prop->nodeValue;
				} else if ($prop->nodeName == 'argument') {
					$k = $prop->getAttribute('nom');
					$v = $prop->nodeValue;
					$arguments[$k] = $v;
				}
			}
			if (!is_subclass_of($classe, 'bobs_extractions_conditions')) {
				throw new \Exception('classe pas autorisée : '.$classe);
			}
			$obj_ext = false;
			eval("\$obj_ext = $classe::new_by_array(\$arguments);");
			if ($obj_ext) {
				$extraction->ajouter_condition($obj_ext);
			}
		}
		return $extraction;
	}
}
