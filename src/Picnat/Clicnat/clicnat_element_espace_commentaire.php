<?php
namespace Picnat\Clicnat;

trait clicnat_element_espace_commentaire {
	protected function __ajoute_commentaire($table, $champ_id, $id_element, $type_c, $commtr, $id_utilisateur) {
		return bobs_commentaire::ajout($this->db, $table, $champ_id, $id_element, $type_c, $commtr, $id_utilisateur);
	}

	protected function __get_commentaires($table, $champ_id, $id_element) {
		return bobs_commentaire::get_commentaires($this->db, $table, $champ_id, $id_element);
	}

	protected function __supprime_commentaire($table, $id_commentaire) {
		return bobs_commentaire::supprime_commentaire($this->db, $table, $id_commentaire);
	}
}
