<?php
namespace Picnat\Clicnat;

class clicnat_wfs_get_desc_feature_type extends clicnat_wfs_operation {
	public function reponse($version='1.0.0') {
		$doc = $this->newdomdoc();
		$root = $doc->createElementNS('http://www.w3.org/2001/XMLSchema','xsd:schema');
		$type_name = $this->get_type_name();

		$ele = $doc->createElementNS('http://www.w3.org/2001/XMLSchema','xsd:element');
		$ele->setAttribute('name', $type_name);
		$ele->setAttribute('type', "{$type_name}_Type");
		$root->appendChild($ele);

		if (preg_match('/liste_espace_(\d+)_(\w+)$/', $type_name)) {
			$root->appendChild($this->complex_type_liste_espaces($type_name,$doc));
			$root->setAttribute("targetNamespace", "http://www.clicnat.org/espace");
		}
		$doc->appendChild($root);
		return $doc;
	}

	public function complex_type_liste_espaces($tn, $doc) {
		if (!preg_match('/liste_espace_(\d+)_(\w+)$/', $tn, $m)) {
			throw new Exception("pas d'id");
		}

		$liste = new clicnat_listes_espaces($this->db, $m[1]);

		$ctype = $doc->createElementNS("http://www.w3.org/2001/XMLSchema","xsd:complexType");
		$ctype->setAttribute("name", "{$tn}_Type");

		$ccontent = $doc->createElementNS("http://www.w3.org/2001/XMLSchema","xsd:complexContent");
		$ext = $doc->createElementNS("http://www.w3.org/2001/XMLSchema","xsd:extension");
		$ext->setAttribute("base", "gml:AbstractFeatureType");
		$ext->setAttribute("nillable", "false");
		$seq = $doc->createElementNS("http://www.w3.org/2001/XMLSchema","xsd:sequence");
		// the_geom
		$ele =  $doc->createElementNS("http://www.w3.org/2001/XMLSchema","xsd:element");
		$ele->setAttribute("name","the_geom");
		$ele->setAttribute("type","gml:{$m[2]}PropertyType"); // alors type ou ref ?
		$seq->appendChild($ele);
		$attrs_base = array(
			array("type" => "int", "name" => "id_espace"),
			array("type" => "string", "name" => "reference"),
			array("type" => "string", "name" => "nom"),
			array("type" => "string", "name" => "classe")
		);
		$attrs_liste = $liste->attributs();
		$attrs = array_merge($attrs_liste, $attrs_base);
		foreach ($attrs as $attr) {
			$ele =  $doc->createElementNS("http://www.w3.org/2001/XMLSchema","xsd:element");
			$ele->setAttribute("name", $attr['name']);
			if ($attr['type'] == "int") {
				$ele->setAttribute("type", "xsd:integer");
			} else {
				$ele->setAttribute("type", "xsd:string");
			}
			$ele->setAttribute("minOccurs", 0);
			$ele->setAttribute("maxOccurs", 1);
			$seq->appendChild($ele);
		}
		$ext->appendChild($seq);
		$ccontent->appendChild($ext);
		$ctype->appendChild($ccontent);
		return $ctype;
	}
}
