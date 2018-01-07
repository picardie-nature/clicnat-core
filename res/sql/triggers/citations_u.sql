create or replace function bob_trig_citations_u() returns trigger as $p$
begin
	update especes set n_citations = n_citations - 1 where id_espece=OLD.id_espece;
	update especes set n_citations = n_citations + 1 where id_espece=NEW.id_espece;

	if NEW.id_espece != OLD.id_espece then
		if exists(select t.id_tag from tags t,citations_tags ct where t.ref='ATTV' and ct.id_tag=t.id_tag and ct.id_citation=NEW.id_citation) then
			NEW.validation_avis_positif := '{}';
			NEW.validation_avis_negatif := '{}';
			NEW.validation_sans_avis := '{}';
		end if;
	end if;

	return NEW;
end;
$p$ language plpgsql;

create trigger trig_citation_up before update on citations for each row execute procedure bob_trig_citations_u();
