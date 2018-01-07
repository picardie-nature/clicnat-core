create or replace function bob_point_obs(espace_table varchar,p_id_espace integer) returns geometry immutable as $$
declare
	g geometry;
begin
	if espace_table = 'espace_point' then
		select the_geom into g from espace_point where id_espace=p_id_espace;
	elseif espace_table = 'espace_chiro' then
		select the_geom into g from espace_chiro where id_espace=p_id_espace;
	end if;
	return g;
end
$$ language plpgsql;

