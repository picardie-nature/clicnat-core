<?php
namespace Picnat\Clicnat\ExtractionsConditions;

class bobs_ext_c_sans_tag_en_attente extends bobs_ext_c_sans_tag {
	const poste = true;

	function __construct() {
		parent::__construct(get_config()->query_nv('/clicnat/validation/id_tag_attente'));
	}

	public static function new_by_array($t) {
		return new self();
	}

	public static function get_titre() {
		return 'Sans données en attente de validation';
	}
}
