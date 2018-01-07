-- Cette fonction extrait les citations des utilisateurs en diffusion restreinte dans une sélection

create or replace function bob_selection_diffusion_restreinte(p_id_selection integer, p_id_utilisateurs integer[]) returns setof citations as $$
declare
	r_citation citations%rowtype;
	obsobs observations_observateurs%rowtype;
	observateurs integer[];
	observateur integer;
	suppression boolean;
	i integer;
begin
	for r_citation in 
		select * 
		from citations, selection_data
		where selection_data.id_citation=citations.id_citation
		and selection_data.id_selection=p_id_selection
	loop
		observateurs := '{}';

		for obsobs in
			select * from observations_observateurs
			where id_observation=r_citation.id_observation
		loop
			observateurs := observateurs || array[obsobs.id_utilisateur];
		end loop;

		if array_upper(observateurs, 1) <= array_upper(p_id_utilisateurs, 1) then
			suppression := true;
			for i in 1..array_upper(observateurs, 1)
			loop
				if not observateurs[i] = any(p_id_utilisateurs) then
					suppression := false;
				end if;
			end loop;
			if suppression then
				return next r_citation;
			end if;
		end if;
	end loop;
	return;
end;
$$ language plpgsql;
