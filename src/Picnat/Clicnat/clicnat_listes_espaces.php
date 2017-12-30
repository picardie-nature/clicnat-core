<?php
namespace Picnat\Clicnat;

/**
 * @brief liste d'espaces
 */
class clicnat_listes_espaces extends bobs_element {
	protected $id_liste_espace;
	protected $id_utilisateur;
	protected $nom;
	protected $ref;
	protected $mention;
	protected $attributs_defs;

	const __table__ = 'listes_espaces';
	const __prim__ = 'id_liste_espace';
	const __seq__ = 'listes_espaces_id_liste_espace_seq';
	const __datatable__ = 'listes_espaces_data';

	const sql_insert = 'insert into listes_espaces (id_utilisateur,nom) values ($1,$2)';
	const sql_suppr = 'delete from listes_espaces where id_liste_espace = $1';
	const sql_vider = 'delete from listes_espaces_data where id_liste_espace = $1';
	const sql_enlever = 'delete from listes_espaces_data where id_liste_espace = $1 and id_espace=$2';
	const sql_ajouter = 'insert into listes_espaces_data (id_liste_espace,id_espace,espace_table) values ($1,$2,bob_trouve_table_espace($3))';
	const sql_liste = 'select e.*,espace_table from espace e,listes_espaces_data l where e.id_espace=l.id_espace and l.id_liste_espace=$1 order by nom';
	const sql_liste_lu = 'select * from listes_espaces where id_utilisateur=$1 order by date_creation desc';
	const sql_liste_lp = 'select * from listes_espaces where ref=true order by nom';

	const sql_le_get_attr = 'select attributs from listes_espaces_data where id_liste_espace=$1 and id_espace=$2';
	const sql_le_set_attr = 'update listes_espaces_data set attributs=$3 where id_liste_espace=$1 and id_espace=$2';

	const sql_recherche = "select * from listes_espaces where nom ilike '%'||$1||'%'";

	const sql_liste_types_espace = "select distinct espace_table from listes_espaces_data where id_liste_espace=$1";

	public function __construct($db, $id) {
		parent::__construct($db, self::__table__, self::__prim__, $id);
		$this->champ_date_maj = 'date_maj';
	}

	public function __toString() {
		return $this->nom;
	}

	public function __get($prop) {
		switch ($prop) {
			case 'id_liste_espace':
				return $this->id_liste_espace;
			case 'id_utilisateur':
				return $this->id_utilisateur;
			case 'nom':
				return $this->nom;
			case 'ref':
				return $this->ref == 't';
			case 'mention':
				return $this->mention;
		}
		throw new Exception($prop.' pas accessible');
	}

	/**
	 * @brief recherche de liste à partir de leur nom
	 * @param $db handler connection
	 * @param $str texte recherché
	 * @return array de clicnat_listes_espaces
	 */
	public static function rechercher($db, $str) {
		self::cls($str);
		$str = str_replace("%"," ",$str);
		$q = bobs_qm()->query($db, 'l_espace_rech', self::sql_recherche, array($str));
		$t = array();
		while ($r = self::fetch($q)) {
			$t[] = new clicnat_listes_espaces($db, $r);
		}
		return $t;
	}

	/**
	 * @brief création d'une nouvelle liste d'espaces
	 * @param $db ressource
	 * @param $id_utilisateur (id du propriétaire)
	 * @param $nom nom de la liste
	 * @return le numéro de la nouvelle liste
	 */
	public static function creer($db, $id_utilisateur, $nom) {
		$data = array();
		$data[self::__prim__] = self::nextval($db, self::__seq__);
		$data['id_utilisateur'] = self::cli($id_utilisateur, self::except_si_inf_1);
		$data['nom'] = self::cls($nom, self::except_si_vide);
		parent::insert($db, self::__table__, $data);
		return $data[self::__prim__];
	}

	/**
	 * @brief dresse la liste des listes appartenant à un utilisateur
	 * @param $db ressource
	 * @param $id_utilisateur numéro de l'utilisateur propriétaire
	 * @return un tableau de lignes de la table listes_espaces
	 */
	public static function liste($db, $id_utilisateur) {
		$q = bobs_qm()->query($db, '_l_u_espa', self::sql_liste_lu, array($id_utilisateur));
		return self::fetch_all($q);
	}

