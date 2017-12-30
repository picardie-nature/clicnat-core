<?php
namespace Picnat\Clicnat;

/**
 * @brief Polygones des territoires gérées par les structures pour la mise à dispo de données
 */
class bobs_espace_structure extends bobs_poly {
	function __construct($db, $id, $table='espace_structure') {
		parent::__construct($db, $id, $table);
	}

	public static function get_list($db, $table='espace_structure') {
		return parent::get_list($db, $table);
	}
}
