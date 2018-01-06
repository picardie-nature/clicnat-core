<?php
namespace Picnat\Clicnat\ExtractionsConditions;

use Picnat\Clicnat\bobs_element;
use Picnat\Clicnat\bobs_tests;

class bobs_ext_c_poly extends bobs_extractions_conditions {
	protected $espace_po_table;
	protected $espace_pt_table;
	protected $espace_po_id_es;
	const poste = true;
	const clicnat1 = true;

	function __construct($table_poly, $table_point, $id_espace_poly) {
		parent::__construct();
		bobs_tests::cli($id_espace_poly, bobs_tests::except_si_inf_1);
		bobs_tests::cls($table_point, bobs_tests::except_si_vide);
		bobs_tests::cls($table_poly, bobs_tests::except_si_vide);
		$this->espace_po_id_es = $id_espace_poly;
		$this->espace_pt_table = $table_point;
		$this->espace_po_table = $table_poly;
		$this->arguments[] = 'espace_po_table';
		$this->arguments[] = 'espace_pt_table';
		$this->arguments[] = 'espace_po_id_es';
	}

	public function get_tables() {
		return [$this->espace_pt_table];
	}

	public function  __toString() {
		$db = $this->extraction->get_db();
		$sql = "select * from {$this->espace_po_table} where id_espace=$1";
		$q = bobs_qm()->query($db, "ext_c_poly_{$this->espace_po_table}", $sql, array($this->espace_po_id_es));
		$r = bobs_element::fetch($q);
		bobs_element::cls($r['nom']);
		$r['nom'] = empty($r['nom'])?"Sans nom #{$r['id_espace']}":$r['nom'];
		return "Intersection entre {$r['nom']} et {$this->espace_pt_table}";
	}

	public static function new_by_array($t) {
		if (array_key_exists('espace_po_table', $t))
			$t['table_poly'] = $t['espace_po_table'];
		if (array_key_exists('espace_pt_table', $t))
			$t['table_point'] = $t['espace_pt_table'];
		if (array_key_exists('espace_po_id_es', $t))
			$t['id_espace'] = $t['espace_po_id_es'];

		return new self($t['table_poly'], $t['table_point'], $t['id_espace']);
	}

	public function get_sql() {
		return "st_intersects((select the_geom from {$this->espace_po_table} where id_espace={$this->espace_po_id_es}), {$this->espace_pt_table}.the_geom)";
	}

	public static function get_titre() {
		return "Extraction depuis un polygone";
	}
}
