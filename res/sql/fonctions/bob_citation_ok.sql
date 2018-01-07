create or replace function bob_citation_ok(p_id_utilisateur utilisateur.id_utilisateur%type, p_id_citation citations.id_citation%type) returns boolean as $$
begin
	insert into utilisateur_citations_ok (id_utilisateur, id_citation) values (p_id_utilisateur, p_id_citation);
	return true;
exception 
	when unique_violation or integrity_constraint_violation then return false;
end;
$$ language plpgsql;
