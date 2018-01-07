-- ne pas utiliser : utiliser st_y directement
create or replace function bob_point_y(point geometry) returns text as $$
begin
	return ST_Y(point);
end
$$ language plpgsql;
