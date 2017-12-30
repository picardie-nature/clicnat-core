<?php
namespace Picnat\Clicnat;

class bobs_rss_especes_villes extends bobs_rss {
	function __construct($db) 	{
		parent::__construct($db, "Liste des espèces par ville", "...", "http://www.picardie-nature.org", "/tmp/villes.xml");
	}

	function get_items() {
		$communes = bobs_espace_commune::get_all($this->db);
		$r = '';

		$n = 0;//DEBUG
		foreach ($communes as $commune) {
			$n++; //DEBUG
			$r .= "
			<item>
			<title>{$commune->nom}</title>
			<link>http://www.picardie-nature.org</link>
			<description>".($this->get_item($commune->id_espace))."</description>
			</item>
			";
			if ($n>6)
				break;
		}
		return $r;
	}

	function get_item($ville) {
		$sql =
			"select
				e.id_espece,nom_f,nom_s,classe,ordre
				from
				espace_point ep,
				observations o,
				citations c,
				especes e
			where
				ep.commune_id_espace is not null and
				o.id_espace=ep.id_espace and
				o.espace_table=$2 and
				o.id_observation=c.id_observation and
				o.brouillard = false and
				e.id_espece=c.id_espece and
				ep.commune_id_espace=$1
			group by
				e.id_espece,nom_f,nom_s,classe,ordre
			order by
				classe,ordre,nom_f,nom_s";
		$q = bobs_qm()->query($this->db,'bobs_rss_ville', $sql, array($ville, 'espace_point'));
		$sp = '';
		$last = null;
		while ($r = bobs_element::fetch($q)) {
			if ($last != $r['classe'] and !empty($last)) {
				$sp .= "<br>";
			}
			$sp .= empty($r['nom_f'])?$r['nom_s']:$r['nom_f'];
			$sp .= ' ';
		}
		return $sp;
	}
}
