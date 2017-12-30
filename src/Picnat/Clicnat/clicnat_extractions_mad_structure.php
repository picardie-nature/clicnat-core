<?php
namespace Picnat\Clicnat;

class clicnat_extractions_mad_structure extends bobs_extractions {
	public function autorise_structure($id_structure,$position=0) {
		self::cli($id_structure);

		if (!$this->ready()) {
			return false;
		}

		$sql = "select bob_citation_structure_ok($id_structure,citations.id_citation)
				from {$this->get_tables()}
				where {$this->get_jointures()}
				{$this->get_conditions()}
				and not exists(
					select 1 from structures_mad
					where structures_mad.id_citation=citations.id_citation
					and structures_mad.id_structure=$id_structure
				)
				and citations.id_citation>$position";

		$this->query($sql);

		return true;
	}

	public static function charge_xml($db, $xml, $id_utilisateur=false, $extraction=null) {
		if (is_null($extraction)) {
			$extraction = new clicnat_extractions_mad_structure($db);
		}
		return parent::charge_xml($db, $xml, $id_utilisateur, $extraction);
	}
}
