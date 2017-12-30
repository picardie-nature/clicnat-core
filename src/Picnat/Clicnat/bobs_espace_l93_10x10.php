<?php
namespace Picnat\Clicnat;

class bobs_espace_l93_10x10 extends bobs_poly {
	function __construct($db, $id, $table='espace_l93_10x10') {
	    parent::__construct($db, $id, $table);
	}


	public function liste_communes() {
		$sql = 'select * from v_commune_l93_10x10 where carre=$1 order by commune';
		$q = bobs_qm()->query($this->db, 'atlas_10x10_communes', $sql, array($this->nom));
		return self::fetch_all($q);
	}

	public static function get_by_nom($db, $nom) {
		self::cls($nom, self::except_si_vide);
		return self::__get_by_nom($db, 'espace_l93_10x10', 'bobs_espace_l93_10x10', $nom);
	}

	/**
 	 * @todo passage a espace_intesect
	 */
	public function get_especes() {
		$sql = 'select distinct e.id_espece,e.nom_f,e.nom_s,systematique
			from especes e,citations c,espace_point p,observations o
			where o.id_observation=c.id_observation and p.id_espace=o.id_espace
			and o.espace_table=\'espace_point\' and o.brouillard=false
			and e.id_espece=c.id_espece
			and p.l93_10x10_id_espace=$1
			order by systematique';

		$q = bobs_qm()->query($this->db, 'l93.10.10.especes', $sql, array($this->id_espace));
		return self::fetch_all($q);
	}

	/**
	 * @brief hiverants du carré (ne porte pas bien son nom)
	 */
	public function get_oiseaux_hivernant_2010_2011($annees="2009,2010,2011,2012,2013") {
		$sql = 'select distinct e.id_espece,e.nom_f,e.nom_s,systematique
			from especes e,citations c,espace_point p,observations o
			where o.id_observation=c.id_observation and p.id_espace=o.id_espace
			and coalesce(c.nb,0)>=0
			and o.espace_table=\'espace_point\' and o.brouillard=false
			and e.id_espece=c.id_espece
			and p.l93_10x10_id_espace=$1
			and extract(\'year\' from date_observation) in ('.$annees.')
			and extract(\'month\' from date_observation) in (12,1)
			and date_observation > \'2009-01-31\'
			and c.id_citation not in (select id_citation from citations_tags where id_tag in (591,126))
			and ((c.indice_qualite >= 3) or c.indice_qualite is null)
			and classe = \'O\'
			order by systematique';

		$q = bobs_qm()->query($this->db, md5('l93.10.10.o.hiv'.$annees), $sql, array($this->id_espace));
		return self::fetch_all($q);
	}

	/**
	 * @brief saison dec y a janv y+1
	 */
	public function get_oiseaux_hivernant_saison($annee) {
		$sql = 'select distinct e.id_espece,e.nom_f,e.nom_s,systematique
			from especes e,citations c,espace_point p,observations o
			where o.id_observation=c.id_observation and p.id_espace=o.id_espace
			and coalesce(c.nb,0)>=0
			and o.espace_table=\'espace_point\' and o.brouillard=false
			and e.id_espece=c.id_espece
			and p.l93_10x10_id_espace=$1
			and (
				(extract(\'year\' from date_observation) = $2 and extract(\'month\' from date_observation) = 12)
				or
				(extract(\'year\' from date_observation) = $3 and extract(\'month\' from date_observation) = 1)
			)
			and date_observation > \'2009-01-31\'
			and c.id_citation not in (select id_citation from citations_tags where id_tag in (591,126))
			and ((c.indice_qualite >= 3) or c.indice_qualite is null)
			and classe = \'O\'
			order by systematique';

		$q = bobs_qm()->query($this->db, md5('l93.10.10.o.hiv2'.$annee), $sql, array($this->id_espace,$annee,$annee+1));
		return self::fetch_all($q);
	}


	public function get_oiseaux_nicheurs() {
		return bobs_aonfm::especes_carre($this->db, $this->id_espace);
	}

	public static function get_nb_especes_carres_hivernants_2010_2011($db) {
		$sql = 'select distinct count(distinct c.id_espece) as n, ec.nom ,ec.id_espace
			from observations o, citations c,especes e,
			espace_point ep right outer join espace_l93_10x10 ec on ep.l93_10x10_id_espace=ec.id_espace
			where o.id_espace=ep.id_espace and o.espace_table=\'espace_point\'
			and o.id_observation=c.id_observation and o.brouillard=false
			and coalesce(c.nb,0)>=0 and c.id_espece=e.id_espece
			and extract(\'year\' from date_observation) in (2009,2010,2011,2012,2013)
			and extract(\'month\' from date_observation) in (1,12)
			and date_observation > \'2009-01-31\'
			and c.id_citation not in (select id_citation from citations_tags where id_tag in (591,126))
			and ((c.indice_qualite >= 3) or c.indice_qualite is null)
			and c.id_espece=e.id_espece and e.classe=\'O\'
			group by ec.nom,ec.id_espace';

		$q = bobs_qm()->query($db, 'l93.10.10.lo.hiv', $sql, array());
		return self::fetch_all($q);
	}

	public static function tous($db) {
		$sql = 'select distinct ec.id_espace, ec.nom, astext(ec.the_geom) as wkt
			from espace_l93_10x10 ec, espace_departement ed
			where ed.nom in (\'AISNE\',\'OISE\',\'SOMME\')
			and intersects(ed.the_geom, ec.the_geom)';
		$q = bobs_qm()->query($db, 'l93.10.10.all', $sql, array());
		return self::fetch_all($q);
	}

	public function responsables_carre() {
		$sql = 'select o.nom,o.prenom,o.id_utilisateur,ue.decideur_aonfm
			from utilisateur o,utilisateur_espace_l93_10x10 ue
			where ue.id_utilisateur=o.id_utilisateur
			and ue.id_espace=$1';
		$q = bobs_qm()->query($this->db, 'l93.10.10.ul', $sql, array($this->id_espace));
		return self::fetch_all($q);
	}
}
