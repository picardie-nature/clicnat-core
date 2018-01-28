create or replace function bob_departement_espace_chiro(espace_chiro_id integer) returns integer immutable as $$
declare
	id integer;
begin
	select ed.id_espace into id from espace_departement ed, espace_chiro ec
		where st_contains(ed.the_geom , ec.the_geom)
		and ec.id_espace = espace_chiro_id;
	return id;
end
$$ language plpgsql;

