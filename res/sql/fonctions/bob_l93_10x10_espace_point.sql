create or replace function bob_l93_10x10_espace_point(espace_point_id integer) returns integer immutable as $$
declare
	id integer;
begin
	select ea.id_espace into id from espace_l93_10x10 ea, espace_point ep
		where contains(ea.the_geom , ep.the_geom)
		and ep.id_espace = espace_point_id;
	return id;
end
$$ language plpgsql;