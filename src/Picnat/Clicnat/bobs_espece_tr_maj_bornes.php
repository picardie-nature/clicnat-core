<?php
namespace Picnat\Clicnat;

class bobs_espece_tr_maj_bornes extends clicnat_travail implements i_clicnat_travail {
	public function executer() {
		bobs_espece::bornage(get_db());
	}
}
