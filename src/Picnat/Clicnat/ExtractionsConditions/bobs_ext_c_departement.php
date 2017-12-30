<?php
namespace Picnat\Clicnat\ExtractionsConditions;

class bobs_ext_c_departement extends bobs_extractions_conditions {
	const poste = true;
	protected $id_espace;

	function  __construct($id_espace) {
		parent::__construct();
		$this->arguments[] = 'id_espace';
		$this->id_espace = $id_espace;
	}

	public function __get($c) {
		switch ($c) {
			case 'id_espace':
				return $this->id_espace;
			default:
				throw new Exception('propriété inconnue');
		}
	}

	public function  __toString() {
		$db = $this->extraction->get_db();
		$dept = get_espace_departement($db, $this->id_espace);
		return "Département : <b>{$dept->nom}</b>";
	}

	public static function get_titre() {
		return 'Département';
	}

	public function get_sql() {
		return sprintf("espace_intersect.id_espace_ref=%d and espace_intersect.table_espace_ref='espace_departement'", $this->id_espace);
	}

	public static function new_by_array($t) {
		return new self($t['id_espace']);
	}

	public function get_tables() {
		return ['espace_intersect', 'observations'];
	}

	public static function get_html() {
		if (!defined('DEPARTEMENTS')) {
			throw new \Exception('définir DEPARTEMENTS dans config.php');
		}
		$l_deps = explode(',', DEPARTEMENTS);
		$r = "<select name='id_espace'>";
		foreach ($l_deps as $id) {
			$d = new bobs_espace_departement(get_db(), $id);
			$r .= "<option value={$id}>{$d}</option>";
		}

		return $r;
	}
}
