<?php
namespace Picnat\Clicnat;

class clicnat_textes extends bobs_element {
	protected $id_texte;
	protected $nom;
	protected $texte;
	protected $date_modif;

	public function __construct($db, $id) {
		parent::__construct($db, 'textes', 'id_texte', $id);
		$this->champ_date_maj = 'date_modif';

	}

	public function __get($k) {
		switch ($k) {
			case 'id_texte':
				return $this->id_texte;
			case 'nom':
				return $this->nom;
			case 'texte':
				return $this->texte;
			case 'date_modif':
				return $this->date_modif;
			default:
				throw new InvalidArgumentException('clé inconnue');
		}
	}

	public function __toString() {
		return $this->nom;
	}

	public static function nouveau($db, $nom) {
		$id = self::nextval($db, 'textes_id_texte_seq');
		self::insert($db, 'textes',
			array(
				'id_texte' => $id,
				'nom' => $nom
			)
		);
		return $id;
	}

	public function set_texte($texte) {
		self::cls($texte, self::except_si_vide);
		$this->update_field('texte', $texte);
	}

	const sql_par_nom = 'select * from textes where nom=$1';

	public static function par_nom($db, $nom) {
		self::cls($nom, self::except_si_vide);
		$q = bobs_qm()->query($db, 'markdown_par_nom', self::sql_par_nom, array($nom));
		$r = self::fetch($q);
		if (!isset($r['id_texte'])) {
			throw new Exception("texte $nom introuvable");
		}
		return new clicnat_textes($db, $r);
	}
	const sql_liste = 'select id_texte from textes order by nom';
	public static function liste($db) {
		$q = bobs_qm()->query($db, 'markdown_liste', self::sql_liste, array());
		$r = self::fetch_all($q);
		$t = array();
		if (count($r) > 0) foreach ($r as $e) $t[] = $e['id_texte'];
		return new clicnat_iterateur_textes($db, $t);
	}
}
