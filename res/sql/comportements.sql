/*

PAS UTILISÉ

drop table citations_comportements;
drop table comportements;

create table citations_comportements (
	id_citation integer,
	id_comportement integer,
	tmp_code_comportement char(4)
);

create table comportements (
	id_comportement serial,
	niveau integer,
	sff integer,
	lib varchar(300),
	nicheur boolean,
	tmp_code_comportement char(4)
);
*/
