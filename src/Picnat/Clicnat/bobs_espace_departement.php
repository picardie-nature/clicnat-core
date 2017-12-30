<?php
namespace Picnat\Clicnat;

class bobs_espace_departement extends bobs_departement {
	public static function rechercher($db, $args, $table='espace_departement') {
		return parent::rechercher($db, $args, $table);
	}

	public static function get_by_ref($db, $ref) {
		return self::__get_by_ref($db, 'espace_departement', 'bobs_espace_departement', $ref);
	}
}
