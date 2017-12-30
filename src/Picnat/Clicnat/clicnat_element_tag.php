<?php
namespace Picnat\Clicnat;

trait clicnat_element_tag {
	protected function __ajoute_tag($table_join, $champ, $id_tag, $id_ele, $intval, $textval) {
		self::cli($id_tag);
		self::cli($id_ele);
		$sql = 'insert into '.$table_join.' ('.$champ.', id_tag, v_int, v_text) values ($1, $2, $3, $4)';
		try {
			bobs_qm()->query($this->db, $this->table.'_tags_set', $sql, array($id_ele, $id_tag, $intval, $textval));
		} catch (Exception $e) {
			bobs_log(sprintf("ERROR : can't add tag %d to %s %d", $id_tag, $this->table, $id_ele));
			throw $e;
		}
		unset($this->tags);
		$this->get_tags();
		bobs_log(sprintf("tag %d added to %s %d", $id_tag, $this->table, $id_ele));
	}

	public function ajoute_tag($id_tag, $intval=null, $textval=null) {
		throw new Exception('doit être implémentée');
	}

	protected function __supprime_tag($table_join, $champ, $id_tag, $id_ele) {
		self::cli($id_tag);
		self::cli($id_ele);
		$sql = 'delete from '.$table_join.' where '.$champ.'=$1 and id_tag=$2';
		bobs_qm()->query($this->db, $this->table.'_tag_sup', $sql, array($id_ele, $id_tag));
		unset($this->tags);
		$this->get_tags();
		bobs_log(sprintf("tag %d removed from %s %d", $id_tag, $this->table, $id_ele));
	}

	public function supprime_tag($id_tag) {
		throw new Exception('doit être implémentée');
	}

	public function get_tag($id_tag) {
		self::cli($id_tag);

		if (!isset($this->tags))
			$this->get_tags();

		if (count($this->tags) > 0 && is_array($this->tags))
			foreach ($this->tags as $tag)
			if ($tag['id_tag'] == $id_tag)
			return $tag;

		return null;
	}

	public function a_tag($id_tag) {
		return !is_null($this->get_tag($id_tag));
	}

	protected function __get_tags($table, $id, $where) {
		$sql = "select tags.lib, tags.id_tag, a_chaine, a_entier, v_int, v_text, tags.ref
				from tags, $table
				where tags.id_tag = $table.id_tag $where";
		$q = bobs_qm()->query($this->db, $table.'_gtag', $sql, array($id));
		return self::fetch_all($q);
	}

	public function get_tags() {
		throw new Exception('doit être implémentée');
	}
}
