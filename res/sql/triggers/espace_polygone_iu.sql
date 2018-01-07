create or replace function bob_trig_espace_polygon_i() returns trigger as $$
begin
	NEW.superficie := st_area(transform(NEW.the_geom,2154))::bigint;
	return NEW;
end;
$$ language plpgsql;


create or replace function bob_trig_espace_polygon_u() returns trigger as $$
begin
	if not OLD.the_geom ~= NEW.the_geom then
		NEW.superficie := st_area(transform(NEW.the_geom,2154))::bigint;
	end if;
	return NEW;
end;
$$ language plpgsql;

create or replace function bob_trig_espace_polygon_index() returns trigger as $$
begin
	if TG_OP = 'INSERT' then
		perform bob_index_espace('espace_polygon', NEW.id_espace, NEW.the_geom);
	end if;
	if TG_OP = 'UPDATE' then
		if not OLD.the_geom ~= NEW.the_geom then
			perform bob_index_espace('espace_polygon', NEW.id_espace, NEW.the_geom);
		end if;
	end if;
	RETURN NEW;
end;
$$ language plpgsql;

drop trigger if exists trig_espace_polygon_1 on espace_polygon;
drop trigger if exists trig_espace_polygon_2 on espace_polygon;
drop trigger if exists trig_espace_polygon_3 on espace_polygon;

create trigger trig_espace_polygon_1 before insert on espace_polygon for each row execute procedure bob_trig_espace_polygon_i();
create trigger trig_espace_polygon_2 before update on espace_polygon for each row execute procedure bob_trig_espace_polygon_u();
create trigger trig_espace_polygon_3 after insert or update on espace_polygon for each row execute procedure bob_trig_espace_polygon_index();
