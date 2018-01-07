/* table utilisé pour les tests unitaire */

drop table if exists tests;
create table tests (
	test_id integer,
	tableau integer[],
	chaine varchar(20)
);

insert into tests values (42, '{1,2,3}', 'hello world');

insert into especes (classe, espece, type_fiche, systematique, ordre, commentaire, famille, nom_f, nom_s, nom_a)
values ('M', 'UTEST', 1, '99999', 'TEST_ORDRE', 'TEST_COMMENTAIRE', 'TEST_FAMILLE', 'TEST_NOMF', 'TEST_NOMS', 'TEST_NOMA');

