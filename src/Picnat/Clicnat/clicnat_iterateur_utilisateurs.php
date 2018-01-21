<?php
namespace Picnat\Clicnat;

class clicnat_iterateur_utilisateurs extends clicnat_iterateur {
	public function current() {
		return get_utilisateur($this->db, $this->ids[$this->position]);
	}

	public function tri_par_nom_prenom() {
		$in = join(',', $this->ids);
		error_log($in);
		if (empty($in)) {
			return;
		}

		$q = bobs_element::query(
			$this->db,
			'select id_utilisateur
			from utilisateur
			where id_utilisateur in ('.$in.')
			order by nom,prenom'
		);

		$r = bobs_element::fetch_all($q);
		$this->ids = array_column($r, 'id_utilisateur');
		$this->position = 0;
	}
}