	/**
	 * @brief dresse la liste des listes publiques
	 * @param $id_utilisateur numéro de l'utilisateur propriétaire
	 * @return un tableau de lignes de la table listes_espaces
	 */
	public static function listes_publiques($db) {
		$q = bobs_qm()->query($db, '_l_p_espa', self::sql_liste_lp, array());
		return self::fetch_all($q);
	}

	public static function liste_public($db) {
		return self::listes_publiques($db);
	}

	/**
	 * @brief vide la liste
	 */
	public function vider() {
		return bobs_qm()->query($this->db, '_lespa_vide', self::sql_vider, array($this->id_liste_espace));
	}

	public function enlever($id_espace) {
		return bobs_qm()->query($this->db, '_lespa_enlev', self::sql_enlever, array($this->id_liste_espace, (int)$id_espace));
	}


	/**
	 * @brief supprime la liste
	 */
	public function supprimer() {
		$this->vider();
		return bobs_qm()->query($this->db, '_lespa_suppr', self::sql_suppr, array($this->id_liste_espace));
	}


	/**
	 * @brief ajoute un espace à la liste
	 */
	public function ajouter($id_espace) {
		self::cli($id_espace, self::except_si_inf_1);
		return bobs_qm()->query($this->db, '_lesp_insta', self::sql_ajouter, [$this->id_liste_espace, $id_espace, $id_espace]);
	}

	/**
	 * @brief liste les espaces dans la liste
	 * @return un tableau de lignes de la table espaces
	 */
	public function liste_espaces() {
		$q = bobs_qm()->query($this->db, '__lespa_list', self::sql_liste, array($this->id_liste_espace));
		return self::fetch_all($q);
	}

	/**
	 * @brief liste le type de géométrie contenu dans la liste
	 * @return array
	 */
	public function liste_types_espace() {
		$q = bobs_qm()->query($this->db, '__ltesp_a', self::sql_liste_types_espace, array($this->id_liste_espace));
		$r = self::fetch_all($q);
		$types = array();

		$Point = 0;
		$LineString = 0;
		$MultiPolygon = 0;

		foreach ($r as $table) {
			switch ($table['espace_table']) {
				case 'espace_commune':
				case 'espace_littoral':
				case 'espace_polygon':
				case 'espace_corine':
				case 'espace_departement':
				case 'espace_l93_10x10':
				case 'espace_l93_5x5':
				case 'espace_structure':
					$MultiPolygon = 1;
					break;
				case 'espace_line':
				case 'espace_cours_eau':
					$LineString = 1;
					break;
				default:
					$Point = 1;
					break;
			}
		}
		$ret = array();
		if ($Point) $ret[] = 'Point';
		if ($LineString) $ret[] = 'LineString';
		if ($MultiPolygon) $ret [] = 'MultiPolygon';
		return $ret;
	}

	protected function parse($liste) {
		$a_min = ord(0);
		$a_max = ord(9);
		$tableau = array();
		$buffer = '';
		for ($p=0; $p<strlen($liste); $p++) {
			$c = $liste[$p];
			if ((ord($c) >= $a_min) && (ord($c) <= $a_max)) {
				$buffer .= $c;
			} else {
				if (!empty($buffer)) {
					$tableau[] = intval($buffer);
					$buffer = '';
				}
			}
		}
		if (!empty($buffer))
			$tableau[] = intval($buffer);

		return $tableau;
	}

	public function ajouter_liste_ids($liste) {
		$t = $this->parse($liste);
		foreach ($t as $l) {
			$this->ajouter($l);
		}
	}

	public function espaces() {
		return $this->get_espaces();
	}

	public function shp_englobant($srid=2154) {
		passthru(sprintf("%s %d %d %d", BIN_LISTE_ESPACES_SHP_ENGLOBANT, $this->id_liste_espace,$this->id_liste_espace,$srid));
	}

	public function get_espaces() {
		$liste = array();
		foreach ($this->liste_espaces() as $e) {
			$liste[] = array("id_espace"=>$e['id_espace'], "espace_table"=>$e['espace_table']);
		}
		return new clicnat_iterateur_espaces($this->db, $liste);
	}

