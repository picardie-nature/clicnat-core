create or replace function bob_recherche_espece_nom_f(texte varchar) returns setof especes as $$
declare
    mots text[];
    i integer;
    query tsquery;
    l especes%rowtype;
    l2 v_especes_synonymes_inpn%rowtype;
    s_orig text;
    s_dest text;
begin
    s_orig := 'àâçèéêîïôùû%';
    s_dest := 'aaceeeiiouu ';
    query := null;
    mots := regexp_split_to_array(translate(lower(texte), s_orig, s_dest), e'\\s+');
    
    for i in 1..array_upper(mots, 1)
    loop
		if query is null then
	    	query := to_tsquery('french', mots[i]);
		else
	    	query := query && to_tsquery('french', mots[i]);
		end if;
    end loop;

    for l in select * from especes
		where to_tsvector('french', translate(lower(nom_f), s_orig, s_dest)) @@ query
		or translate(lower(nom_f), s_orig, s_dest) like translate(lower(texte), s_orig, s_dest)||'%'
    loop
		return next l;
    end loop;
    
    for l2 in select * from v_especes_synonymes_inpn
    			where to_tsvector('french', trim(lower(nom_vern))) @@ query
    loop
    	select into l * from especes where id_espece=l2.id_espece;
    	if l.nom_f ilike l2.nom_vern then
    		continue;
    	end if;
    	l.nom_f := left(l2.nom_vern||' (synonyme de '||l.nom_f||')',100);
    	return next l;
    end loop;
    
    return;
end;
$$ language plpgsql;
