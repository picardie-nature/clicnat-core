<?php
namespace Picnat\Clicnat;

trait clicnat_autocompletes {
	protected function array_autocomplete_espece() {
		$affiche_expert = isset($_GET['affiche_expert']);
		$forcer_absent = isset($_GET['forcer_absent']);
		bobs_element::cls($_GET['term']);
		$t = bobs_espece::recherche_par_nom($this->db, $_GET['term']);
		$r = array();
		$ids_ok = array();
		foreach ($t as $esp) {
			if (($esp['expert'] == 't') && !$affiche_expert)
				continue;
			if ($esp['absent_region'] == 't' && !$forcer_absent)
				continue;

			$ids_ok[] = $esp['id_espece'];
			$r[] = [
		    'label'=> isset($_GET['nohtml'])?"{$esp['nom_f']} {$esp['nom_s']}":$esp['nom_f'].'<br/><i>'.$esp['nom_s'].'</i>',
				'value' => $esp['id_espece'],
				'classe' => strtolower($esp['classe']),
				'n_citations' => $esp['n_citations']
			];
		}

		$t_obj = bobs_espece::index_recherche($this->db, $_GET['term']);
		foreach ($t_obj['especes'] as $obj) {
			if ($obj->absent_region && !$forcer_absent)
				continue;
			if ($obj->expert && !$affiche_expert)
				continue;

			if (in_array($obj->id_espece, $ids_ok))
				continue;

			$r[] = [
				'label' => isset($_GET['nohtml'])?"{$obj->nom_f} {$obj->nom_s}":"{$obj->nom_f} <br/><i>{$obj->nom_s}</i>",
				'value' => $obj->id_espece,
				'classe' => strtolower($obj->classe),
				'n_citations' => $obj->n_citations
			];
		}
		/*
		 * Pas assez rapide
		 *
		foreach (bobs_espece::synonymes_nom_sc_inpn($this->db, $_GET['term']) as $syn) {
			$obj = get_espece($this->db, $syn['id_espece']);
			if ($obj->absent_region && !$forcer_absent)
				continue;
			if ($obj->expert && !$affiche_expert)
				continue;

			$r[] = array(
				'label' => "{$syn['nom_sc']} (synonyme de {$obj->nom_s})",
				'value' => $obj->id_espece,
				'classe' => strtolower($obj->classe),
				'n_citations' => $obj->n_citations
			);
		}
		 */

		if (!isset($_GET['nohtml'])) {
			foreach($r as $k=>$v) {
				$r[$k]['label'] = "<img style=\"float:right;\" src=\"image/30x30_g_{$r[$k]['classe']}.png\"/>{$r[$k]['label']}";
			}
		}

		if (count($r) <= 20) {
			usort($r, "clicnat_cmp_tri_tableau_especes_n_citations");
		}
		return $r;
	}
}
