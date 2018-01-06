<?php
namespace Picnat\Clicnat\ExtractionsConditions;
use Picnat\Clicnat\bobs_element;
use Picnat\Clicnat\bobs_tests;

class bobs_ext_c_poly2 extends bobs_extractions_conditions {
	protected $espace_po_table;
	protected $espace_po_id_es;

	const poste = true;

	function __construct($table_poly, $id_espace_poly) {
		parent::__construct();
		bobs_tests::cli($id_espace_poly, bobs_tests::except_si_inf_1);
		bobs_tests::cls($table_poly, bobs_tests::except_si_vide);
		$this->espace_po_id_es = $id_espace_poly;
		$this->espace_po_table = $table_poly;
		$this->arguments[] = 'espace_po_table';
		$this->arguments[] = 'espace_po_id_es';
	}

	public function get_tables() {
		return ['observations'];
	}

	public function __toString() {
		$db = $this->extraction->get_db();
		$sql = "select * from {$this->espace_po_table} where id_espace=$1";
		$q = bobs_qm()->query($db, "ext_c_poly_{$this->espace_po_table}", $sql, array($this->espace_po_id_es));
		$r = bobs_element::fetch($q);
		bobs_element::cls($r['nom']);
		$r['nom'] = empty($r['nom'])?"Sans nom #{$r['id_espace']}":$r['nom'];
		return "Intersection avec {$r['nom']}";
	}

	public static function new_by_array($t) {
		if (array_key_exists('espace_po_table', $t))
			$t['table_poly'] = $t['espace_po_table'];
		if (array_key_exists('espace_po_id_es', $t))
			$t['id_espace'] = $t['espace_po_id_es'];

		return new self($t['table_poly'], $t['id_espace']);
	}

	public static function get_html() {
		return "
		<div>
			<label for='lcond_tb'>Table contenant le polygone</label>
			<select id='lcond_tb' name='table_poly'>
				<option value='espace_l93_10x10'>espace_l93_10x10</option>
				<option value='espace_polygon'>espace_polygon</option>
			</select>
		</div>
		<div>
			<label for='lcond_id_espace'>Numéro de l'objet</label>
			<input type='text' name='id_espace' required=true class='form-control'>
		</div>
		";
	}

	public function get_sql() {
		return "observations.id_observation in (
				select id_observation from observations,espace_point e where e.id_espace=observations.id_espace and st_intersects((select the_geom from {$this->espace_po_table} where id_espace={$this->espace_po_id_es}),e.the_geom)
				union
				select id_observation from observations,espace_line e where e.id_espace=observations.id_espace and st_intersects((select the_geom from {$this->espace_po_table} where id_espace={$this->espace_po_id_es}),e.the_geom)
				union
				select id_observation from observations,espace_polygon e where e.id_espace=observations.id_espace and st_intersects((select the_geom from {$this->espace_po_table} where id_espace={$this->espace_po_id_es}),e.the_geom)
				union
				select id_observation from observations,espace_chiro e where e.id_espace=observations.id_espace and st_intersects((select the_geom from {$this->espace_po_table} where id_espace={$this->espace_po_id_es}),e.the_geom)
				union
				select id_observation from observations,espace_commune e where e.id_espace=observations.id_espace and st_intersects((select the_geom from {$this->espace_po_table} where id_espace={$this->espace_po_id_es}),e.the_geom)
		)
		";
	}

	public static function get_titre() {
		return "Extraction depuis un polygone (v2)";
	}
}
