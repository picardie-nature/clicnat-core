create database clicnat;
create role clicnat_admin;
create role clicnat_www login;
create role clicnat_utilisateur;
alter database clicnat owner to clicnat_admin;
\connect clicnat
grant select,insert,update,delete on all tables in schema public to clicnat_www;
grant select on all tables in schema public to clicnat_utilisateur;

\i /usr/share/postgresql/9.4/contrib/postgis-2.1/postgis.sql
\i /usr/share/postgresql/9.4/contrib/postgis-2.1/spatial_ref_sys.sql
\i /usr/share/postgresql/9.4/contrib/postgis-2.1/legacy.sql 

create extension "uuid-ossp";
create extension "unaccent";
create extension "hstore";
create extension "fuzzystrmatch";
set role clicnat_admin
\i init.sql
