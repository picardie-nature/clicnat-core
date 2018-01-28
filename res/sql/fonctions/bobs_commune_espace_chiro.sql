create or replace function bob_commune_espace_chiro(espace_chiro_id integer) returns integer immutable as $$
declare
        id integer;
begin
        select ec.id_espace into id from espace_commune ec, espace_chiro ep
                where st_contains(ec.the_geom , ep.the_geom)
                and ep.id_espace = espace_chiro_id;
        return id;
end
$$ language plpgsql;
