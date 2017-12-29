<?php
namespace Picnat\Clicnat;

/**
 * @brief espèce / référentiel (TEMPORAIRE)
 *
 * C'est cette espèce qui fait référence dans la base,
 * les citations sont rapprochées avec celle-ci.
 *
 * Les autres référentiels, doivent être rapproché
 * à celui-ci.
 */
class bobs_referentiel extends bobs_element {
	function __construct($db, $id, $table='import_ref_pn') {
		parent::__construct($db, $table, 'id_import', $id);
	}

	public static function get_all($db, $id_espece_is_null=true, $oiseaux=false) {
		$all = array();
		$iein = '';
		$table = $oiseaux?'import_ref_pn_oiseaux':'import_ref_pn';
		if ($id_espece_is_null)
			$iein = ' where id_espece is null and cd_nom is null';
		$sql = sprintf('select * from %s %s', $table, $iein);
		$q = self::query($db, $sql);
		while ($r = self::fetch($q))
			$all[] = new bobs_referentiel($db, $r, $table);
		return $all;
	}

	public function set_id_espece($id) {
		if (empty($id))
			throw new exception('$id est vide');

		$this->id_espece = $id;
		$sql = sprintf('update %s set id_espece=%d where id_import=%d',
				$this->table,
				$id , $this->id_import);
		self::query($this->db, $sql, array('logmaj'=>true));
	}

	public function set_cd_nom($id) {
		if (empty($id))
			throw new exception('$id est vide');

		$this->cd_nom = $id;
		$sql = sprintf('update %s set cd_nom=%d where id_import=%d',
				$this->table,
				$id , $this->id_import);
		self::query($this->db, $sql, array('logmaj'=>true));
	}

	public function set_nom($nom) {
		$nom = trim($nom);
		if (empty($nom)) {
			throw new exception('$nom est vide');
		}
		$sql = sprintf("update %s set nom_sc='%s' where id_import=%d",
			$this->table,
			self::escape($nom), $this->id_import);
		self::query($this->db, $sql, ['logmaj'=>true]);
	}
}
