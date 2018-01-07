create table questions (
	id_question serial,
	date_creation timestamp default now(),
	date_modif timestamp default null,
	id_utilisateur integer references utilisateur (id_utilisateur),
	titre varchar(300),
	fermee boolean default false,
	primary key (id_question)
);

create table questions_reponses (
	id_reponse serial,
	id_question integer references questions (id_question),
	id_utilisateur integer references utilisateur (id_utilisateur),
	date_creation timestamp default now(),
	texte text,
	primary key (id_reponse)
);

select AddGeometryColumn('questions', 'geom', 4326, 'POINT', 2);