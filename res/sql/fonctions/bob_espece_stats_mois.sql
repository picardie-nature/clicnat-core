create or replace function bob_espece_stats_mois(p_id_espece integer) returns boolean as $$
begin
	delete from especes_stats_mois where id_espece=p_id_espece;
	insert into especes_stats_mois (id_espece,mois,n) 
		select id_espece,extract(month from date_observation),count(id_citation) 
		from observations,citations 
		where citations.id_observation=observations.id_observation 
		and indice_qualite >= 3
		and id_citation not in (
			select distinct c.id_citation
			from citations_tags ct,citations c
			where c.id_citation=ct.id_citation
			and ct.id_tag in (591,592)
			and c.id_espece=p_id_espece
		)
		and id_espece=p_id_espece 
		group by extract(month from date_observation),id_espece;
	return true;
end;
$$ language plpgsql;
