create or replace function bob_trig_espace_line_iu() returns trigger as $$
begin
	perform bob_index_espace('espace_line', NEW.id_espace, NEW.the_geom);
	return NEW;
end;
$$ language plpgsql;


drop trigger if exists trig_espace_line_1 on espace_line;

create trigger trig_espace_line_1 after insert or update on espace_line for each row execute procedure bob_trig_espace_line_iu();
