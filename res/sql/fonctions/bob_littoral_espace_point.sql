create or replace function bob_littoral_espace_point(espace_point_id integer) returns integer immutable as $$
declare
        id integer;
begin
        select el.id_espace into id from espace_littoral el, espace_point ep
                where contains(el.the_geom , ep.the_geom)
                and ep.id_espace = espace_point_id;
        return id;
end
$$ language plpgsql;