<?php
namespace Picnat\Clicnat;

class clicnat_extraction_mad_tete_reseau extends bobs_extractions {
	public function autorise($utilisateur,$position=0) {
		self::cli($id_structure);

		if (!$this->ready())
			return false;

		$sql = "select bob_citation_ok({$utilisateur->id_utilisateur},citations.id_citation), count(*)
				from {$this->get_tables()}
				where {$this->get_jointures()}
				{$this->get_conditions()}
				group by bob_citation_ok";

		$q = $this->query($sql);

		$retour = ['nouveau' => 0, 'deja_present' => 0];

		while ($r = $this->fetch($q)) {
			if ($r['bob_citation_ok'] == 't') {
				$retour['nouveau'] = $r['count'];
			} else {
				$retour['deja_present'] = $r['count'];
			}
		}

		return $retour;
	}

	public static function charge_xml($db, $xml, $id_utilisateur=false, $extraction=null) {
		if (is_null($extraction)) {
			$extraction = new clicnat_extractions_mad_structure($db);
		}
		return parent::charge_xml($db, $xml, $id_utilisateur, $extraction);
	}
}
