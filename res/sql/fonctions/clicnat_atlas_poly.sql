
create or replace function clicnat_atlas_poly(p_srid integer, p_pas integer, p_x0 integer, p_y0 integer) returns geometry as $$
begin
	return st_setsrid(st_envelope(st_makeline(st_makepoint(p_x0*p_pas,p_y0*p_pas),st_makepoint((p_x0+1)*p_pas, (p_y0+1)*p_pas))),p_srid);
end
$$ language plpgsql;
