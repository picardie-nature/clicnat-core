create or replace function bob_trig_espace_chiro_bi() returns trigger as $p$
declare
    nref text;
begin
    if coalesce(length(NEW.nom),0) = 0 then
	NEW.date_modif := now();
	NEW.nom := 'P_'||nextval('espace_chiro_seq');
	NEW.reference := NEW.nom;
    end if;
    return NEW;
end;
$p$ language plpgsql;

create or replace function bob_trig_espace_chiro_bu() returns trigger as $p$
declare
    nref text;
begin
    NEW.date_modif := now();
    return NEW;
end;
$p$ language plpgsql;

create or replace function bob_trig_espace_chiro_iu() returns trigger as $p$
begin
	-- A RETIRER UNE FOIS QUE ESPACE_INTERSECT EST UTILISE PARTOUT
	if TG_OP = 'INSERT' then
		RAISE NOTICE 'Trigger Insert %', NEW.id_espace;
		update espace_chiro
			set commune_id_espace = bob_commune_espace_chiro(NEW.id_espace)
			where id_espace = NEW.id_espace;
		update espace_chiro
			set departement_id_espace = bob_departement_espace_chiro(NEW.id_espace)
			where id_espace = NEW.id_espace;
	else
		if not st_equals(NEW.the_geom, OLD.the_geom) then
			NEW.commune_id_espace := bob_commune_espace_chiro(NEW.id_espace);
			if OLD.commune_id_espace != NEW.commune_id_espace then
				NEW.departement_id_espace := bob_departement_espace_chiro(NEW.id_espace);
			end if;
		end if;
	end if;
	-- FIN DE LA SECTION A RETIRER
	perform bob_index_espace('espace_chiro', NEW.id_espace, NEW.the_geom);
        return NEW;
end;
$p$ language plpgsql;

drop trigger if exists trig_espace_chiro_1 on espace_chiro;
drop trigger if exists trig_espace_chiro_2 on espace_chiro;
drop trigger if exists trig_espace_chiro_3 on espace_chiro;

create trigger trig_espace_chiro_1 after insert or update
	on espace_chiro for each row execute procedure bob_trig_espace_chiro_iu();

create trigger trig_espace_chiro_2 before insert
	on espace_chiro for each row execute procedure bob_trig_espace_chiro_bi();

create trigger trig_espace_chiro_3 before update
	on espace_chiro for each row execute procedure bob_trig_espace_chiro_bu();
