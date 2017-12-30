<?php
namespace Picnat\Clicnat\ExtractionsConditions;

class bobs_ext_c_poste extends bobs_extractions_conditions {
	protected $id_utilisateur;
	protected $use_join;

	function __construct($id_utilisateur) {
		$this->id_utilisateur = $id_utilisateur;
	}

	public static function get_titre() {
		return 'Observations mises à disposition';
	}

	public function __toString() {
		return self::get_titre();
	}

	public function get_sql() {
		return sprintf("exists(select 1 from utilisateur_citations_ok
					where utilisateur_citations_ok.id_utilisateur=%d
					and  utilisateur_citations_ok.id_citation=citations.id_citation)",
				$this->id_utilisateur);
	}

	public function get_tables() {
		return array('citations');
	}

	public function sauve_xml($doc, $ele) {
		// ne sera pas enregistré
		return true;
	}
}
