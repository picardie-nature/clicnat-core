create or replace function bob_recherche_import_observateur_nom(texte varchar) returns setof utilisateur as $$
declare
	mots text[];
	i integer;
	query tsquery;
	l utilisateur%rowtype;
begin
	query := null;
	mots := regexp_split_to_array(lower(texte), e'\\s+');

	for i in 1..array_upper(mots, 1)
	loop
		if query is null then
			query := to_tsquery('french', mots[i]);
		else
			query := query && to_tsquery('french', mots[i]);
		end if;
	end loop;

	for l in select * from utilisateur 
		where to_tsvector('french', lower(coalesce(nom,' ')||' '||coalesce(prenom, ' '))) @@ query
	loop
		return next l;
	end loop;

	return;
end;
$$ language plpgsql;