	public function modifier_nom($nouveau_nom) {
		self::cls($nouveau_nom, self::except_si_vide);
		return $this->update_field('nom', $nouveau_nom);
	}

	public function modifier_mention($nouvel_mention) {
		self::cls($nouvel_mention, self::except_si_vide);
		return $this->update_field('mention', $nouvel_mention);
	}


	public function kml() {
		return $this->export_kml();
	}

	/**
 	 * @brief extraction au format kml des élements de la carte
	 * @return DOMDocument
	 */
	public function export_kml() {
		$doc = new DOMDocument();
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
		foreach ($this->attributs() as $attr) {
			$simplefield = $doc->createElement('SimpleField');
			$simplefield->setAttribute("type", $attr['type']);
			$simplefield->setAttribute("name", $attr['name']);
			$simplefield->appendChild($doc->createElement('displayName', $attr['name']));
			$schema->appendChild($simplefield);
		}
		$simplefield = $doc->createElement('SimpleField');
		$simplefield->setAttribute("type", "int");
		$simplefield->setAttribute("name", "id_espace");
		$simplefield->appendChild($doc->createElement('displayName', "id_espace"));
		$schema->appendChild($simplefield);

		$simplefield = $doc->createElement('SimpleField');
		$simplefield->setAttribute("type", "string");
		$simplefield->setAttribute("name", "reference");
		$simplefield->appendChild($doc->createElement('displayName', "reference"));
		$schema->appendChild($simplefield);

		$document->appendChild($schema);

		foreach ($this->get_espaces() as $espace) {
			$placemark = $doc->createElement('Placemark');
			$name = $doc->createElement('name');
			$name->appendChild($doc->createCDATASection($espace->__toString()));
			$placemark->appendChild($name);
			$geom_kml = $espace->get_geom_kml();
			$doc_geom = new DOMDocument();
			$doc_geom->loadXML($geom_kml);
			$placemark->appendChild($doc->importNode($doc_geom->firstChild, true));

			// Attributs supplémentaires
			// https://developers.google.com/kml/documentation/kmlreference#data
			$exd = $doc->createElement("ExtendedData");
			$attrs = $this->espace_attributs($espace->id_espace);
			if (is_array($attrs)) {
				$sdata = $doc->createElement('SchemaData');
				$sdata->setAttribute("schemaUrl", "#attributs_id");

				// ajout des attribut id_espace et reference
				$data = $doc->createElement("SimpleData", $espace->id_espace);
				$data->setAttribute("name", "id_espace");
				$sdata->appendChild($data);

				$data = $doc->createElement("SimpleData", $espace->reference);
				$data->setAttribute("name", "reference");
				$sdata->appendChild($data);

				foreach ($attrs as $k => $attr) {
					$data = $doc->createElement("SimpleData", $attr);
					$data->setAttribute("name", $k);
					$sdata->appendChild($data);
				}

				$exd->appendChild($sdata);
			}
			$placemark->appendChild($exd);
			$folder->appendChild($placemark);
		}
		$document->appendChild($folder);
		$kml->appendChild($document);
		$doc->appendChild($kml);
		return $doc;
	}

	/**
	 * @param $doc DOMDocument
	 */
	public static function import_kml_liste_champs($doc) {
		$t = array();
		foreach ($doc->getElementsByTagName('Schema') as $schema) {
			foreach ($schema->childNodes as $e) {
				if (get_class($e) != 'DOMElement') continue;
				$t[$e->getAttribute('name')] = $e->getAttribute('type');
			}
		}
		return $t;
	}

