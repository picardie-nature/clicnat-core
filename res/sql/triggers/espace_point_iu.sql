create or replace function bob_trig_espace_point_iu() returns trigger as $p$
declare
	id integer;
begin
	if TG_OP = 'INSERT' then
		RAISE NOTICE 'Trigger Insert %', NEW.id_espace;
		-- commune 
		id := bob_commune_espace_point(NEW.id_espace);
		if not id is null then
			update espace_point set commune_id_espace = id where id_espace = NEW.id_espace;
		end if;
		id := bob_departement_espace_point(NEW.id_espace);
		if not id is null then
			update espace_point set departement_id_espace = id where id_espace = NEW.id_espace;
		end if;
		id := bob_littoral_espace_point(NEW.id_espace);
		if not id is null then
			update espace_point set littoral_id_espace = id where id_espace = NEW.id_espace;
		end if;
		id := bob_l93_10x10_espace_point(NEW.id_espace);
		if not id is null then
	                update espace_point set l93_10x10_id_espace = id where id_espace = NEW.id_espace;
		end if;
	else
		if not st_equals(NEW.the_geom, OLD.the_geom) then
			NEW.l93_10x10_id_espace = bob_l93_10x10_espace_point(NEW.id_espace);
			NEW.commune_id_espace := bob_commune_espace_point(NEW.id_espace);
			if OLD.commune_id_espace != NEW.commune_id_espace then
				NEW.departement_id_espace := bob_departement_espace_point(NEW.id_espace);
			end if;
		end if;
	end if;
	perform bob_index_espace('espace_point', NEW.id_espace, NEW.the_geom);
        return NEW;
end;
$p$ language plpgsql;

drop trigger if exists trig_espace_point_1 on espace_point;
create trigger trig_espace_point_1 after insert or update 
	on espace_point for each row execute procedure bob_trig_espace_point_iu();
