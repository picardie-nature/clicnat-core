create table sinp_dee (
	id_citation integer references citations(id_citation),
	document xml,
	date_creation timestamp,
	date_modification timestamp,
	primary key (id_citation)
);

create table sinp_dee_archive (
	id_archive serial,
	id_citation integer references citations(id_citation),
	document xml,
	date_creation timestamp,
	date_archive timestamp,
	primary key (id_archive)
);
