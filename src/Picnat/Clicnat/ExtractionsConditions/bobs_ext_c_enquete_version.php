<?php
namespace Picnat\Clicnat\ExtractionsConditions;

/**
 * @brief Citations reliées à une enquête
 */
class bobs_ext_c_enquete_version extends bobs_extractions_conditions {
	const poste = false;
	protected $id_enquete;
	protected $version;

	public function __toString() {
		return "Enquête {$this->id_enquete} version {$this->version}";
	}

	public static function get_titre() {
		return 'Observations d\'une enquête';
	}

	public function get_sql() {
		return sprintf("citations.enquete_resultat is not null and xpath('/enquete_resultat[@id_enquete=%d][@version=%d]',citations.enquete_resultat)::text != '{}'", $this->id_enquete, $this->version);
	}

	public function get_tables() {
		return array('citations');
	}

	public static function new_by_array($t) {
		return new bobs_ext_c_enquete_version($t['id_enquete'], $t['version']);
	}

	public function __construct($id_enquete, $version) {
		$this->id_enquete = bobs_element::cli($id_enquete);
		$this->version = bobs_element::cli($version);
		$this->arguments = ['id_enquete','version'];
	}
}
