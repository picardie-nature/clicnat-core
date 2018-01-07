create or replace function clicnat_mad_id_citation(integer, integer) returns integer as 'clicnat_mad','clicnat_mad_id_citation' language c;
create or replace function clicnat_mad_tri(integer)  returns integer as 'clicnat_mad','clicnat_mad_tri' language c;
drop type if exists mad_id_citation cascade;
create type mad_id_citation as (id_citation integer);
create or replace function clicnat_mad_liste(integer) returns setof mad_id_citation as 'clicnat_mad','clicnat_mad_liste' language c;
create or replace function clicnat_mad_init(integer) returns integer as 'clicnat_mad','clicnat_mad_init' language c;
create or replace function clicnat_mad_init(integer) returns integer as 'clicnat_mad','clicnat_mad_init' language c;
create or replace function clicnat_mad_id_citation_ok(integer,integer) returns boolean as 'clicnat_mad','clicnat_mad_id_citation_ok' language c;
