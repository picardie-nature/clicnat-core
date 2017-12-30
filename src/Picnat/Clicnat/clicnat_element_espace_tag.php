<?php
namespace Picnat\Clicnat;

trait clicnat_element_espace_tag {
	use clicnat_element_tag;

	protected function __ajoute_tag($table_join, $champ, $id_tag, $id_ele, $intval, $textval) {
		self::cli($id_tag);
		self::cli($id_ele);
		$sql = 'insert into '.$table_join.' ('.$champ.', id_tag, v_int, v_text, espace_table) values ($1, $2, $3, $4, $5)';
		try {
			$data = array($id_ele, $id_tag, $intval, $textval, $this->table);
			bobs_qm()->query($this->db, $this->table.'_tags_set_e', $sql, $data);
		} catch (Exception $e) {
			bobs_log(sprintf("ERROR : can't add tag %d to %s %d", $id_tag, $this->table, $id_ele));
			throw $e;
		}
		$this->get_tags();
		bobs_log(sprintf("tag %d added to %s %d txt=\"%s\"", $id_tag, $this->table, $id_ele, $textval));
	}

	protected function __supprime_tag($table_join, $champ, $id_tag, $id_ele) {
		self::cli($id_tag);
		self::cli($id_ele);
		$sql = 'delete from '.$table_join.' where '.$champ.'=$1 and id_tag=$2 and espace_table=$3';
		bobs_qm()->query($this->db, $this->table.'_tag_sup_e', $sql, array($id_ele, $id_tag, $this->table));
	}
}
