create schema repartitions;

create table repartitions.log (
	date_deb timestamp,
	date_fin timestamp,
	n_espece integer,
	max_id_citation integer
);

create table repartitions.repartition_especes (
	id_espece integer,
	srs integer,
	pas integer,
	x0 integer,
	y0 integer,
	annee integer,
	nc integer,
	the_geom geometry,
	primary key (id_espece,srs,pas,x0,y0,annee)
);

create or replace function repartition_espece(p_id_espece integer, p_pas integer, p_srs integer) returns integer as $$
begin
	create temp table tmp (x0 integer,y0 integer,annee integer,nc integer,primary key(x0,y0,annee));
	insert into tmp 

		select
                        x0,y0,
                      extract('year' from date_observation) as annee,
                        count(c.id_citation) as nc
                from
                        especes eroot
                        join (
				especes e join (
					citations c left join citations_tags on (citations_tags.id_tag=591 and c.id_citation=citations_tags.id_citation)
				) on e.id_espece=c.id_espece
			) on (e.borne_a between eroot.borne_a and eroot.borne_b or eroot.id_espece=e.id_espece)
                        join (
				observations o left join espace_polygon ep on (ep.id_espace=o.id_espace) 
				join espace_index_atlas eia on (eia.id_espace=o.id_espace and srid=p_srs and pas=p_pas)
			) on o.id_observation=c.id_observation
                where o.brouillard = false
                        and eroot.id_espece=p_id_espece
                        and coalesce(c.nb,0) != -1
                        and (coalesce(superficie,0)<=e.superficie_max or e.superficie_max=0)
                        and (c.indice_qualite > 2 or c.indice_qualite is null)
                        and citations_tags.id_tag is null
                group by x0,y0,extract('year' from date_observation);
	delete from repartitions.repartition_especes where id_espece=p_id_espece and pas=p_pas and srs=p_srs;
	insert into repartitions.repartition_especes (id_espece,srs,pas,x0,y0,annee,nc,the_geom)
		select p_id_espece,p_srs,p_pas,x0,y0,annee,nc,clicnat_atlas_poly(p_srs,p_pas,x0,y0) 
		from tmp;
	drop table tmp;
	return 1;
end;
$$ language plpgsql;
