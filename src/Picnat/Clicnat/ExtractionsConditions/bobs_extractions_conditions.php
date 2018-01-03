<?php
namespace Picnat\Clicnat\ExtractionsConditions;
/**
 * @brief Condition d'une extraction
 */
abstract class bobs_extractions_conditions {
	protected $extraction;
	protected $arguments;
	protected $visible_sur_poste;

	const poste = false;
	const qg = true;
	const clicnat1 = false; // ne montrer que dans clicnat 1.x

	public function __construct() {
		$this->arguments = [];
		$this->visible_sur_poste = true;
	}

	public function __toString() {
		return $this->get_titre();
	}

	public function ready() {
		return true;
	}

	public static function get_titre() {
		return 'inconnu';
	}

	public function get_sql() {
		return '';
	}

	public static function get_html() {
		return '';
	}

	public function get_tables() {
		return [];
	}

	public static function new_by_array($t) {
		throw new \Exception('doit être réimplémentée');
	}

	public function set_extraction($extraction) {
		$this->extraction = $extraction;
	}

	public function sauve_xml($doc, $element) {
		$condition = $doc->createElement('condition');
		$className = end(explode("\\", get_class($this)));
		$condition->appendChild($doc->createElement('classe', $className));
		if (count($this->arguments) > 0) {
			foreach ($this->arguments as $arg) {
				$xarg = $doc->createElement('argument', $this->$arg);
				$xarg->setAttribute('nom', $arg);
				$condition->appendChild($xarg);
			}
		}
		$element->appendChild($condition);
		return true;
	}
}
