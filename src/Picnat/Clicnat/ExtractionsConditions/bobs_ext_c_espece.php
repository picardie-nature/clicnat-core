<?php
namespace Picnat\Clicnat\ExtractionsConditions;

use Picnat\Clicnat\i_clicnat_tests;
use Picnat\Clicnat\clicnat_tests;

class bobs_ext_c_espece extends bobs_extractions_conditions implements i_clicnat_tests {
	use clicnat_tests;

	protected $id_espece;
	const poste = true;

	function __construct($id) {
		parent::__construct();
		$this->arguments[] = 'id_espece';
		$this->id_espece = self::cli($id, self::except_si_vide);
	}

	public function  __toString() {
		$esp = get_espece($this->extraction->get_db(), $this->id_espece);
		return "Espèce : {$esp->__toString()}";
	}

	static public function get_titre() {
		return 'Espèce';
	}

	public function get_sql() {
		return sprintf('citations.id_espece=%d', $this->id_espece);
	}

	public function get_tables() {
		return array('citations');
	}

	public static function new_by_array($t) {
		return new self($t['id_espece']);
	}

	public static function get_html() {
		return "
			<label for='lcond_taxonx'>Espèce</label>
			<input id='lcond_taxonx' type='text' name='id_espece' class='autocomplete_espece form-control' required=true/>
		";
	}
}
