
create or replace function bob_trouve_table_espace(p_id_espace integer) returns text as $$
declare
	n integer;
begin
	select coalesce(count(*),0) into n from espace_point where id_espace=p_id_espace;
	if n > 0 then
		return 'espace_point';
	end if;

	select coalesce(count(*),0) into n from espace_chiro where id_espace=p_id_espace;
	if n > 0 then
		return 'espace_chiro';
	end if;

	select coalesce(count(*),0) into n from espace_polygon where id_espace=p_id_espace;
	if n > 0 then
		return 'espace_polygon';
	end if;

	select coalesce(count(*),0) into n from espace_commune where id_espace=p_id_espace;
	if n > 0 then
		return 'espace_commune';
	end if;

	select coalesce(count(*),0) into n from espace_line where id_espace=p_id_espace;
	if n > 0 then
		return 'espace_line';
	end if;
	
	select coalesce(count(*),0) into n from espace_l93_10x10 where id_espace=p_id_espace;
	if n > 0 then
		return 'espace_l93_10x10';
	end if;

	select coalesce(count(*),0) into n from espace_l93_5x5 where id_espace=p_id_espace;
	if n > 0 then
		return 'espace_l93_5x5';
	end if;

	select coalesce(count(*),0) into n from espace_littoral where id_espace=p_id_espace;
	if n > 0 then
		return 'espace_littoral';
	end if;


	return null;
end;
$$ language plpgsql;

