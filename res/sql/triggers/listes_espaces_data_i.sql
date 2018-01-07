create or replace function bob_trig_listes_espaces_data_iu() returns trigger as $$
declare
	tbl varchar(100);
begin
	tbl := bob_trouve_table_espace(NEW.id_espace);
	NEW.espace_table := tbl;
	return NEW;
end;
$$ language plpgsql;
create trigger trig_listes_espaces_data_1 before insert or update on listes_espaces_data for each row execute procedure bob_trig_listes_espaces_data_iu();

