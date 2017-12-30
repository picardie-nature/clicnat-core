<?php
namespace Picnat\Clicnat\ExtractionsConditions;

class bobs_ext_c_tag_invalide extends bobs_ext_c_tag {
	const poste = true;

	function __construct() {
		parent::__construct(get_config()->query_nv('/clicnat/validation/id_tag_invalide'));
	}

	public static function new_by_array($t) {
		return new bobs_ext_c_tag_invalide();
	}

	public static function get_titre() {
		return 'Données invalides';
	}
}
