<?php
namespace Picnat\Clicnat;

/**
 * @brief créé un document SLD à partir de la définition d'une rampe pour un attribut
 *
 *
 *	 array(
 *		"styles" => array(
 *			"total" => array (
 *				"rules" => array(
 *					array(
 *						"min" => 0,
 *						"max" => 1,
 *						"fillcolor" => "#f7fcf5"
 *					),
 *					array(
 *						"min" => 1,
 *						"max" => 10,
 *						"fillcolor" => "#daedff"
 *					)
 *				),
 *			"titre" => "Nombre total d'espèces recensées par commune",
 *			"property" => "total"
 *		),
 *		"layername" => "liste_espace_224"
 *	);
 */
class clicnat_sld_rampe extends clicnat_sld implements i_clicnat_sld {
	/**
	 * @brief créer plusieurs rampes pour une liste de champs d'une sélection
	 * @param $liste_espaces objet clicnat_liste_espaces
	 * @param $regexp_param expression régulière que doit matcher le nom des attributs
	 * @param $resolution nombre de classe
	 * @param $teinte [0,360]
	 * @param $saturation [0,1]
	 * @param $valeur [0,1]
	 * @param $methode ["repartie","entre_min_et_max"]
	 * @return instance DOMDocument
	 **/
	public static function liste_espaces_attrs_min_max($liste_espaces, $regexp_param, $resolution, $teinte=120, $saturation=0.8, $valeur=0.9, $methode="repartie") {
		$style = [
			"styles" => [],
			"layername" => "liste_espace_{$liste_espaces->id_liste_espace}"
		];
		$resolution_init = $resolution;
		$teinte_init = $teinte%360;
		foreach ($liste_espaces->attributs() as $attr) {
			$resolution = $resolution_init;
			if (!preg_match($regexp_param, $attr['name']))
				continue;

			$sattr = ['rules' => array(), 'property' => $attr['name'] , 'titre' => $attr['name']];

			switch ($methode) {
				default:
				case "repartie":
					$valeurs = $liste_espaces->attribut_int_liste_valeurs_triees($attr['name']);
					$pas = (int)(count($valeurs)/($resolution-1));
					$teinte = $teinte_init;
					$pas_teinte = (int)(120/$resolution);
					for ($p=0; $p<$resolution; $p++) {
						$c = clicnat_couleur::tsv2rvb($teinte,$saturation,$valeur);
						$couleur = sprintf("rgb(%d,%d,%d)", 255*$c[0], 255*$c[1], 255*$c[2]);
						$teinte -= $pas_teinte;
						$dernier = $p==$resolution-1?1:0;
						if ($teinte < 0) $teinte = 0;
						$rule = array(
							"min" => $valeurs[min($p*$pas,count($valeurs)-1)],
							"max" => $valeurs[min(($p+1)*$pas,count($valeurs)-1)]+$dernier,
							"fillcolor" => $couleur
						);
						if ($rule['min'] == $rule['max'])
							continue;

						$sattr['rules'][] = $rule;
					}
					$style['styles'][$attr['name']] = $sattr;
					break;
				case "entre_min_et_max":
					list($min,$max) = $liste_espaces->attribut_int_min_max($attr['name']);

					if ($max-$min < $resolution)
						$resolution = $max-$min;

					if ($resolution == 0)
						$resolution = 1;

					$pas = (int)(($max-$min)/$resolution);
					$pas_teinte = (int)(120/$resolution);

					$teinte = $teinte_init;;
					for ($p=$min; $p<$max; $p+=$pas) {
						$c = clicnat_couleur::tsv2rvb($teinte,$saturation,$valeur);
						$couleur = sprintf("rgb(%d,%d,%d)", 255*$c[0], 255*$c[1], 255*$c[2]);
						$teinte -= $pas_teinte;
						if ($teinte < 0) $teinte = 0;
						$sattr['rules'][] = array(
							"min" => $p,
							"max" => $p+$pas,
							"fillcolor" => $couleur
						);
					}
					$style['styles'][$attr['name']] = $sattr;
					break;
				}
		}
		return self::doc($style);
	}

