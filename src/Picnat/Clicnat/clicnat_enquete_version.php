<?php
namespace Picnat\Clicnat;

class clicnat_enquete_version {
	protected $id_enquete;
	protected $version;
	protected $definition;
	protected $db;

	private $champs;
	private $__cache_champs_from_def;

	const sql_liste_champs = "select unnest(xpath('/enquete/champ/@nom', definition)) as champs from enquete_def_version where id_enquete=$1 and version=$2";
	const sql_sauve_def = 'update enquete_def_version set definition=xmlparse(document $3) where id_enquete=$1 and version=$2';
	const sql_nombre_champs = 'select array_length(xpath(\'/enquete/champ\', definition),1) as n from enquete_def_version where id_enquete=$1 and version=$2';
	const sql_champs = 'select x[i] as champ from (select generate_subscripts(xpath(\'/enquete/champ\', definition),1) as i,xpath(\'/enquete/champ\', definition) as x from enquete_def_version where id_enquete=$1 and version=$2) as s';

	public function __construct($db, $id_enquete, $version, $definition=null) {
		$this->db = $db;
		$this->id_enquete = $id_enquete;
		$this->version = $version;
		$this->definition = $definition;
	}

	public static function getInstance($db, $id, $version) {
		static $instances;

		if (!isset($instances))
			$instances = [];

		if (!isset($instances["{$id}.{$version}"]))
			$instances["{$id}.{$version}"] = new self($db, $id, $version);

		return $instances["{$id}.{$version}"];
	}

	public static function getInstanceFromXML($db, $xml) {
		if (empty($xml))
			return false;
		$resultat = new DOMDocument();
		$resultat->loadXML($xml);
		$xml_id_enquete = $resultat->firstChild->getAttribute("id_enquete");
		$xml_version = $resultat->firstChild->getAttribute("version");
		if (empty($xml_id_enquete) || empty($xml_version))
			return false;
		return self::getInstance($db, $xml_id_enquete, $xml_version);
	}


	public function enquete() {
		return clicnat_enquete::getInstance($this->db, $this->id_enquete);
	}

	public function __get($champ) {
		switch ($champ) {
			case 'id_enquete':
				return $this->id_enquete;
			case 'version':
				return $this->version;
			case 'definition':
				return $this->definition;
		}
	}

	public function sauve_definition($definition) {
		$def = '<?xml version="1.0"?>'.$definition;
		$q = bobs_qm()->query($this->db, 'sql_enq_v_sauve_d', self::sql_sauve_def, [$this->id_enquete, $this->version, $def]);
		unset($this->champs);
		if ($q) $this->definition = $definition;
	}

	public function nombre_de_champs() {
		$q = bobs_qm()->query($this->db, 'sql_enq_v_n_champ', self::sql_nombre_champs, [$this->id_enquete, $this->version]);
		$r = bobs_element::fetch($q);
		return $r['n'];
	}

	/**
	 * @brief Code XML de chaque champ
	 */
	public function champs_xml() {
		$q = bobs_qm()->query($this->db, 'sql_enq_v_champs', self::sql_champs, [$this->id_enquete, $this->version]);
		$t = array();
		while ($r = bobs_element::fetch($q)) {
			$t[] = $r['champ'];
		}
		return $t;
	}

	public function champs_xml_debug_html() {
		$champs = $this->champs_xml();
		foreach ($champs as $k=>$v) {
			$champs[$k] = htmlentities($v,null,'UTF-8');
			self::champ_obj($v);
		}
		return $champs;
	}

	public function champs() {
		if (!isset($this->champs))
			$this->champs = $this->champs_xml();

		return $this->champs;
	}

	/**
	 * @param $id_citation permet d'initialiser le formulaire
	 */
	public function formulaire($id_citation=false) {
		$champs = $this->champs_xml();
		$citation = false;
		$valeurs_init = array();
		if ($id_citation) {
			$citation = get_citation($this->db, (int)$id_citation);
			$doc = new DOMDocument('1.0', 'UTF-8');
			@$doc->loadXML($citation->enquete_resultat);
			$champs_resultat = $doc->getElementsByTagName('champ');
			foreach ($champs_resultat as $champ_resultat) {
				$nom = $champ_resultat->getAttribute('nom');
				if ($champ_resultat->getAttribute('type') == 'multiple') {
					$valeurs_init[$nom] = array();
					if ($champ_resultat->hasChildNodes())
					foreach ($champ_resultat->childNodes as $valeur) {
						if ($valeur->tagName == 'valeur')
							$valeurs_init[$nom][] = $valeur->nodeValue;
					}
				} else {
					$valeurs_init[$nom] = $champ_resultat->getAttribute('valeur');
				}
			}
		}

		foreach ($champs as $k=>$v) {
			$champ = self::champ_obj($v);
			if ($champ) {
				echo "<div class=\"directive\">{$champ->lib()}</div>";
				$vi = array_key_exists($champ->nom, $valeurs_init)?$valeurs_init[$champ->nom]:false;
				echo "<div class=\"valeur\">{$champ->formulaire($vi)}</div>";
			}
		}
	}

