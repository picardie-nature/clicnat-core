create schema stats_validation;

create table stats_validation.effectifs (
	id_espece integer references especes(id_espece) primary key,
	moyenne float
);

create table stats_validation.periodes_especes (
	id_espece integer references especes(id_espece),
	decade integer,
	primary key (id_espece,decade)
);
