<?php
namespace Picnat\Clicnat\ExtractionsConditions;

class bobs_ext_c_diffusion_libre extends bobs_extractions_conditions {
	public function __toString() {
		return "Observateurs en diffusion libre";
	}

	public static function get_titre() {
		return "Observateurs en diffusion libre";
	}

	public function get_sql() {
		return "utilisateur.diffusion_restreinte = false";
	}

	public static function new_by_array($t) {
		return new self();
	}

	public function get_tables() {
		return ['utilisateur'];
	}
}
