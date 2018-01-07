create or replace function clicnat_poly_englob_liste_espaces (p_id_liste_espace integer) returns geometry stable as $$
declare 
	geom geometry;
begin
	RAISE NOTICE 'poly_englob_liste_espaces(%s)',p_id_liste_espace;
	select st_union(array_agg(g)) into geom from (
		select st_union(array_agg(the_geom)) as g from espace_polygon e,listes_espaces_data led where led.id_liste_espace=p_id_liste_espace and led.id_espace=e.id_espace
	union
		select st_union(array_agg(the_geom)) as g from espace_littoral e,listes_espaces_data led where led.id_liste_espace=p_id_liste_espace and led.id_espace=e.id_espace
	union
		select st_union(array_agg(st_transform(st_buffer(st_transform(the_geom,3857),10),4326))) as g
		from espace_line e,listes_espaces_data led where id_liste_espace=p_id_liste_espace and led.id_espace=e.id_espace
	union
		select st_union(array_agg(st_transform(st_buffer(st_transform(the_geom,3857),10),4326))) as g
		from espace_point e,listes_espaces_data led where id_liste_espace=p_id_liste_espace and led.id_espace=e.id_espace
	union
		select st_union(array_agg(st_transform(st_buffer(st_transform(the_geom,3857),10),4326))) as g
		from espace_chiro e,listes_espaces_data led where id_liste_espace=p_id_liste_espace and led.id_espace=e.id_espace
	union
		select st_union(array_agg(st_transform(st_buffer(st_transform(the_geom,3857),10),4326))) as g
		from espace_l93_5x5 e,listes_espaces_data led where id_liste_espace=p_id_liste_espace and led.id_espace=e.id_espace
	union
		select st_union(array_agg(st_transform(st_buffer(st_transform(the_geom,3857),10),4326))) as g
		from espace_l93_10x10 e,listes_espaces_data led where id_liste_espace=p_id_liste_espace and led.id_espace=e.id_espace
	union
		select st_union(array_agg(st_transform(st_buffer(st_transform(the_geom,3857),10),4326))) as g
		from espace_commune e,listes_espaces_data led where id_liste_espace=p_id_liste_espace and led.id_espace=e.id_espace
	union
		select st_union(array_agg(st_transform(st_buffer(st_transform(the_geom,3857),10),4326))) as g
		from espace_departement e,listes_espaces_data led where id_liste_espace=p_id_liste_espace and led.id_espace=e.id_espace
	union
		select st_union(array_agg(st_transform(st_buffer(st_transform(the_geom,3857),10),4326))) as g
		from espace_structure e,listes_espaces_data led where id_liste_espace=p_id_liste_espace and led.id_espace=e.id_espace
	) as sq;
	return geom;
end
$$ language plpgsql;

create or replace function clicnat_espace_in_poly_englob_liste_espaces (p_id_liste_espace integer) returns setof espace as $$
declare
	geom geometry;
	le espace%rowtype;
begin
	geom := clicnat_poly_englob_liste_espaces (p_id_liste_espace);
	for le in 
		(
			select espace.* from observations,espace_point e,espace 
			where espace.id_espace=e.id_espace and e.id_espace=observations.id_espace
			and st_intersects(geom,e.the_geom)
		union
			select espace.* from observations,espace_line e,espace 
			where espace.id_espace=e.id_espace and e.id_espace=observations.id_espace
			and st_intersects(geom,e.the_geom)
		union
			select espace.* from observations,espace_littoral e,espace 
			where espace.id_espace=e.id_espace and e.id_espace=observations.id_espace
			and st_intersects(geom,e.the_geom)
		union
			select espace.* from observations,espace_chiro e,espace 
			where espace.id_espace=e.id_espace and e.id_espace=observations.id_espace
			and st_intersects(geom,e.the_geom)
		union
			select espace.* from observations,espace_polygon e,espace 
			where espace.id_espace=e.id_espace and e.id_espace=observations.id_espace
			and st_intersects(geom,e.the_geom)
		union
			select espace.* from observations,espace_structure e,espace 
			where espace.id_espace=e.id_espace and e.id_espace=observations.id_espace
			and st_intersects(geom,e.the_geom)
		union
			select espace.* from observations,espace_departement e,espace 
			where espace.id_espace=e.id_espace and e.id_espace=observations.id_espace
			and st_intersects(geom,e.the_geom)
		union
			select espace.* from observations,espace_commune e,espace 
			where espace.id_espace=e.id_espace and e.id_espace=observations.id_espace
			and st_intersects(geom,e.the_geom)
		)
	loop
		return next le;
	end loop;
	return;
end;
$$ language plpgsql;
