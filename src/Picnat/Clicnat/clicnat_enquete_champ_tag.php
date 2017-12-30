<?php
namespace Picnat\Clicnat;

class clicnat_enquete_champ_tag extends clicnat_enquete_champ {
	public function formulaire($valeur=false) {
		if (!$valeur) $valeur = array();
		$tags = array();
		$liste = $this->doc->getElementsByTagName('tag_ref');
		$len = $liste->length;
		for ($p = 0;$p<$len; $p++) {
			$tags[] = get_tag_by_ref(get_db(), $liste->item($p)->nodeValue);
		}
		$r = '';
		foreach ($tags as $tag) {
			if (array_search($tag->id_tag, $valeur) !== false) $chk = 'checked=1';
			else $chk = '';
			$r .= "<input type=\"checkbox\" $chk name=\"{$this->nom}_{$tag->id_tag}\" id=\"ftt_{$this->nom}_{$tag->id_tag}\"/>";
			$r .= "<label for=\"ftt_{$this->nom}_{$tag->id_tag}\">{$tag->lib}</label><br/>";
		}
		return $r;
	}

	public function doc_champ_sauve($element, $data) {
		$element->setAttribute('nom', $this->nom);
		$element->setAttribute('type', 'multiple');
		$cles = array_keys($data);
		foreach ($cles as $cle) {
			if (preg_match("/^{$this->nom}_(\d+)$/", $cle, $m)) {
				if ($data[$cle] == 1) {
					$val = $element->ownerDocument->createElement('valeur', $m[1]);
					$element->appendChild($val);
				}
			}
		}
		return true;
	}
}
