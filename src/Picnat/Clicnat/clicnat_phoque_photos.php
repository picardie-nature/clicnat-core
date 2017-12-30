<?php
namespace Picnat\Clicnat;

class clicnat_phoque_photos extends bobs_element {
	protected $id_phoque;
	protected $document_ref;
	protected $orientation;
	protected $date_creation;
	protected $date_modification;

	const orientation_type = 't_phoque_photo_orient';

	function __get($c) {
		switch ($c) {
			case 'phoque':
				return get_phoque($this->db, $this->id_phoque);
			default:
				return $this->$c;
		}
	}

	function __construct($db, $document_ref) {
		parent::__construct($db, 'phoques_photos', 'document_ref', $document_ref);
		$this->champ_date_maj = 'date_modification';
	}

	public static function choix_orientation($db) {
		return get_db_type_enum($db, self::orientation_type);
	}

	public static function insert($db, $document_ref) {
		return parent::insert($db, 'phoques_photos', array('document_ref'=>$document_ref));
	}

	public function set_phoque($phoque) {
		if (is_null($phoque))
			$this->update_field_null('id_phoque');
		else
			$this->update_field('id_phoque', $phoque->id_phoque);
	}

	public function set_orientation($orientation) {
		$this->update_field('orientation', $orientation);
	}

	const sql_par_phoque = 'select document_ref from phoques_photos where id_phoque = $1';

	public static function par_phoque($phoque) {
		$q = bobs_qm()->query($phoque->db(), 'phoque_photo_par_ph', self::sql_par_phoque, array($phoque->id_phoque));
		$r  = self::fetch_all($q);
		return new clicnat_iterateur_phoque_photos($phoque->db(), array_column($r, 'document_ref'));
	}

	const sql_sans_phoque = 'select document_ref from phoques_photos where id_phoque is null';

	public static function sans_phoque($db) {
		$q = bobs_qm()->query($db, 'phoque_photo_sans_ph', self::sql_sans_phoque, array());
		$r  = self::fetch_all($q);
		return new clicnat_iterateur_phoque_photos($db, array_column($r, 'document_ref'));
	}

	const sql_supprimer = 'delete from phoques_photos where document_ref=$1 and id_phoque is null';

	public function supprimer() {
		if (!empty($this->id_phoque))
			throw new Exception('ne peut supprimer, encore associer a un phoque');

		bobs_qm()->query($this->db, 'photo_phoque_supprimer', self::sql_supprimer, array($this->document_ref));
	}
}
