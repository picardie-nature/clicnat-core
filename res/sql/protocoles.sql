create table protocoles (
	id_protocole varchar(30) primary key,
	lib varchar(200),
	description text,
	ouvert boolean default false
);
