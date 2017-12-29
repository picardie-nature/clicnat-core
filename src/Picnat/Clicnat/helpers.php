<?php

namespace \Picnat\Clicnat;


if (!function_exists('array_column')) {
	/**
	 * @brief Retourne les valeurs d'une colonne d'un tableau d'entrée (en attendant PHP 5.5)
	 */
	function array_column($input, $column_key, $index_key = false) {
		$r = array();
		if ($index_key === false) {
			foreach ($input as $k => $v) {
				$r[] = $v[$column_key];
			}
		} else {
			foreach ($input as $k => $v) {
				$r[$v[$index_key]] = $v[$column_key];
			}
		}
		return $r;
	}
}


function get_db_type_enum($db, $typname) {
	static $types;

	if (!isset($types))
		$types = array();

	if (!isset($types[$typname])) {
		$types[$typname] = new clicnat_db_type_enum($db, $typname);
	}
	return isset($types[$typname])?$types[$typname]:false;
}

/**
 * @brief Transformation markdown vers texte (sans les balises md)
 * @param $txt_md le texte en markdown
 * @return texte sans balises markdown
 */
function clicnat_markdown_txt($txt_md) {
	require_once('markdown.php');
	require_once(OBS_DIR.'/Html2Text.php');
	static $html2txt;

	$html = markdown($txt_md);

	if (!isset($html2txt))
		$html2txt = new \Html2Text\Html2Text($html, false, array('do_links' => 'none'));
	else
		$html2txt->set_html($html);

	return $html2txt->get_text();
}

function csv_clean_string($s,$quote) {
	return str_replace($quote," ",$s);
}

function tmpdir($path="/tmp", $prefix="clicnat") {
	$fn = tempnam($path,$prefix);
	if (!$fn)
		throw new Exception('peut pas créer de dossier temporaire (tempnam)');
	unlink($fn);
	if (!mkdir($fn))
		throw new Exception('peut pas créer de dossier temporaire (mkdir)');
	return $fn;
}

/**
 * @brief détermine la quantitée max de mémoire utilisable
 * @return int
 */
function memory_limit() {
	static $s;

	if (isset($s)) return $s;

	$sm = ini_get('memory_limit');
	$unit = strtoupper($sm[strlen($sm)-1]);

	$s = trim($sm, $unit);

	switch ($unit) {
		case 'G':
			$s *= 1024;
		case 'M':
			$s *= 1024;
		case 'K':
			$s *= 1024;
	}

	// on se limitera a 128M si possible
	if ($s>128*1024*1024) {
		$s = 128*1024*1024;
	}

	return $s;
}

/**
 * @brief Obtenir l'instance qui gère le fichier de conf xml
 */
function get_config($fichier = '/etc/baseobs/config.xml') {
	static $c;

	if (!isset($c)) {
		$c = new clicnat_config($fichier);
	}
	return $c;
}

function get_db($init_db=null) {
	static $db;
	if (!is_null($init_db))
		$db = $init_db;
	return $db;
}

/**
 * @brief provide query manager singleton
 */
function bobs_qm() {
	static $qm;

	if (!isset($qm))
		$qm = new bobs_query_manager();

	return $qm;
}

function aonfm_xml($db) {
	return bobs_aonfm::aonfm_xml($db);
}

function aonfm_tri_sys2($a,$b) {
	return bobs_aonfm::aonfm_tri_sys2($a,$b);
}

function aonfm_tri_systematique($a, $b) {
	if ($a['objet']->systematique == $b['objet']->systematique) return 0;
	return ((int)$a['objet']->systematique > (int)$b['objet']->systematique)?1:-1;
}
