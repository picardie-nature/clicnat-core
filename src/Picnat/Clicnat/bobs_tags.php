<?php
namespace Picnat\Clicnat;

class bobs_tags extends bobs_element {
	protected $id_tag;
	protected $parent_id;
	protected $lib;
	protected $ref;
	protected $a_chaine;
	protected $a_entier;
	protected $categorie_simple;

	protected $borne_a;
	protected $borne_b;

	protected $classes_esp_ok;

	const ref_tag_saisie_citation = 'CIOK';

	public function __construct($db, $id) {
		parent::__construct($db, 'tags', 'id_tag', $id);

		$this->a_chaine = $this->a_chaine == 't';
		$this->a_entier = $this->a_entier == 't';
		$this->categorie_simple = $this->categorie_simple == 't';
	}

	/**
	 * @brief test si l'espèce association du tag avec une espèce est valable
	 * @return boolean
	 */
	public function test_association_espece($espece) {
		return strpos($this->classes_esp_ok, $espece->classe) !== false;
	}

	public function __get($prop) {
		switch($prop) {
			case 'id_tag':
				return $this->id_tag;
			case 'parent_id':
				return $this->parent_id;
			case 'lib':
				return $this->lib;
			case 'ref':
				return $this->ref;
			case 'a_chaine':
				return $this->a_chaine == 't';
			case 'a_entier':
				return $this->a_entier == 't';
			case 'categorie_simple':
				return $this->categorie_simple == 't';
			case 'borne_a':
				return $this->borne_a;
			case 'borne_b':
				return $this->borne_b;
			case 'classes_esp_ok':
				return $this->classes_esp_ok;
			default:
				throw new Exception('propriété inconnu '.$prop);
		}
	}

	public function __toString() {
		if (empty($this->lib))
			return 'Tag #'.$this->id_tag;
		return $this->lib;
	}

	public static function insert($db, $args) {
		$parent_id = $args['parent_id'];
		$lib = $args['lib'];

		self::cli($parent_id, false);
		self::cls($lib);

		if (empty($lib))
			throw new Exception('$args[lib] is empty !');

		$args['a_chaine'] = $args['a_chaine']?true:false;
		$args['a_entier'] = $args['a_entier']?true:false;
		$args['categorie_simple'] = $args['categorie_simple']?true:false;

		$id = self::nextval($db, 'tags_id_tag_seq');
		parent::insert($db, 'tags', array(
				'id_tag' => $id,
				'parent_id' => $parent_id,
				'lib' => $lib,
				'ref' => $args['ref'],
				'a_chaine' => self::truefalse($args['a_chaine']),
				'a_entier' => self::truefalse($args['a_entier']),
				'categorie_simple' => self::truefalse($args['categorie_simple'])
			)
		);
		return $id;
	}

	/**
	 * @todo a enlever et remplacer par update_field
	 */
	public function update($champ, $valeur) {
		switch ($champ) {
			case 'lib':
			case 'ref':
			case 'classes_esp_ok':
				self::cls($valeur, self::except_si_vide);
				break;
			case 'parent_id':
				self::cli($valeur);
				break;
			case 'a_chaine':
			case 'a_entier':
			case 'categorie_simple':
				$valeur = $valeur?1:0;
				break;
			default:
				throw new Exception('pas prévu');
		}
		$sql = "update tags set {$champ} = $2 where id_tag = $1";
		$q = bobs_qm()->query($this->db, 'upd_tag_'.$champ, $sql, array($this->id_tag, $valeur));
		if ($q) {
			$this->$champ = $valeur;
			if ($champ == 'parent_id')
				self::calculer_bornes($this->db);
		}
		return $q;
	}

	public function set_parent($id_parent) {
		$this->update('parent_id', $id_parent);
	}

	public static function insert_node($db, $parent_id, $lib, $ref='', $have_string=false, $have_integer=false, $category=false) {
		self::cli($parent_id, false);
		if (empty($parent_id))
			$parent_id = null;
		self::cls($lib);
		self::cls($ref);

		if (empty($lib))
			throw new InvalidArgumentException('$lib is empty');

		self::insert($db, array(
			'parent_id' => $parent_id,
			'lib' => $lib,
			'ref' => $ref,
			'a_chaine' => $have_string,
			'a_entier' => $have_integer,
			'categorie_simple' => $category
		));

		self::calculer_bornes($db);
	}

	private static function get_nodes_where($db, $sql_part, $args) {
		if (!is_array($args))
			throw new InvalidArgumentException('$args doit être un tableau');

		$q_sql = '';
		$q_name = 'gnw_'.md5($sql_part);
		$qm = bobs_qm();

		if (!$qm->ready($q_name))
			$q_sql = sprintf('select * from tags where %s order by lib', $sql_part);

		$q = $qm->query($db, $q_name, $q_sql, $args);

		return self::fetch_all($q);
	}

	public function get_childs() {
		if (!$this->have_childs())
			return array();
		return self::get_nodes_where($this->db, 'parent_id = $1', array($this->id_tag));
	}

	/**
	 * @brief test s'il y a des enfants (faux si les bornes ne sont pas à jour)
	 * @return boolean
	 */
	public function have_childs() {
		return (($this->borne_b - $this->borne_a) > 0) or (empty($this->borne_a)) or (empty($this->borne_b));
	}

	const sql_sous_arbre = 'select * from tags where borne_a > $1 and borne_b < $2';

