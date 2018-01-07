create table referentiel_especes_tiers (
	tiers varchar(30) not null,
	id_espece integer references especes(id_espece),
	id_tiers integer,
	primary key (tiers,id_espece,id_tiers)
);