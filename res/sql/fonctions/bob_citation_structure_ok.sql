create or replace function bob_citation_structure_ok(p_id_structure structures.id_structure%type, p_id_citation citations.id_citation%type) returns boolean as $$
begin
	insert into structures_mad (id_structure, id_citation) values (p_id_structure, p_id_citation);
	return true;
exception 
	when unique_violation or integrity_constraint_violation then return true;
end;
$$ language plpgsql;
