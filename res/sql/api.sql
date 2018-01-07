create table sessions_api (
	id_session char(128) primary key,
	id_utilisateur integer references utilisateur(id_utilisateur),
	"date" timestamp,
	valid boolean not null default true
);
