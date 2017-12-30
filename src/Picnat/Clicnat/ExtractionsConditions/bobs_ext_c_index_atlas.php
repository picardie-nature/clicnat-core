<?php
namespace Picnat\Clicnat\ExtractionsConditions;

/**
 * @brief Index répartition (atlas)
 */
class bobs_ext_c_index_atlas extends bobs_extractions_conditions {
	const poste = false;
	protected $srid;
	protected $pas;
	protected $x0;
	protected $y0;

	public function __construct($srid,$pas,$x0,$y0) {
		$this->srid = $srid;
		$this->pas = $pas;
		$this->x0 = $x0;
		$this->y0 = $y0;
	}

	public static function get_titre() {
		return "index atlas répartition";
	}

	public function get_sql() {
		return sprintf("srid=%d and pas=%d and x0=%d and y0=%d", $this->srid, $this->pas, $this->x0, $this->y0);
	}

	public function get_tables() {
		return array("espace_index_atlas");
	}

	public static function new_by_array($t) {
		return new bobs_ext_c_index_atlas($t['srid'],$t['pas'],$t['x0'],$t['y0']);
	}
}
