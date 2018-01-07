create or replace function bob_diffusion_restreinte(p_id_citation integer, p_id_utilisateurs integer[]) returns boolean as $$
declare
	obsobs observations_observateurs%rowtype;	
begin
	
	for obsobs in
		select oo.* 
		from observations_observateurs oo,
			citations c
		where oo.id_observation=c.id_observation
		and c.id_citation=p_id_citation
		and oo.id_observation=c.id_observation
	loop		
		-- si un des observateurs est en diffusion libre
		-- alors c'est pas une diffusion restreinte
		if not obsobs.id_utilisateur = any(p_id_utilisateurs) then
			return false;
		end if;
	end loop;
	-- il n'y avait que des observateurs en diffusion restreinte
	-- donc citation en diffusion restreinte.
	return true;
end;
$$ language plpgsql;