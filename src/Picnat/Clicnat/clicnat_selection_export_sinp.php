<?php
namespace Picnat\Clicnat;

class clicnat_selection_export_sinp extends bobs_selection {
	/**
	 * @brief export SINP
	 * @return DOMDocument
	 */
	public function extraction() {
		$doc = new DOMDocument('1.0','utf-8');
		$doc->formatOutput = true;
		$root = $doc->createElement("Collection");
		$root->setAttributeNs(GML_NS_URL, 'gml:id', "selection{$this->id_selection}");
		foreach ($this->get_citations() as $citation) {
			$c_sinp = new clicnat_citation_export_sinp($this->db, $citation->id_citation);
			$root->appendChild($c_sinp->occurence($doc));
		}
		$doc->appendChild($root);
		return $doc;
	}
}
