<?php
namespace Picnat\Clicnat\ExtractionsConditions;

class bobs_ext_c_tag_txt extends bobs_ext_c_tag {
	const poste = false;
	protected $texte;

	function __construct($id_tag, $texte) {
		parent::__construct($id_tag);
		$this->arguments[] = 'texte';
		$this->texte = $texte;
	}

	public function __toString() {
		return parent::__toString().' <i>'.$this->texte.'</i>';
	}

	public static function get_titre() {
		return 'Étiquette - code comportement avec texte';
	}

	const sql = "citations.id_citation in (select id_citation from citations_tags where id_tag=%d and v_text='%s')";

	public function get_sql() {
		return sprintf(self::sql, $this->id_tag, pg_escape_string($this->texte));
	}

	public static function new_by_array($t) {
		return new self($t['id_tag'], $t['texte']);
	}
}