	/**
	 * @brief tous les tags en dessous de celui-ci (sur plusieurs niveaux)
	 * @return un tableau de lignes tags
	 */
	public function sous_arbre() {
		$q = bobs_qm()->query($this->db, 'tags_g_sous_arbre', array($this->borne_a, $this->borne_b));
		return self::fetch_all($q);
	}

	public function get_neighbors() {
		return self::get_nodes_where($this->db, 'parent_id = $1', array($this->id_parent));
	}

	public static function get_roots($db) {
		return self::get_nodes_where($db, 'parent_id is null and 1=$1', array(1));
	}

	public function get_html_tree() {
		$s = $this->lib.'<ul>';
		$vide = $s;
		$tags = self::get_childs($this->db, $this->id_tag);

		if (count($tags) > 0)
		foreach ($tags as $tag) {
			$s .= '<li>';
			$otag = new bobs_tags($this->db, $tag);
			$s .= $otag->get_html_tree();
			$s .= '</li>';
		}
		return $s==$vide?'<span id="'.$this->id_tag.'">'.$this->lib.'</span>':$s.'</ul>';
	}

	public static function by_ref($db, $ref) {
		self::cls($ref);
		if (empty($ref))
			throw new InvalidArgumentException('$ref est vide');

		$q = bobs_qm()->query($db, 'tag_by_ref', 'select * from tags where ref=$1', array($ref));
		$tag = new bobs_tags($db, self::fetch($q));

		try {
			self::cli($tag->id_tag);
			if ($tag->id_tag == 0)
				throw new Exception('not found');
		} catch (Exception $e) {
			throw new Exception($ref.' : unknown reference');
		}
		return $tag;
	}

	public function get_v_text_values($table) {
		$sql = 'select distinct v_text from '.$table.' where id_tag=$1';
		$q = bobs_qm()->query($this->db, 'tag_g_values_'.$table, $sql, array($this->id_tag));
		return self::fetch_all($q);
	}

	const sql_set_borne_a = 'update tags set borne_a=$2 where id_tag=$1';
	const sql_set_borne_b = 'update tags set borne_b=$2 where id_tag=$1';

	private function set_borne($borne, $valeur) {
		self::cls($borne);
		self::cli($valeur);
		echo $this;
		echo " $borne = $valeur\n";
		if (($borne == 'a') or ($borne == 'b')) {
			if ($borne == 'a')
				bobs_qm()->query($this->db, 'tag_s_borne_a', self::sql_set_borne_a, array($this->id_tag, $valeur));
			else
				bobs_qm()->query($this->db, 'tag_s_borne_b', self::sql_set_borne_b, array($this->id_tag, $valeur));
		} else {
			throw new Exception('borne=a ou borne=b !');
		}
	}

	private static function calculer_bornes_rec($db, $borne, $id_tag) {
		$tag = new bobs_tags($db, $id_tag);
		$tag->set_borne('a', $borne++);
		$childs = $tag->get_childs($db);
		if (count($childs) > 0) {
			foreach ($childs as $child) {
				$borne = self::calculer_bornes_rec($db, $borne, $child['id_tag']);
			}
		}
		$tag->set_borne('b', $borne++);
		return $borne;
	}

	public static function calculer_bornes($db) {
		$roots = self::get_roots($db);
		$borne = 0;
		foreach ($roots as $root) {
			$tag = new bobs_tags($db, $id_tag);
			$tag->set_borne('a', $borne++);
			$borne = self::calculer_bornes_rec($db, $borne, $root['id_tag']);
			$tag->set_borne('b', $borne++);
		}
	}

	const sql_recherche = 'select * from bob_recherche_tag_lib($1,$2,$3)';

	private static function recherche($db, $texte, $bmin, $bmax) {
		self::cls($texte);
		self::cli($bmin);
		self::cli($bmax);

		$tags = array();
		$q = bobs_qm()->query($db, 'tag_rech_ft', self::sql_recherche, array($texte, $bmin, $bmax));
		while ($r = self::fetch($q)) {
			$tags[] = new bobs_tags($db, $r);
		}
		return $tags;
	}

	public static function recherche_tag_citation($db, $texte) {
		$tag = self::by_ref($db, self::ref_tag_saisie_citation);
		return self::recherche($db, $texte, $tag->borne_a, $tag->borne_b);
	}

	const sql_especes = 'select distinct especes.id_espece, especes.classe,especes.ordre,especes.nom_f,especes.nom_s
				from citations_tags,citations,especes
				where especes.id_espece=citations.id_espece
				and citations_tags.id_citation=citations.id_citation
				and citations_tags.id_tag=$1
				order by especes.classe,especes.ordre,especes.nom_f,especes.nom_s';
	/**
	 * @brief liste les espèces où l'étiquette est utilisée
	 * @return clicnat_iterateur_especes
	 */
	public function especes() {
		$q = bobs_qm()->query($this->db, 'tag_especes', self::sql_especes, array($this->id_tag));
		$t = array();
		while ($r = self::fetch($q)) {
			$t[] = $r['id_espece'];
		}
		return new clicnat_iterateur_especes($this->db, $t);
	}

	/**
	 * @brief liste les classes d'espèce qui peuvent être associé à cette étiquette
	 * @return un table d'objet bobs_classe
	 */
	public function classes() {
		$t = array();
		if (!empty($this->classes_esp_ok)) {
			foreach (str_split($this->classes_esp_ok) as $l_classe) {
				$t[] = new bobs_classe($this->db, $l_classe);
			}
		}
		return $t;
	}
}
