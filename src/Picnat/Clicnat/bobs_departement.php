<?php
namespace Picnat\Clicnat;

class bobs_departement extends bobs_poly {
	function __construct($db, $id, $table='espace_departement') 	{
		parent::__construct($db, $id, $table);
	}

	/**
	 *
	 * @param ressource $db
	 * @return array
	 * @deprecated
	 */
	public static function get_liste($db) {
		$sql = "select id_espace as id,nom
			from espace_departement
			where nom in
				('SOMME','AISNE','OISE')
			order by nom";
		$q = self::query($db, $sql);
		$t = [];
		while ($r = self::fetch($q)) {
			$t[] = $r;
		}
		return $t;
	}

	public static function get_list($db, $table='espace_departement') {
		return parent::get_list($db, $table);
	}
}
