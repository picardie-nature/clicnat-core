<?php
namespace Picnat\Clicnat\ExtractionsConditions;

class bobs_ext_c_sans_diffusion_restreinte extends bobs_extractions_conditions {
	const poste = false;
	const qg = false;
	protected $observateurs;

	/**
	 *
	 * @param array $observateurs un tableau d'id_utilisateur ou une liste d'id séparés par des virgules
	 */
	function __construct($observateurs) {
		parent::__construct();
		$this->arguments[] = 'observateurs';
		$this->visible_sur_poste = false;

		if (!is_array($observateurs)) {
			$this->observateurs = explode(',', $observateurs);
		} else {
			$s = '';
			foreach ($observateurs as $observateur)
				$s .= $observateur.',';
			$s = trim($s, ',');
			$this->observateurs = $s;
		}

	}

	public function __toString() {
		return "Observateurs en diffusion restreinte";
	}

	static public function get_titre() {
		return "Observateurs en diffusion restreinte";
	}

	public function get_sql() {
		return sprintf('not bob_diffusion_restreinte(citations.id_citation, \'{%s}\')',$this->observateurs);
	}

	public function get_tables() {
		return array('citations');
	}
}
