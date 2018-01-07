create or replace function bob_citations_observateurs_str(integer) returns text as $$
declare
	ret text;
	u utilisateur%rowtype;
begin
	ret := '';
	for u in 
		select * from utilisateur,observations_observateurs,citations
		where observations_observateurs.id_observation=citations.id_observation
		and utilisateur.id_utilisateur=observations_observateurs.id_utilisateur
		and citations.id_citation=$1
	loop
		ret := ret||coalesce(u.nom,'');
		if u.prenom is not null then
			ret := ret||' '||u.prenom;
		end if;
		ret := ret||', ';
	end loop;
	ret := trim(both ', ' from ret);
	return ret;
end;
$$ language plpgsql;
