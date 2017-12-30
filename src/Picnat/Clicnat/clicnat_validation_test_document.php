<?php
namespace Picnat\Clicnat;

class clicnat_validation_test_document extends clicnat_validation_test {
	public function evaluer() {
		$n_docs = count($this->citation->documents_liste());
		return [
			"passe" => $n_docs == 0,
			"message" => $n_docs>0?"$n_doc document(s) associé a valider par quelqu'un":"Pas de document associé : OK"
		];
	}
}
