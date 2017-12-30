<?php

namespace Picnat\Clicnat;

class clicnat_wfs_get_feature extends clicnat_wfs_operation {
	protected $filtre_type = false;

	public function reponse() {
		$type_name = $this->get_type_name();
		if (preg_match('/selection_(\d+)$/', $type_name)) {
			return $this->reponse_selection();
		} else if (preg_match('/liste_espace_(\d+)_(\w+)$/', $type_name)) {
			return $this->reponse_liste_espace();
		} else if (preg_match('/liste_espace_(\d+)$/', $type_name)) {
			return $this->reponse_liste_espace();
		}
	}

	public function get_liste_espaces() {
		$t = $this->get_type_name();
		if (!$t) {
			throw new Exception('pas trouvé typeName');
		}
		if (preg_match('/liste_espace_(\d+)$/', $t, $m)) {
			return new clicnat_listes_espaces($this->db, $m[1]);
		} else if (preg_match('/liste_espace_(\d+)_(\w+)$/', $t, $m)) {
			$this->filtre_type = $m[2];
			return new clicnat_listes_espaces($this->db, $m[1]);
		}
		throw new Exception('pas trouvé le numéro :'.$t);

	}

	public function reponse_liste_espace() {
		$s = $this->get_liste_espaces();

		$doc = new DOMDocument();
		$doc->formatOutput = true;
		$root = $doc->createElementNS("http://www.opengis.net/wfs",'wfs:FeatureCollection');
		$attrs = array(
			'version' => self::version,
			//'xmlns' => 'http://www.opengis.net/wfs',
			//'xmlns:espace' => 'http://www.clicnat.org/espace'
		);
		$srs = 4326;

		foreach ($attrs as $k => $v) {
			$root->setAttribute($k, $v);
		}

		if (!is_array($this->args)) {
			$r = $this->args->getElementsByTagName("Query");
			foreach ($r as $e) {
				if ($e->hasAttribute('srsName')) {
					$srs_str = $e->getAttribute('srsName');
					if (preg_match("/.*:(\d+)$/", $srs_str, $m)) {
						$srs = $m[1];
					}
				}
			}
		} else {
			if (isset($_GET['SRSNAME'])) {
				if (preg_match("/.*:(\d+)$/", $_GET['SRSNAME'], $m)) {
					$srs = $m[1];
				}
			}
		}

		$t = $this->get_type_name();

		if (empty($t)) {
			throw new Exception("typename vide");
		}
		foreach ($s->get_espaces() as $espace) {
			// recupere la géometrie
			$gml = $espace->get_geom_gml($srs);
			if (empty($gml))
				throw new Exception('pas de géométrie ? '.$espace->id_espace);
			$dgml = new DOMDocument();
			$dgml->loadXML('<top xmlns:gml="http://www.opengis.net/gml">'.$gml.'</top>');

			$fmemb = $doc->createElementNS("http://www.opengis.net/gml",'gml:featureMember');
			try {
				$cit = $doc->createElementNS("http://www.clicnat.org/espace","espace:$t");
			} catch (Exception $e) {
				echo "namespace : espace:$t";
				throw $e;
			}
			$cit->setAttribute("fid", "$t.{$espace->id_espace}");
			$the_geom = $doc->createElementNS("http://www.clicnat.org/espace",'espace:the_geom');
			$the_geom->appendChild($doc->importNode($dgml->firstChild->firstChild, true));
			$cit->appendChild($the_geom);
			$espace_attributs = $s->espace_attributs($espace->id_espace);
			foreach ($s->attributs() as $attr) {
				if ($attr['type'] == 'int') {
					$val = (int)isset($espace_attributs[$attr['name']])?$espace_attributs[$attr['name']]:0;
				} else {
					$val = isset($espace_attributs[$attr['name']])?$espace_attributs[$attr['name']]:'non définie';
				}
				//$cit->appendChild($doc->createElement("espace:{$attr['name']}", isset($espace_attributs[$attr['name']])?$espace_attributs[$attr['name']]:'non définie'));
				$cit->appendChild($doc->createElementNS("http://www.clicnat.org/espace","espace:{$attr['name']}", $val));
			}
			$cit->appendChild($doc->createElementNS("http://www.clicnat.org/espace",'espace:id_espace', $espace->id_espace));
			$cit->appendChild($doc->createElementNS("http://www.clicnat.org/espace",'espace:reference', $espace->reference));
			$cit->appendChild($doc->createElementNS("http://www.clicnat.org/espace",'espace:nom', $espace->nom));
			$cit->appendChild($doc->createElementNS("http://www.clicnat.org/espace",'espace:classe', $espace->get_table()));
			$fmemb->appendChild($cit);
			$root->appendChild($fmemb);
		}

		$doc->appendChild($root);

		return $doc;
	}


