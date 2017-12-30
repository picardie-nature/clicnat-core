<?php
namespace Picnat\Clicnat\ExtractionsConditions;

class bobs_ext_c_poly_tampon extends bobs_ext_c_poly {
	const clicnat1 = true;
	protected $distance_tampon;

	public static function get_titre() {
		return "Extraction depuis un tampon autour d'un polygone";
	}

	public function __toString() {
		return sprintf("Tampon de %dm autour de %s", $this->distance_tampon, parent::__toString());
	}

	function __construct($table_poly, $table_point, $id_espace_poly, $distance_tampon) {
		parent::__construct($table_poly, $table_point, $id_espace_poly);
		$this->distance_tampon = bobs_element::cli($distance_tampon, bobs_element::except_si_vide);
	}

	public function get_sql() {
		 return "st_intersects(
		 		(select st_transform(
		 		st_buffer(st_transform({$this->espace_po_table}.the_geom,2154),{$this->distance_tampon}),4326)
		 		from {$this->espace_po_table} where id_espace={$this->espace_po_id_es})
		 		,{$this->espace_pt_table}.the_geom
		 	)";
	}

	public static function new_by_array($t) {
		if (array_key_exists('espace_po_table', $t))
			$t['table_poly'] = $t['espace_po_table'];
		if (array_key_exists('espace_pt_table', $t))
			$t['table_point'] = $t['espace_pt_table'];
		if (array_key_exists('espace_po_id_es', $t))
			$t['id_espace'] = $t['espace_po_id_es'];

		return new bobs_ext_c_poly_tampon($t['table_poly'], $t['table_point'], $t['id_espace'], $t['tampon']);
	}
}
