create table taches (
	id_tache serial,
	date_creation timestamp default now(),
	date_exec_prevue timestamp,
	date_exec timestamp,
	date_fin timestamp,
	date_maj timestamp,
	id_utilisateur integer references utilisateur(id_utilisateur),
	code_retour integer default 0,
	message_retour text,
	nom varchar(200),
	classe varchar(200),
	args text
);