	/**
	 * @brief création d'un instance d'un champ à partir de son code xml
	 */
	public static function champ_obj($xml) {
		$doc = new DOMDocument('1.0', 'UTF-8');
		$doc->loadXML($xml);
		switch ($doc->documentElement->getAttribute('type')) {
			case 'clc':
				return new clicnat_enquete_champ_clc($doc);
			case 'tag':
				return new clicnat_enquete_champ_tag($doc);
			case 'liste_choix':
				return new clicnat_enquete_champ_liste_choix($doc);
			case 'entier':
				return new clicnat_enquete_champ_entier($doc);
			case 'texte':
				return new clicnat_enquete_champ_texte($doc);
			case 'liste_choix_multiple':
				return new clicnat_enquete_champ_choix_multiple($doc);
			default:
				throw new Exception('type de champ inconnu');
		}
		return false;
	}

	public function resultat_enregistre($id_citation, $data) {
		$doc_resultat = new DOMDocument('1.0','UTF-8');
		$doc_resultat->formatOutput=true;
		$enq_res = $doc_resultat->createElement('enquete_resultat');
		$enq_res->setAttribute('id_enquete', $this->id_enquete);
		$enq_res->setAttribute('version', $this->version);
		foreach ($this->champs() as $c) {
			$champ = self::champ_obj($c);
			$e_champ = $doc_resultat->createElement('champ');
			$champ->doc_champ_sauve($e_champ, $data);
			$enq_res->appendChild($e_champ);
		}
		$doc_resultat->appendChild($enq_res);
		return $doc_resultat;
	}

	private function export_csv_ligne_titre() {
		$titres = bobs_citation::get_ligne_array_titre();
		foreach ($this->champs_xml() as $champ_xml) {
			$champ = self::champ_obj($champ_xml);
			$titres[] = $champ->lib;
		}
		$titres[]='x';
		$titres[]='y';
		return $titres;
	}

	public function citations() {
		$extraction = new bobs_extractions($this->db);
		$extraction->ajouter_condition(new bobs_ext_c_enquete_version($this->id_enquete, $this->version));
		return $extraction->get_citations();
	}

	public function liste_champs_depuis_def() {
		if (empty($this->__cache_champs_from_def)) {
			$q = bobs_qm()->query($this->db, "enq_l_champs_xml", self::sql_liste_champs, [$this->id_enquete, $this->version]);
			$this->__cache_champs_from_def = array_column(bobs_element::fetch_all($q), "champs");
		}
		return $this->__cache_champs_from_def;
	}

	/**
	 * @brief retourne un tableau associatif des réponses
	 * @param $citation instance de bobs_citation
	 * @return array
	 */
	public function citation_reponses($citation) {
		if (empty($citation->enquete_resultat))
			return false;
		$ret = [];
		$resultat = new DOMDocument();
		$resultat->loadXML($citation->enquete_resultat);
		$xml_id_enquete = $resultat->firstChild->getAttribute("id_enquete");
		$xml_version = $resultat->firstChild->getAttribute("version");

		if (($xml_id_enquete != $this->id_enquete) || ($xml_version != $this->version))
			return false;

		$xpath = new DOMXpath($resultat);
		foreach ($this->champs_xml() as $champ_xml) {
			$champ = self::champ_obj($champ_xml);
			$elems = $xpath->query("//champ[@nom='{$champ->nom}']");
			$v = "";
			foreach ($elems as $e) {
				switch ($e->getAttribute('type')) {
					case 'simple':
						$v = $e->getAttribute('valeur');
						break;
					case 'multiple':
						$l = '';
						foreach ($e->childNodes as $c) {
							if ($c->nodeName == 'valeur') {
								$l .= trim("{$c->nodeValue}").";";
							}
						}
						$v = trim($l,';');
						break;
					default:
						throw new Exception('Pas simple ni multiple ?');

				}
				break;
			}
			$ret[$champ->nom] = $v;
		}
		return $ret;
	}

	public function csv($fh) {
		fputcsv($fh, $this->export_csv_ligne_titre());
		foreach ($this->citations() as $citation) {
			$ligne = $citation->get_ligne_array();
			// ajouter les champs de l'enquete ici
			$resultat = new DOMDocument();
			$resultat->loadXML($citation->enquete_resultat);
			$xpath = new DOMXpath($resultat);
			foreach ($this->champs_xml() as $champ_xml) {
				$champ = self::champ_obj($champ_xml);
				$elems = $xpath->query("//champ[@nom='{$champ->nom}']");
				foreach ($elems as $e) {
					switch ($e->getAttribute('type')) {
						case 'simple':
							$ligne[] = $e->getAttribute('valeur');
							break;
						case 'multiple':
							$l = '';
							foreach ($e->childNodes as $c) {
								if ($c->nodeName == 'valeur') {
									$l .= trim("{$c->nodeValue}").";";
								}
							}
							$ligne[] = trim($l,';');
							break;
						default:
							throw new Exception('Pas simple ni multiple ?');

					}
					break;
				}

			}
			$espace = $citation->get_observation()->get_espace();
			if (is_subclass_of($espace, 'bobs_espace_point') or (get_class($espace) == 'bobs_espace_point')) {
				$ligne[] = sprintf("%F", $espace->get_x());
				$ligne[] = sprintf("%F", $espace->get_y());
			} else {
				$ligne[] = "undef";
				$ligne[] = "undef";
			}
			fputcsv($fh, $ligne);
		}
	}
}
