<?php
namespace Picnat\Clicnat\ExtractionsConditions;

class bobs_ext_c_classe extends bobs_extractions_conditions {
	protected $classe;
	const poste = true;
	const clicnat1 = true;

	function __construct($classe) {
		parent::__construct();
		bobs_element::cls($classe);
		$this->arguments[] = 'classe';
		$this->classe = $classe;
	}

	public function  __toString() {
		return bobs_espece::get_classe_lib_par_lettre($this->classe);
	}

	public static function get_titre() {
		return 'Classe d\'espèce';
	}

	public function get_sql() {
		return sprintf('especes.classe=\'%s\'', $this->classe);
	}

	public function get_tables() {
		return array('especes');
	}

	public static function new_by_array($t) {
		$cle = array_key_exists('cl', $t)?'cl':'classe';
		return new bobs_ext_c_classe($t[$cle]);
	}
}
