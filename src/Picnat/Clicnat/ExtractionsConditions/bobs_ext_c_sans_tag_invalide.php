<?php
namespace Picnat\Clicnat\ExtractionsConditions;

class bobs_ext_c_sans_tag_invalide extends bobs_ext_c_sans_tag {
	const poste = true;

	function __construct() {
		parent::__construct(\Picnat\Clicnat\get_config()->query_nv('/clicnat/validation/id_tag_invalide'));
	}

	public static function new_by_array($t) {
		return new self();
	}

	public static function get_titre() {
		return 'Sans données invalide';
	}
}
