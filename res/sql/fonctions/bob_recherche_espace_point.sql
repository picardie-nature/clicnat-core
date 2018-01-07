create or replace function bob_recherche_espace_point(texte varchar) returns setof espace_point as $$
declare
    mots text[];
    i integer;
    query tsquery;
    l espace_point%rowtype;
    s_orig text;
    s_dest text;
    nom_departement espace_departement.nom%type;
    nom_commune espace_commune.nom%type;
begin
    s_orig := 'àâçèéêîïôùû%ë';
    s_dest := 'aaceeeiiouu e';
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

    for l in select * 
	from espace_point
	    left join espace_commune on espace_point.commune_id_espace=espace_commune.id_espace	   
	where (to_tsvector('french', translate(lower(coalesce(espace_point.nom, '')||' '||coalesce(espace_commune.nom, '')), s_orig, s_dest)) @@ query
	    or translate(lower(espace_point.nom), s_orig, s_dest) like translate(lower(texte), s_orig, s_dest)||'%') and length(trim(espace_point.nom))>0 and espace_point.nom is not null
    loop
	return next l;
    end loop;
    return;
end;
$$ language plpgsql;