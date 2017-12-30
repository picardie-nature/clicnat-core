<?php
namespace Picnat\Clicnat;

/**
 * @brief Observateur d'une mangeoire
 */
class clicnat_mangeoire_observateur extends bobs_utilisateur {
	const sql_liste_mangeoire = 'select * from espace_point ep,espace_tags et where et.id_espace=ep.id_espace and et.id_tag=$1 and et.v_int=$2';

	public function get_mangeoires() {
		$t = array();
		$tag = clicnat_mangeoire::tag_mangeoire($this->db);
		$q = bobs_qm()->query($this->db, 'mangeoire_lm', self::sql_liste_mangeoire, array($tag->id_tag, $this->id_utilisateur));
		while ($r = self::fetch($q))
			$t[] = new clicnat_mangeoire($this->db, $r);
		return $t;
	}
}
