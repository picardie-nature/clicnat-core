<?php
namespace Picnat\Clicnat\ExtractionsConditions;

class bobs_ext_c_reseau extends bobs_extractions_conditions {
	protected $reseau;
	protected $id;
	const poste = true;

	/**
	 * @param $reseau bobs_reseau
	 */
	function __construct($reseau) {
		parent::__construct();
		$this->reseau = $reseau;
		$this->id = $reseau->get_id();
		$this->arguments[] = 'id';
	}

	public function __toString() {
		return "Réseau {$this->reseau}";
	}

	static public function get_titre() {
		return 'Réseau naturaliste';
	}

	public function get_sql() {
		return $this->reseau->where;
	}

	public function get_tables() {
		return ['especes'];
	}

	public static function new_by_array($t) {
		$reseau = get_bobs_reseau(get_db(), $t['id']);
		return new self($reseau);
	}

	public static function get_html() {
		$db = get_db();
		$ret = "<select name=\"id\">";
		foreach (clicnat2_reseau::liste_reseaux($db) as $reseau) {
			$ret .= "<option value=\"{$reseau->id}\">{$reseau}</option>";
		}
		$ret .= "</option>";
		return $ret;
	}
}
