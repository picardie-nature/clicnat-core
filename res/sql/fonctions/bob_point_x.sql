-- ne pas utiliser : utiliser st_x directement
create or replace function bob_point_x(point geometry) returns text as $$
begin
	return ST_X(point);
end
$$ language plpgsql;
