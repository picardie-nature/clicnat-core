<?php
namespace Picnat\Clicnat;

class clicnat_aohfm {
	public static function xml_atlas_ornitho($db) {
		$sql = "select * from referentiel_especes_tiers where tiers='visionature'";
		$q = bobs_qm()->query($db, 'aonfm_visio_gesv', $sql, array());
		$referentiel = array();
		$id_especes_manquant = array();
		$log = fopen("/tmp/correspondances_a_faire.txt","w");
		while ($e = bobs_element::fetch($q)) {
			$referentiel[$e['id_espece']] = $e['id_tiers'];
		}

		$doc = new DOMDocument('1.0', 'utf-8');
		$doc->formatOutput = true;
		$data = $doc->createElement("data");
		$doc->appendChild($data);
		$annee_fin = strftime("%Y");
		for ($annee=2009;$annee<=$annee_fin;$annee++) {
			$year = $doc->createElement("year");
			$year->setAttribute("value",$annee);
			$data->appendChild($year);
			$carres = bobs_espace_l93_10x10::tous($db);
			$cells = $doc->createElement("cells");
			$year->appendChild($cells);
			foreach ($carres as $carre) {
				$cell = $doc->createElement("cell");
				$cell->setAttribute("name", $carre['nom']);
				$cells->appendChild($cell);
				$espace = bobs_espace_l93_10x10::get_by_nom($db, $carre['nom']);
				$oiseaux = $espace->get_oiseaux_hivernant_saison($annee);
				foreach ($oiseaux as $oiseau) {
					$espece = get_espece($db, $oiseau['id_espece']);
					$specie = $doc->createElement('specie');
					if (!array_key_exists($oiseau['id_espece'],$referentiel)) {
						if (array_search($oiseau['id_espece'], $id_especes_manquant) === false) {
							$id_especes_manquant[] = $oiseau['id_espece'];
							$nom_f = str_replace(";",",",$espece->nom_f);
							fwrite($log, "{$oiseau['id_espece']};$nom_f;{$espece->nom_s}\n");
						}
						continue;
					}
					$specie->setAttribute('id', $referentiel[$oiseau['id_espece']]);
					$cell->appendChild($specie);
				}
			}
		}
		fclose($log);
		return $doc->saveXML();
	}
}
?>
