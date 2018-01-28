create or replace function bob_departement_espace_point(espace_point_id integer) returns integer immutable as $$
declare
	id integer;
begin
	select ed.id_espace into id from espace_departement ed, espace_point ep 
		where st_contains(ed.the_geom , ep.the_geom)
		and ep.id_espace = espace_point_id;
	return id;
end
$$ language plpgsql;

