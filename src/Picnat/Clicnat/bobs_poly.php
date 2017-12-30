<?php
namespace Picnat\Clicnat;

class bobs_poly extends bobs_espace {
	public static function get_especes_in_espaces($db, $espaces) {
		$liste = '';
		foreach ($espaces as $esp)
			$liste .= $esp->id_espace.',';
		$sql = sprintf("select distinct esp.*
				from especes esp,citations ci, observations obs
				where esp.id_espece=ci.id_espece
				and ci.id_observation = obs.id_observation
				and obs.id_espace in (%s)
				order by nom_f", trim($liste,','));
		$q = self::query($db, $sql);
		$esps = array();
		while ($r = self::fetch($q))
			$esps[] = $r;
		return $esps;
	}

	/**
	 * @brief return MS ImageObj
	 * @param $width image width
	 * @param $height image height
	 * @param $layer base layer (scan25,scan100,ortho see l93.map)
	 * @param $pt a point drawn on layer (array(x,y))
	 * @return MS ImageObj object
	 */
	public function get_ms_img($width, $height, $layer, $pt=null)
	{
	    $pab = bobmap_extent_point_from_wkt($this->get_envelope());
	    $img = bobmap_get_layer_image($width, $height, $layer, $pab[0], $pab[1], $pt);
	    return $img;
	}

	public static function get_list($db, $table)
	{
	    return parent::get_list($db, $table);
	}
}
