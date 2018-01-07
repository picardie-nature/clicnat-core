create or replace function bob_trig_citations_i() returns trigger as $p$
begin
	update especes set n_citations = n_citations + 1 where id_espece=NEW.id_espece;
	return NEW;
end;
$p$ language plpgsql;

create trigger trig_citation_1 after insert on citations for each row execute procedure bob_trig_citations_i();
