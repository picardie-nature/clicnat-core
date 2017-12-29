<?php
namespace \Picnat\Clicnat;

/**
 * @brief Recherche des oiseaux nicheurs sur les données d'une sélection
 */
class bobs_aonfm_selection extends bobs_aonfm
{
	function run($id_selection)
	{
		bobs_element::query($this->db, 'drop table if exists aonfm');
		$sql = sprintf('select distinct selection_data.id_citation into temporary aonfm
				from selection_data,citations
				where id_selection=%d
				and selection_data.id_citation=citations.id_citation
				and citations.id_espece=%d', $id_selection, $this->espece->id_espece);

		$q = bobs_element::query($this->db, $sql);

		parent::run(true);
	}
}