	public function reponse_selection() {
		$t = $this->get_type_name();
		if (!$t) {
			throw new Exception('pas trouvé typeName');
		}
		if (!preg_match('/selection_(\d+)$/', $t, $m)) {
			throw new Exception('pas trouvé le numéro :'.$t);
		}

		$t = "selection_{$m[1]}";
		$s = new bobs_selection($this->db, $m[1]);

		$doc = new DOMDocument();
		$doc->formatOutput = true;
		$root = $doc->createElement('wfs:FeatureCollection');
		$attrs = array(
			'version' => self::version,
			'xmlns' => 'http://www.opengis.net/wfs',
			'xmlns:wfs' => 'http://www.opengis.net/wfs',
			'xmlns:cit' => 'http://www.clicnat.org/citation'
		);

		foreach ($attrs as $k => $v) {
			$root->setAttribute($k, $v);
		}

		foreach ($s->get_citations() as $c) {
			// recupere la géometrie
			$gml = $c->get_observation()->get_espace()->get_geom_gml();
			$dgml = new DOMDocument();
			$dgml->loadXML('<top xmlns:gml="http://www.opengis.net/gml">'.$gml.'</top>');

			$fmemb = $doc->createElement('gml:featureMember');
			$cit = $doc->createElement("cit:$t");
			$cit->setAttribute("fid", "$t.{$c->id_citation}");
			$the_geom = $doc->createElement('cit:the_geom');
			$the_geom->appendChild($doc->importNode($dgml->firstChild->firstChild, true));
			$cit->appendChild($the_geom);
			$cit->appendChild($doc->createElement('cit:id_espece', $c->id_espece));
			$cit->appendChild($doc->createElement('cit:id_observation', $c->id_observation));
			$cit->appendChild($doc->createElement('cit:id_citation', $c->id_citation));
			$cit->appendChild($doc->createElement('cit:nb', $nb));
			$cit->appendChild($doc->createElement('cit:indice_qualite', $c->indice_qualite));
			$cit->appendChild($doc->createElement('cit:sexe', $c->sexe));
			$cit->appendChild($doc->createElement('cit:age', $c->age));
			$cit->appendChild($doc->createElement('cit:valide', $c->invalide()?'0':'1'));
			$cit->appendChild($doc->createElement('cit:espece', $c->get_espece()));
			$cit->appendChild($doc->createElement('cit:o_annee', strftime("%Y", strtotime($c->get_observation()->date_observation))));
			$cit->appendChild($doc->createElement('cit:o_mois', strftime("%m", strtotime($c->get_observation()->date_observation))));
			$cit->appendChild($doc->createElement('cit:o_jour', strftime("%d", strtotime($c->get_observation()->date_observation))));
			$cit->appendChild($doc->createElement('cit:o_date', $c->get_observation()->date_observation));
			$fmemb->appendChild($cit);
			$root->appendChild($fmemb);
		}

		$doc->appendChild($root);

		return $doc;
	}
}
