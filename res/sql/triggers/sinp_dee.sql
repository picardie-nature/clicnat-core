create or replace function clicnat_trig_update_sinp_dee() returns trigger as $p$
declare
begin
	-- on ne peut pas changer la date de création
	NEW.date_creation := OLD.date_creation;
	insert into sinp_dee_archive (id_citation,document,date_creation,date_archive)
		values (
			NEW.id_citation,
			OLD.document,
			OLD.date_modification,
			NEW.date_modification
		);
	return NEW;
end;
$p$ language plpgsql;

drop trigger if exists sinp_dee_update on sinp_dee;
create trigger sinp_dee_update before update 
	on sinp_dee for each row execute procedure clicnat_trig_update_sinp_dee();
