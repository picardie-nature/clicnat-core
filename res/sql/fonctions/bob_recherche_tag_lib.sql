create or replace function bob_recherche_tag_lib(texte varchar, borne_min integer, borne_max integer) returns setof tags as $$
declare
	mots text[];
	i integer;
	query tsquery;
	l tags%rowtype;
	dernier_mot text;
	s_orig text;
	s_dest text;
begin
	query := null;
	mots := regexp_split_to_array(lower(texte), e'\\s+');
	dernier_mot := '';
    	s_orig := 'àâçèéêîïôùû%';
	s_dest := 'aaceeeiiouu ';
	for i in 1..array_upper(mots, 1)
	loop
		dernier_mot := mots[i];
		if query is null then
			query := to_tsquery('french', mots[i]);
		else
			query := query && to_tsquery('french', mots[i]);
		end if;
	end loop;

	dernier_mot := translate(lower(dernier_mot), s_orig, s_dest);

	for l in select * from tags
		where (
			to_tsvector('french', lower(lib)) @@ query
			or (translate(lower(lib), s_orig, s_dest) like '%'||dernier_mot||'%' and length(dernier_mot)>3)
		) and (
			borne_a between borne_min and borne_max
			and borne_b between borne_min and borne_max

		)
	loop
		return next l;
	end loop;

	return;
end;
$$ language plpgsql;
