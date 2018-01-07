create type t_mix_ac as (
	id_espece integer,
	the_geom geometry,
	date_obs date
);

create or replace function bob_selection_mix_annee(p_id_selection integer, p_pas integer, p_proj integer) returns integer as $$
declare
	mix_ac t_mix_ac;
	e_annee integer;
	e_x0 integer;
	e_y0 integer;
	pol geometry;
	ligne geometry;
	n integer;
	n_insert integer;
begin
	n_insert := 0;
	delete from selection_mix_annees 
		where id_selection=p_id_selection
		and pas=p_pas
		and srid=p_proj;

	for mix_ac in 
		select c.id_espece,espace_point.the_geom,date_observation
			from observations o,citations c,selection_data sd,espace_point
			where sd.id_selection=p_id_selection
			and c.id_citation=sd.id_citation
			and o.id_observation=c.id_observation
			and espace_point.id_espace=o.id_espace
			and o.espace_table = 'espace_point'
			and coalesce(c.nb,0) != -1
		union
		select c.id_espece,espace_chiro.the_geom,date_observation
			from observations o,citations c,selection_data sd,espace_chiro
			where sd.id_selection=p_id_selection
			and c.id_citation=sd.id_citation
			and o.id_observation=c.id_observation
			and espace_chiro.id_espace=o.id_espace
			and o.espace_table = 'espace_chiro'
			and coalesce(c.nb,0) != -1
	loop
		e_annee := extract('year' from mix_ac.date_obs);
		e_x0 := st_x(transform(mix_ac.the_geom, p_proj))::integer - (st_x(transform(mix_ac.the_geom, p_proj))::integer%p_pas);
		e_y0 := st_y(transform(mix_ac.the_geom, p_proj))::integer - (st_y(transform(mix_ac.the_geom, p_proj))::integer%p_pas);

		select count(*) into n from selection_mix_annees 
			where id_selection=p_id_selection
			and pas=p_pas
			and srid=p_proj
			and annee=e_annee
			and id_espece=mix_ac.id_espece
			and x0=e_x0
			and y0=e_y0;

		if n <> 1 then
			ligne := st_makeline(st_point(e_x0::float,e_y0::float), st_point((e_x0+p_pas)::float, e_y0::float));
			ligne := st_addpoint(ligne, st_point((e_x0+p_pas)::float, (e_y0+p_pas)::float));
			ligne := st_addpoint(ligne, st_point(e_x0::float, (e_y0+p_pas)::float));
			ligne := st_addpoint(ligne, st_point(e_x0::float, e_y0::float));
			pol := st_polygon(ligne, p_proj);
			insert into selection_mix_annees (id_selection,x0,y0,pas,srid,annee,id_espece,the_geom)
			values (p_id_selection,e_x0,e_y0,p_pas,p_proj,e_annee,mix_ac.id_espece, pol);
			n_insert := n_insert + 1;
		end if;
	end loop;
	return n_insert;
end;
$$ language plpgsql;
