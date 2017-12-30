<?php

namespace Picnat\Clicnat;

/**
 * @brief Association localisation - utilisateur
 */
class bobs_utilisateur_repertoire extends bobs_tests {
	public $id_utilisateur;
	public $table_espace;
	public $id_espace;
	public $date_modification;

	/**
	 * @brief enregistre la relation en objet carto et l'utilisateur
	 * @param ressource $db
	 * @param integer $id_utilisateur
	 * @param string $table_espace
	 * @param integer $id_espace
	 */
	public static function insert($db, $id_utilisateur, $table_espace, $id_espace) {
		self::cls($table_espace);
		self::cli($id_utilisateur);
		self::cli($id_espace);

		$sql = 'insert into utilisateur_repertoire (id_utilisateur,table_espace,id_espace)
				values ($1,$2,$3)';

		return bobs_qm()->query($db, 'utilisateur_r_ins', $sql, array($id_utilisateur, $table_espace, $id_espace));
	}

	const tri_par_nom = 1;
	const tri_par_date = 2;

	/**
	 * @brief liste les objets du répertoire d'un utilisateur
	 * @param ressource $db
	 * @param integer $id_utilisateur
	 * @return array
	 */
	public static function liste_utilisateur($db, $id_utilisateur, $tri=self::tri_par_nom) {
		self::cli($id_utilisateur);

		$sql = 'select utilisateur_repertoire.*,espace.nom from utilisateur_repertoire,espace
					where utilisateur_repertoire.id_utilisateur=$1
					and espace.id_espace=utilisateur_repertoire.id_espace';
		switch ($tri) {
			case self::tri_par_nom:
				$sql .= ' order by espace.nom';
				break;
			case self::tri_par_date:
				$sql .= ' order by date_association desc';
				break;
		}
		$q = bobs_qm()->query($db, 'utilisateur_r_lu'.$tri, $sql, array($id_utilisateur));
		return bobs_element::fetch_all($q);
	}

	const sql_suppr = 'delete from utilisateur_repertoire where id_utilisateur=$1 and table_espace=$2 and id_espace=$3';

	public static function supprime($db, $id_utilisateur, $table_espace, $id_espace) {
		self::cls($table_espace);
		return bobs_qm()->query($db, 'utilisateur_d_rep', self::sql_suppr, array((int)$id_utilisateur, $table_espace, (int)$id_espace));
	}
}
