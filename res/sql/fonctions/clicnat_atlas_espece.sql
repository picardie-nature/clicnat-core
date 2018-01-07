drop function if exists clicnat_atlas_espece(integer[], integer, integer);

drop type if exists t_clicnat_atlas_espece;

create type t_clicnat_atlas_espece as (
	x0 integer,
	y0 integer,
	the_geom geometry,
	min_year integer,
	max_year integer,
	n_citation integer
);

create function clicnat_atlas_espece(id_especes integer[], p_srid integer, p_pas  integer) returns setof t_clicnat_atlas_espece as $$
declare
	carre t_clicnat_atlas_espece;
begin
	for carre in select eia.x0,eia.y0,null,
				min(extract('year' from date_observation)),
				max(extract('year' from date_observation)),
				count(id_citation) 
			from citations c,observations o,espace_index_atlas eia
			where eia.srid=p_srid and eia.pas=p_pas 
				and eia.id_espace=o.id_espace 
				and o.id_observation=c.id_observation 
				and c.id_espece = any(id_especes)
				and o.brouillard = false
			    	and c.nb != -1
    				and coalesce(c.indice_qualite,4) > 2 
				and c.id_citation not in (
                			select ct.id_citation from citations c,citations_tags ct 
					where ct.id_citation = c.id_citation and ct.id_tag in (591,592) and id_espece = any(id_especes)
        			)
			group by eia.x0,eia.y0
	loop
		carre.the_geom = st_setsrid(st_envelope(st_collect(st_makepoint(carre.x0*p_pas,carre.y0*p_pas),st_makepoint((carre.x0+1)*p_pas, (carre.y0+1)*p_pas))), p_srid);
		return next carre;
	end loop;
end;
$$ language plpgsql;
