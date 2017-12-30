<?php
namespace Picnat\Clicnat\ExtractionsConditions;

class bobs_ext_c_tag_int extends bobs_ext_c_tag {
	const poste = false;
	protected $entier;

	function __construct($id_tag, $entier) {
		parent::__construct($id_tag);
		$this->arguments[] = 'texte';
		$this->entier = $entier;
	}

	public function __toString() {
		return parent::__toString().' <i>'.$this->entier.'</i>';
	}

	public static function get_titre() {
		return 'Étiquette - code comportement avec entier';
	}

	const sql = "citations.id_citation in (select id_citation from citations_tags where id_tag=%d and v_int=%d)";

	public function get_sql() {
		return sprintf(self::sql, $this->id_tag, $this->entier);
	}

	public static function new_by_array($t) {
		return new self($t['id_tag'], $t['entier']);
	}
}
