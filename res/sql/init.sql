create language plpgsql;
create extension hstore;

\i clc.sql
\i utilisateur.sql
\i api.sql
\i taches.sql
\i espace.sql
\i chr.sql
\i tags.sql
\i especes.sql
\i observations.sql
\i referentiel_tiers.sql
\i selections.sql
\i calendrier.sql
\i indexes.sql
\i import.sql
\i textes.sql
\i enquetes.sql
\i repartition.sql
\i structures.sql
\i taches.sql
\i travaux.sql
\i validation.sql
\i reseau.sql

\i vues/v_especes_synonymes_inpn.sql

\i fonctions/bob_diffusion_restreinte.sql
\i fonctions/bobs_commune_espace_chiro.sql
\i fonctions/bob_l93_10x10_espace_point.sql
\i fonctions/bob_selection_diffusion_restreinte.sql
\i fonctions/bob_recherche_observateur_nom.sql
\i fonctions/bob_point_x.sql
\i fonctions/bob_citation_ok.sql
\i fonctions/bob_recherche_espace_point.sql
\i fonctions/bob_departement_espace_point.sql
\i fonctions/bob_departement_espace_chiro.sql
\i fonctions/bob_point_obs.sql
\i fonctions/bob_littoral_espace_point.sql
\i fonctions/bob_commune_espace_point.sql
\i fonctions/bob_point_y.sql
\i fonctions/bob_citations_observateurs_str.sql
\i fonctions/bob_recherche_espece_nom_f.sql
\i fonctions/bob_toponyme_proche.sql
\i fonctions/clicnat_atlas.sql
\i fonctions/bob_espace_index.sql
\i fonctions/bob_citation_structure_ok.sql
\i fonctions/bob_recherche_tag_lib.sql

\i triggers/espace_point_iu.sql
\i triggers/espace_chiro_iu.sql
