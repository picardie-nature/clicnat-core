<?php
namespace Picnat\Clicnat\ExtractionsConditions;

class bobs_ext_c_tag_protocole extends bobs_ext_c_tag_txt {
	const poste = true;

	function __construct($texte) {
		parent::__construct(get_config()->query_nv('/clicnat/protocoles/id_tag'), $texte);
	}

	public static function new_by_array($t) {
		return new bobs_ext_c_tag_structure($t['texte']);
	}

	public static function get_titre() {
		return 'Données associées à un protocole';
	}
}
