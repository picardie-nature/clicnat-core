<?php
namespace Picnat\Clicnat;

class clicnat_index_mailles {
	/**
	 * @brief Constructeur
	 *
	 * @param $proj projection
	 * @param $pas  pas des mailles
	 */
	public function __construct($db, $proj, $pas) {
		$this->db = $db;
		$this->proj = $proj;
		$this->pas = $pas;
	}

	const sql_esp = '
		select e.id_espece, e.nom_f, e.nom_s, borne_a,remarquable,expert,n_citations as n_citations_total,count(id_citation) as n_citations,
			min(extract(year from date_observation)) as ymin,
			max(extract(year from date_observation)) as ymax
		from especes e,observations o,citations c,espace_index_atlas ei
		where e.id_espece=c.id_espece
		and c.id_observation=o.id_observation
		and ei.id_espace=o.id_espace
		and pas=$1
		and srid=$2
		and x0=$3
		and y0=$4
		and exclure_restitution=false
		group by e.id_espece, e.nom_f, e.nom_s
		order by borne_a
	';
	public function taxons($x0,$y0) {
		$q = bobs_qm()->query($this->db, 'idx_m__', self::sql_esp, [$this->pas, $this->proj, (int)$x0, (int)$y0]);
		return bobs_element::fetch_all($q);
	}
}
