create type t_id_n as (
	id integer,
	n integer
);

create or replace function bob_bilan_diff_restreinte_annee(p_annee integer) returns integer as $$
declare
	id_obs_min observations.id_observation%type;
	id_obs_max observations.id_observation%type;
	id_n t_id_n;
	n integer;
	n_obs integer;
begin
	select min(id_observation),max(id_observation) into id_obs_min ,id_obs_max
		from observations 
		where extract('year' from date_creation) = p_annee;

	n := 0;
	for id_n in
		select id_observation,count(distinct observations_observateurs.id_utilisateur)
		from observations_observateurs,utilisateur
		where id_observation between id_obs_min and id_obs_max
		and utilisateur.id_utilisateur = observations_observateurs.id_utilisateur
		and utilisateur.diffusion_restreinte = true
		group by id_observation
	loop
		select count(distinct id_utilisateur) into n_obs
			from observations_observateurs
			where id_observation = id_n.id;

		-- si le nombre d'observateurs est plus grand
		-- c'est de la diffusion libre
		if n_obs = id_n.n then
			select count(distinct id_citation)+n into n from citations
			where id_observation = id_n.id;
		end if;

	end loop;

	return n;
end;
$$ language plpgsql;

create or replace function bob_bilan_diff_restreinte_jusque_fin(p_annee integer) returns integer as $$
declare
	id_obs_max observations.id_observation%type;
	id_n t_id_n;
	n integer;
	n_obs integer;
begin
	select max(id_observation) into id_obs_max
		from observations 
		where extract('year' from date_creation) = p_annee;

	n := 0;
	for id_n in
		select id_observation,count(distinct observations_observateurs.id_utilisateur)
		from observations_observateurs,utilisateur
		where id_observation <= id_obs_max
		and utilisateur.id_utilisateur = observations_observateurs.id_utilisateur
		and utilisateur.diffusion_restreinte = true
		group by id_observation
	loop
		select count(distinct id_utilisateur) into n_obs
			from observations_observateurs
			where id_observation = id_n.id;

		-- si le nombre d'observateurs est plus grand
		-- c'est de la diffusion libre
		if n_obs = id_n.n then
			select count(distinct id_citation)+n into n from citations
			where id_observation = id_n.id;
		end if;

	end loop;

	return n;
end;
$$ language plpgsql;

