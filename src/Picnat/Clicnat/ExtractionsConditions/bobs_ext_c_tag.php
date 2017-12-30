<?php
namespace Picnat\Clicnat\ExtractionsConditions;

class bobs_ext_c_tag extends bobs_extractions_conditions {
	const poste = true;
	protected $id_tag;

	function __construct($id_tag) {
		$this->id_tag = $id_tag;
		parent::__construct();
		$this->arguments[] = 'id_tag';
	}

	public function __toString() {
		$db = $this->extraction->get_db();
		$tag = new bobs_tags($db, $this->id_tag);
		return "étiquette {$tag->lib}";
	}

	public static function get_titre() {
		return 'Étiquette - code comportement';
	}

	public function get_sql() {
		return sprintf("citations.id_citation in (select id_citation from citations_tags where id_tag=%d)", $this->id_tag);
	}

	public static function new_by_array($t) {
		return new bobs_ext_c_tag($t['id_tag']);
	}

	public function get_tables() {
		return array('citations');
	}
}
