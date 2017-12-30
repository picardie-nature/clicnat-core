<?php
namespace Picnat\Clicnat;

/**
 * @brief Document XML contenant des sorties
 */
class clicnat_doc_sorties extends DOMDocument {
	public function sorties() {
		$t = array();
		foreach ($this->getElementsByTagName('sortie') as $sortie) {
			$t[] = new clicnat_doc_ele_sortie($sortie);
		}
		return $t;
	}

	public function tableau_html($ressource) {
		foreach ($this->getElementsByTagName('sortie') as $sortie) {
			$s = new clicnat_doc_ele_sortie($sortie);
			$commune = $s->commune;
			if ($commune == ".") {
				$commune = '';
			}
			$imgreseau = "http://hyla.picardie-nature.org:8080/~nicolas/sorties/svg/reseaux/reseau_{$s->reseau_n}.png";
			fwrite($ressource, "
				<tr>
					<td class='c_date'>{$s->date}<br/>{$s->heure_depart}</td>
					<td class='c_description'>
						<div class=top>
							<h5>{$s->nom}</h5>
						</div>
						<div>
							<img style='float:right;' src='$imgreseau' />
						</div>

						{$s->description}
					</td>
					<td class='c_contact'>
						<div class=top>&nbsp;</div>
						{$s->orga_prenom} {$s->orga_nom} {$s->structure}<br/>
						{$s->mail_reservation} {$s->contact_reservation}
					</td>
					<td class='c_lieu'>
						<div class=top>{$commune} {$s->departement_n}</div>
						{$s->description_lieu}
					</td>
					<td class='c_materiel'>
						<div class=top>&nbsp;</div>
						{$s->materiels_lib} {$s->materiel_autre}
					</td>
				</tr>
			");
		}
	}

	public function images_personnes() {
		$txt = "";
		$fait = [];
		foreach ($this->getElementsByTagName('image_personne') as $image) {
			$nom = basename($image->getAttribute("href"));
			if (array_search($nom, $fait) !== false)
				continue;
			$txt .= $nom."\n";
			$fait[] = $nom;
		}
		return $txt;
	}

	public function illustrations() {
		$txt = "";
		$fait = [];
		foreach ($this->getElementsByTagName('sortie') as $sortie) {
			$id = basename($sortie->getAttribute("id"));
			if (array_search($id, $fait) !== false) {
				continue;
			}
			$txt .= "sortie-{$id}.eps\n";
			$fait[] = $id;
		}
		return $txt;
	}

	public function sorties_par_cellule() {
		$txt = "";
		$cellules = [];

		$q = bobs_qm()->query(get_db(), 'l_reseau2', 'select * from sortie_reseau', array());
		$t = bobs_element::fetch_all($q);
		$reseaux = [];
		foreach ($t as $r) {
			$reseaux[$r['id_sortie_reseau']] = $r['lib'];
		}

		foreach ($this->sorties() as $sortie) {
			$gx = $sortie->grille_x;
			if (!empty($gx)) {
				$c = sprintf("%s%02d",$sortie->grille_x, $sortie->grille_y);
				if (!isset($cellules[$c])) {
					$cellules[$c] = array();
				}
				$cellules[$c][] = $sortie;
			}
		}
		ksort($cellules);
		foreach ($cellules as $c) {
			$txt .= $c[0]->grille_x."".$c[0]->grille_y." :";
			foreach ($c as $sortie) {
				$txt .= " {$reseaux[$sortie->reseau_n]} - {$sortie->nom} #{$sortie->id_sortie},";
			}
			$txt = trim($txt,',');
			$txt .= "\n";
		}

		return $txt;
	}

	public function geojson() {
		$geojson = array(
			"type" => "FeatureCollection",
			"features" => array()
		);

		foreach ($this->sorties() as $s) {
			$geojson['features'][] = array(
				"type" => "Feature",
				"geometry" => array(
					"type" => "Point",
					"coordinates" => array((float)$s->longitude, (float)$s->latitude),
				),
				"properties" => array(
					"date" => $s->date(),
					"date_txt" => $s->date,
					"nom" => $s->nom,
					"description" => $s->description,
					"sortie_type" => $s->sortie_type,
					"sortie_type_n" => $s->sortie_type_n,
					"pole_n" => $s->pole_n,
					"pole_couleur" => $s->pole_couleur
				)
			);
		}
		return json_encode($geojson);
	}
}