	public static function doc($params) {
		$layer_name = $params['layername'];

		if (empty($layer_name))
			throw new Exception("pas de param. layername");

		$doc = new DOMDocument();
		$root = $doc->createElementNS("http://www.opengis.net/sld","sld:StyledLayerDescriptor");
		$doc->appendChild($root);
		$doc->formatOutput = true;

		$layer = $doc->createElementNS("http://www.opengis.net/sld","sld:NamedLayer");

		// Layer
		$se_name = $doc->createElementNS("http://www.opengis.net/sld","sld:Name",$layer_name);
		$layer->appendChild($se_name);
		foreach ($params['styles'] as $style_name => $style) {
			$ustyle = $doc->createElementNS("http://www.opengis.net/sld","sld:UserStyle");
			$ustyle->appendChild($doc->createElementNS("http://www.opengis.net/sld","sld:Name",$style_name)); // nom du style
			$ustyle->appendChild($doc->createElementNS("http://www.opengis.net/sld","sld:Title",$style['titre'])); // nom du style
			$ustyle->appendChild($doc->createElementNS("http://www.opengis.net/sld","sld:Abstract"));
			$fts = $doc->createElementNS("http://www.opengis.net/sld","sld:FeatureTypeStyle");
			foreach ($style['rules'] as $rule) {
				$r = $doc->createElementNS("http://www.opengis.net/sld","sld:Rule");

				$r->appendChild($doc->createElementNS("http://www.opengis.net/sld","sld:Name", "de {$rule['min']} inclus à {$rule['max']} exclus"));
				$r->appendChild($doc->createElementNS("http://www.opengis.net/sld","sld:Title", "de {$rule['min']} inclus à {$rule['max']} exclus"));
				$r->appendChild($doc->createElementNS("http://www.opengis.net/sld","sld:Abstract"));

				$r->appendChild($doc->createElementNS("http://www.opengis.net/sld","sld:FeatureTypeName","Feature"));
				$r->appendChild($doc->createElementNS("http://www.opengis.net/sld","sld:SemanticTypeIdentifier","generic:geometry"));

				$filter = $doc->createElementNS("http://www.opengis.net/ogc","ogc:Filter");
				$and = $doc->createElementNS("http://www.opengis.net/ogc","ogc:And");
				$gt = $doc->createElementNS("http://www.opengis.net/ogc","ogc:PropertyIsGreaterThanOrEqualTo");
				$lt = $doc->createElementNS("http://www.opengis.net/ogc","ogc:PropertyIsLessThan");
				$gt->appendChild($doc->createElementNS("http://www.opengis.net/ogc","ogc:PropertyName",$style['property']));
				$lt->appendChild($doc->createElementNS("http://www.opengis.net/ogc","ogc:PropertyName",$style['property']));
				$gt->appendChild($doc->createElementNS("http://www.opengis.net/ogc","ogc:Literal",$rule['min']));
				$lt->appendChild($doc->createElementNS("http://www.opengis.net/ogc","ogc:Literal",$rule['max']));
				$and->appendChild($gt);
				$and->appendChild($lt);

				$filter->appendChild($and);
				$r->appendChild($filter);

				$polsym = $doc->createElementNS("http://www.opengis.net/sld","sld:PolygonSymbolizer");
				$fill = $doc->createElementNS("http://www.opengis.net/sld","sld:Fill");
				$svgp = $doc->createElementNS("http://www.opengis.net/sld","sld:SvgParameter", $rule['fillcolor']);
				$svgp->setAttribute("name", "fill");
				$cssp = $doc->createElementNS("http://www.opengis.net/sld","sld:CssParameter");
				$cssp->appendChild($doc->createElementNS("http://www.opengis.net/ogc","ogc:Literal",$rule['fillcolor']));
				$cssp->setAttribute("name", "fill");
				$fill->appendChild($svgp);
				$fill->appendChild($cssp);
				$polsym->appendChild($fill);
				$r->appendChild($polsym);

				$fts->appendChild($r);
			}
			$ustyle->appendChild($fts);
			$layer->appendChild($ustyle);
		}
		$root->appendChild($layer);
		return $doc;
	}
}
