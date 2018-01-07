drop function if exists clicnat_carre_atlas(integer,integer,geometry);

drop type if exists t_clicnat_carre_atlas;

create type t_clicnat_carre_atlas as (
	srid integer,
	pas integer,
	x0 integer,
	y0 integer
);

create function clicnat_carre_atlas(p_srid integer, pas integer, p_geom geometry) returns setof t_clicnat_carre_atlas as $$
declare
	carre t_clicnat_carre_atlas;
	carre_geom geometry;
	geom_srid integer;
	linestring geometry;
	point geometry;
	n integer;
	minx integer;
	maxx integer;
	miny integer;
	maxy integer;
	geom geometry;
begin
	carre.srid := p_srid;
	carre.pas := pas;
	-- si la geometrie est pas dans le referentiel de l'atlas
	-- on fait le necessaire

	geom_srid := st_srid(p_geom);
	if geom_srid != p_srid then
		geom := st_transform(p_geom, p_srid);
	else
		geom := p_geom;
	end if;
	
	-- si la geometrie est un point
	if geometrytype(geom) = 'POINT' then
		carre.x0 := trunc(st_x(geom)/pas);
		carre.y0 := trunc(st_y(geom)/pas);
		return next carre;
	else
	-- si la geometrie est autre chose
		linestring := st_boundary(st_envelope(geom));
		point := st_pointn(linestring, 1);
		minx := trunc(st_x(point)/pas);
		miny := trunc(st_y(point)/pas);
		point := st_pointn(linestring, 3);
		maxx := trunc(st_x(point)/pas);
		maxy := trunc(st_y(point)/pas);
		for carre.x0 in select generate_series(minx,maxx) loop
			for carre.y0 in select generate_series(miny,maxy) loop
				carre_geom := clicnat_atlas_poly(p_srid, pas, carre.x0, carre.y0);
				if st_intersects(geom, carre_geom) then
					return next carre;
				end if;
			end loop;
		end loop;
	end if;
end
$$ language plpgsql;
