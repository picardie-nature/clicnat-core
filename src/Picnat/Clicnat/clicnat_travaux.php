<?php
namespace Picnat\Clicnat;

class clicnat_travaux extends bobs_element {
	protected $id_travail;
	protected $id_travail_categorie;
	protected $titre;
	protected $date_creation;
	protected $date_modif;
	protected $description;
	protected $type;
	protected $data;

	public function __construct($db, $id) {
		parent::__construct($db, 'travaux', 'id_travail', $id);
		$this->champ_date_maj = 'date_modif';
	}

	const sql_liste = 'select id_travail from travaux order by titre';

	public static function liste($db) {
		$q = bobs_qm()->query($db, 'travaux_liste', self::sql_liste, array());
		$r = self::fetch_all($q);
		return new clicnat_iterateur_travaux($db, array_column($r, 'id_travail'));
	}

	public static function nouveau($db, $titre, $type) {
		$id = self::nextval($db, 'travaux_id_travail_seq');
		self::insert($db, 'travaux',
			array(
				'id_travail' => $id,
				'titre' => $titre,
				'type' => $type
			)
		);
		return $id;
	}

	const sql_s = 'select * from travaux where id_travail=$1';

	public static function instance($db, $id) {
		$q = bobs_qm()->query($db, 'travail_s', self::sql_s, array($id));
		$r = self::fetch($q);
		switch ($r['type']) {
			case 'images':
				return new clicnat_travaux_images($db, $id);
			case 'lien':
				return new clicnat_travaux_lien($db, $id);
			case 'wfs':
				return new clicnat_travaux_wfs($db, $id);
			case 'wms':
				return new clicnat_travaux_wms($db, $id);
			default:
				throw new Exception("type inconnu");
		}
	}
}
