create table visite_espace_hirondelle (
	id_visite_nid	serial,
	date_modif	timestamp,
	date_creation	timestamp default now(),
	date_visite_nid	date not null,
	id_espace	integer references espace_point(id_espace),

	n_nid_occupe_r	integer not null default 0,
	n_nid_vide_r	integer not null default 0,
	n_nid_detruit_r	integer not null default 0,

	n_nid_occupe_ri	integer not null default 0,
	n_nid_vide_ri	integer not null default 0,
	n_nid_detruit_ri	integer not null default 0,

	n_nid_occupe_f	integer not null default 0,
	n_nid_vide_f	integer not null default 0,
	n_nid_detruit_f	integer not null default 0,

	id_utilisateur integer references utilisateur(id_utilisateur),
	id_observation integer references observations(id_observation),
	
	primary key (id_visite_nid),
	unique (date_visite_nid,id_espace)
);
