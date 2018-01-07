-- point : géométrie d'un point, peut importe la projection
-- dmax : distance max de recherche en métres

create or replace function bob_toponymes_proches(point geometry,dmax integer) returns setof espace_toponyme as $$
declare
	p1 geometry;
	t espace_toponyme%rowtype;
begin
	p1 := ST_Transform(point, 2154);

	for t in select * from espace_toponyme where ST_DWithin(p1, the_geom_rgf93, dmax) order by ST_Distance(p1, the_geom_rgf93)
	loop
		return next t;
	end loop;
	return;
end
$$ language plpgsql;
