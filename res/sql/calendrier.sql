create table calendriers_dates (
    id_date serial,
    id_espace integer,
    espace_table varchar(100),
    date_sortie date,
    commentaire text,
    tag varchar(10)
);
alter table calendriers_dates add constraint calendriers_dates_pk primary key (id_date);
create table calendriers_participants (
    id_date integer references calendriers_dates(id_date),
    id_utilisateur integer references utilisateur(id_utilisateur)
);


alter table calendriers_participants add constraint calendriers_participants_pk primary key (id_date, id_utilisateur);