	public function import_kml($xml, $utilisateur, $champs=array()) {
		$tags = array('Polygon','LineString','Point');
		$doc = new DOMDocument();
		$doc->loadXML($xml);
		foreach ($tags as $tag) {
			$elems = $doc->getElementsByTagName($tag);
			foreach ($elems as $e) {
				$data = [
					'id_utilisateur' => $utilisateur->id_utilisateur,
					'reference' => '',
					'nom' => '?',
					'xml' => $doc->saveXML($e)
				];

				if (count($champs) > 0) {
					$attrs = [];
					$parentnode = $e->parentNode;
					while ($parentnode->tagName != 'Placemark') {
						bobs_log("Tagname : {$parentnode->tagName}");
						$parentnode = $parentnode->parentNode;
						if (!$parentnode)
							throw new Exception("Revenu au début du doc ? {$parentnode->tagName}");
					}
					foreach ($parentnode->getElementsByTagName('SchemaData') as $ed) {
						foreach ($ed->childNodes as $e_data) {
							if (get_class($e_data) != 'DOMElement')
								continue;
							$attrs[$e_data->getAttribute('name')] = $e_data->nodeValue;
						}
					}
					if (count($attrs) > 0) {
						foreach ($champs as $nom_champ_espace => $nom_champ_kml) {
							$data[$nom_champ_espace] = $attrs[$nom_champ_kml];
						}
					} else {
						bobs_log("pas d'attribut pour cet élément");
					}
				}

				$id_espace = false;

				switch ($e->tagName) {
					case 'Polygon':
						$id_espace = bobs_espace_polygon::insert_kml($this->db, $data);
						break;
					case 'LineString':
						$id_espace = bobs_espace_ligne::insert_kml($this->db, $data);
						break;
					case 'Point':
						$id_espace = bobs_espace_point::insert_kml($this->db, $data);
						break;
					default:
						throw new Exception("Type {$e->tagName} pas implémenté");
				}

				if ($id_espace) {
					$this->ajouter($id_espace);
				} else {
					throw new Exception('echec creation espace');
				}
			}
		}
	}

	/**
	 * @brief ajout d'un champ à la table attributaire de la liste
	 * @param $nom nom du champ
	 * @param $type type du champ pour produire kml https://developers.google.com/kml/documentation/kmlreference?hl=fr#schema
	 */
	public function attributs_def_ajout_champ($nom, $type, $valeurs) {
		self::cls($nom);

		$attrs = $this->attributs();

		if (count($attrs)>0) {
			foreach ($attrs as $attr) {
				if ($attr['name'] == $nom) {
					throw new Exception("nom déjà utilisé par un autre champ");
				}
			}
		}
		$attrs[] = array(
			"name" => $nom,
			"type" => $type,
			"values" => $valeurs,
			"active" => true
		);
		$this->update_field("attributs_defs", serialize($attrs));
	}

	/**
	 * @brief liste des attributs
	 * @brief array clés : name,type,values,active
	 */
	public function attributs() {
		if (isset($this->attributs_defs) && !empty($this->attributs_defs)) {
			return unserialize($this->attributs_defs);
		} else {
			return array();
		}
	}

	/**
	 * @brief retourne la valeur mini d'un attribut
	 * @param $attr le nom de l'attribut
	 * @return array(min,max)
	 */
	public function attribut_int_min_max($attr) {
		$min = null;
		$max = null;
		foreach ($this->espaces() as $e) {
			$es = $this->espace_attributs($e->id_espace);
			if (isset($es[$attr])) {
				if (is_null($min)) $min = $es[$attr];
				else $min = min($es[$attr], $min);

				if (is_null($max)) $max = $es[$attr];
				else $max = max($es[$attr], $max);
			}
		}
		return array($min,$max);
	}


	/**
	 * @brief retourne la liste triée des valeurs d'un attribut
	 * @param $attr le nom de l'attribut
	 * @return array(v1,v2...vn)
	 */
	public function attribut_int_liste_valeurs_triees($attr) {
		$valeurs = array();
		foreach ($this->espaces() as $e) {
			$es = $this->espace_attributs($e->id_espace);
			if (isset($es[$attr]))
				$valeurs[] = (int)$es[$attr];
		}
		sort($valeurs, SORT_NUMERIC);
		return $valeurs;
	}

	public function espace_attributs($id_espace) {
		$q = bobs_qm()->query($this->db, 'le_get_attr', self::sql_le_get_attr, array($this->id_liste_espace, $id_espace));
		$r = self::fetch($q);
		if (empty($r['attributs']))
			return array();
		return unserialize($r['attributs']);
	}

	public function espace_enregistre_attribut($id_espace, $nom_attribut, $valeur) {
		$attrs = $this->espace_attributs($id_espace);
		$attrs[$nom_attribut] = $valeur;
		return bobs_qm()->query($this->db, 'le_set_attr', self::sql_le_set_attr, array($this->id_liste_espace, $id_espace, serialize($attrs)));
	}

}
