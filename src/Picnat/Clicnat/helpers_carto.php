<?php
namespace Picnat\Clicnat;

/**
 * @brief changement de projection d'un point
 * @param $x coordonnée
 * @param $y coordonnée
 * @param $epsg_src id de la projection d'origine
 * @param $epsg_dst id de la projection cible
 * @return x et y dans un tableau asssociatif
 *
 * Utilise les fonctions fournies par mapserver
 * http://mapserver.org/mapscript/php/index-5.6.html#projectionobj
 */
function bobmap_point_reproject($x, $y, $epsg_src=EPSG_WGS84, $epsg_dst=EPSG_RGF93) {
	$projInObj = ms_newprojectionobj("init=epsg:$epsg_src");
	$projOutObj = ms_newprojectionobj("init=epsg:$epsg_dst");
	$poPoint = ms_newpointobj();
	$poPoint->setXY($x, $y);
	$poPoint->project($projInObj, $projOutObj);
	return array($poPoint->x, $poPoint->y);
}

function bobmap_get_map_object($mapfile = DEFAULT_MAPFILE) {
	static $mapobj;

	bobs_tests::cls($mapfile);

	if (!isset($mapobj)) {
	    	if (!file_exists($mapfile))
		    throw new InvalidArgumentException('mapfile not found @ '.$mapfile);
		$mapobj = ms_newMapObj($mapfile);
	}

	return $mapobj;
}

function bobmap_get_layer_image($width, $height, $layer_to_draw, $pa, $pb, $pt=null) {
	return bobmap_get_layers_image($width, $height, array($layer_to_draw), $pa, $pb, $pt);
}

/**
 * @param $width image width in pixel
 * @param $height image height in pixel
 * @param $layers array layer to draw
 * @param $opts special cases
 */
function bobmap_get_layers_image($width, $height, $layers, $pa, $pb, $pt=null, $mapfile = DEFAULT_MAPFILE, $opts=null)
{
	if (!is_array($opts))
	    $opts = array();

	$map = bobmap_get_map_object($mapfile);
	// switch off all layers except $layer_to_draw
	$ok = false;

	// for atlas we must change data value given in opts
	if (in_array('atlas', $layers)) {
		$layer = $map->getLayerByName('atlas');
		if (empty($opts['atlas_data']))
		    throw new Exception('with atlas you must provide path to shapefile');
		$layer->set('data', $opts['atlas_data']);
	}

	foreach ($map->getAllLayerNames() as $layer_name) {
		$layer = $map->getLayerByName($layer_name);
		if (!in_array($layer_name, $layers)) {
			$layer->set('status', MS_OFF);
		} else {
			$ok = true;
			$layer->set('status', MS_ON);
			while ($map->moveLayerUp($layer->index))
				continue;
		}
	}
	if (!$ok)
		throw new Exception('All layers off !');

	$map->setSize($width, $height);
	$map->setExtent(
		min($pa[0],$pb[0]), min($pa[1],$pb[1]),
		max($pa[0],$pb[0]), max($pa[1],$pb[1])
	);

	if ($pt) {
		$layer_pt = @$map->getLayerByName(BM_LAYER_PT);

		if (!$layer_pt)
			throw new Exception("can't get layer ".BM_LAYER_PT);

		$layer_pt->set('status', MS_ON);

		$shape = ms_shapeObjFromWkt(sprintf('POINT(%s %s)', $pt[0], $pt[1]));

		if ($shape->type != MS_SHAPE_POINT)
			throw Exception('not a point ?');
	}

	$img = $map->draw();

	if ($pt)
		$shape->draw($map, $layer_pt, $img);

	return $img;
}

function bobmap_extent_point_from_wkt($wkt) {
	bobmap_get_map_object();
	bobs_element::cls($wkt);
	if (empty($wkt)) {
		throw new \Exception('empty wkt string');
	}
	$shape = ms_shapeObjFromWkt($wkt);
	return [
		bobmap_point_reproject($shape->bounds->minx, $shape->bounds->miny),
		bobmap_point_reproject($shape->bounds->maxx, $shape->bounds->maxy)
	];
}

function bobmap_image($img, $file='') {
	$map = bobmap_get_map_object();
	if ($img->saveImage($file, $map) == -1)
		throw new \Exception('saveImage error');
}

function bobmap_image_out($img) {
	header("Content-Type: image/png");
	bobmap_image($img);
	exit();
}

function bobmap_get_n_citations_par_maille($reseau) {
	bobs_tests::cls($reseau, bobs_tests::except_si_vide);
	$pa = bobmap_point_reproject(COORD_REGION_AX, COORD_REGION_AY);
	$pb = bobmap_point_reproject(COORD_REGION_BX, COORD_REGION_BY);
	return bobmap_get_layers_image(1600, 1600*0.75, array('fleuves','departements', 'mailles', 'nb_cit_par_maille_'.$reseau), $pa, $pb);
}

function bobmap_get_n_especes_par_maille($reseau) {
	bobs_tests::cls($reseau, bobs_tests::except_si_vide);
	$pa = bobmap_point_reproject(COORD_REGION_AX, COORD_REGION_AY);
	$pb = bobmap_point_reproject(COORD_REGION_BX, COORD_REGION_BY);
	return bobmap_get_layers_image(1600, 1600*0.75, array('fleuves','departements', 'mailles', 'nb_esp_par_maille_'.$reseau), $pa, $pb);
}

function bobmap_get_region_especes_par_communes() {
	$pa = bobmap_point_reproject(COORD_REGION_AX, COORD_REGION_AY);
	$pb = bobmap_point_reproject(COORD_REGION_BX, COORD_REGION_BY);
	return bobmap_get_layers_image(800, 600, array('fleuves','departements','especes par communes'), $pa, $pb);
}

function bobmap_get_atlas_espece($id_espece) {
	$pa = bobmap_point_reproject(COORD_REGION_AX, COORD_REGION_AY);
	$pb = bobmap_point_reproject(COORD_REGION_BX, COORD_REGION_BY);
	$opts = array('atlas_data' => sprintf(ATLAS_SHAPE_PATH, $id_espece));
	return bobmap_get_layers_image(800, 600, array('fleuves','departements','atlas'),
		$pa, $pb, null, DEFAULT_MAPFILE, $opts);
}
