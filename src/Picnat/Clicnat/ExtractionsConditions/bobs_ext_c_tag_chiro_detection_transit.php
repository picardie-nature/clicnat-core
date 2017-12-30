<?php
namespace Picnat\Clicnat\ExtractionsConditions;

class bobs_ext_c_tag_chiro_detection_transit extends bobs_ext_c_tag {
	function __construct() {
		parent::__construct(588);
	}

	public static function new_by_array($t) {
		return new self();
	}

	public static function get_titre() {
		return 'Données chiro avec étiquette détection transit';
	}
}
