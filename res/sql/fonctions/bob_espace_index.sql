create or replace function bob_index_espace(p_tble varchar, p_id_espace integer, geom geometry) returns boolean as $$
declare
	commune espace_commune%rowtype;
	littoral espace_littoral%rowtype;
	departement espace_departement%rowtype;
	toponyme espace_toponyme%rowtype;
	toponyme_ok boolean;
	corine espace_corine%rowtype;
	carre_atlas t_clicnat_carre_atlas;
begin
	delete from espace_intersect where table_espace_obs=p_tble and id_espace_obs=p_id_espace;

	for commune in select * from espace_commune where st_intersects(the_geom, geom) loop
		insert into espace_intersect (table_espace_obs,id_espace_obs,table_espace_ref,id_espace_ref)
			values (p_tble,p_id_espace,'espace_commune', commune.id_espace);
	end loop;

	for departement in select * from espace_departement where st_intersects(the_geom, geom)  loop
		insert into espace_intersect (table_espace_obs,id_espace_obs,table_espace_ref,id_espace_ref)
			values (p_tble,p_id_espace,'espace_departement', departement.id_espace);
	end loop;

	for corine in select * from espace_corine where st_intersects(the_geom, geom) loop
		insert into espace_intersect (table_espace_obs,id_espace_obs,table_espace_ref,id_espace_ref)
			values (p_tble,p_id_espace,'espace_corine', corine.id_espace);
	end loop;

	for littoral in select * from espace_littoral where st_intersects(the_geom,geom) loop
		insert into espace_intersect (table_espace_obs,id_espace_obs,table_espace_ref,id_espace_ref)
			values (p_tble,p_id_espace,'espace_littoral', littoral.id_espace);
	end loop;

	if geometrytype(geom) = 'POINT' then
		toponyme_ok := false;
		for toponyme in select * from bob_toponymes_proches(geom,700) loop
			if toponyme_ok = false then
				insert into espace_intersect (table_espace_obs,id_espace_obs,table_espace_ref,id_espace_ref)
					values (p_tble,p_id_espace,'espace_toponyme', toponyme.id_espace);
				toponyme_ok := true;
			end if;
		end loop;
	end if;

	delete from espace_index_atlas where table_espace=p_tble and id_espace=p_id_espace;
	-- Grille en Lambert 93
	for carre_atlas in select * from clicnat_carre_atlas(2154, 10000, geom) loop
		insert into espace_index_atlas (table_espace, id_espace, srid, pas, x0, y0)
		values (p_tble, p_id_espace, carre_atlas.srid, carre_atlas.pas, carre_atlas.x0, carre_atlas.y0);
	end loop;
	for carre_atlas in select * from clicnat_carre_atlas(2154, 5000, geom) loop
		insert into espace_index_atlas (table_espace, id_espace, srid, pas, x0, y0)
		values (p_tble, p_id_espace, carre_atlas.srid, carre_atlas.pas, carre_atlas.x0, carre_atlas.y0);
	end loop;
	for carre_atlas in select * from clicnat_carre_atlas(2154, 1000, geom) loop
		insert into espace_index_atlas (table_espace, id_espace, srid, pas, x0, y0)
		values (p_tble, p_id_espace, carre_atlas.srid, carre_atlas.pas, carre_atlas.x0, carre_atlas.y0);
	end loop;

	
	-- Grille ETRS LAEA
	for carre_atlas in select * from clicnat_carre_atlas(3035, 10000, geom) loop
		insert into espace_index_atlas (table_espace, id_espace, srid, pas, x0, y0)
		values (p_tble, p_id_espace, carre_atlas.srid, carre_atlas.pas, carre_atlas.x0, carre_atlas.y0);
	end loop;
	for carre_atlas in select * from clicnat_carre_atlas(3035, 5000, geom) loop
		insert into espace_index_atlas (table_espace, id_espace, srid, pas, x0, y0)
		values (p_tble, p_id_espace, carre_atlas.srid, carre_atlas.pas, carre_atlas.x0, carre_atlas.y0);
	end loop;

	return true;
exception
	when others then
		raise notice 'id_espace % %',p_tble, p_id_espace;
	return false;
end;
$$ language plpgsql;
