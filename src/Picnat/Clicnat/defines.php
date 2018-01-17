<?php
namespace Picnat\Clicnat;
$context = 'general';
if (!defined('')) define('DB_RESOURCE_TYPE', 'pgsql link');

if (!defined('DATE_M')) define('DATE_M', 'mois');
if (!defined('DATE_J')) define('DATE_J', 'jour');
if (!defined('DATE_A')) define('DATE_A', 'annee');

if (!defined('BOBS_INSERT_QUERY_SUFFIX')) define('BOBS_INSERT_QUERY_SUFFIX', '_insert_b');
if (!defined('BOBS_NEXTVAL_QUERY_SUFFIX')) define('BOBS_NEXTVAL_QUERY_SUFFIX', '_nextval_b');
if (!defined('BOBS_SELECT_QUERY_SUFFIX')) define('BOBS_SELECT_QUERY_SUFFIX', '_select_b');
if (!defined('BOBS_LIBDIR')) define('BOBS_LIBDIR', '/var/lib/bobs/');
if (!defined('BOBS_CLI_NO_EX')) define('BOBS_CLI_NO_EX', false);



if (!defined('CLICNAT_MAIL_EXPEDITEUR')) {
	define('CLICNAT_MAIL_EXPEDITEUR', 'ne.pas.repondre@clicnat.fr');
}

if (!defined('BOBS_LOG_FILE')) define('BOBS_LOG_FILE',  '/tmp/bobs.log');
if (!defined('BOBS_ERR_NOTFOUND')) define('BOBS_ERR_NOTFOUND', 404);
if (!defined('NICHEUR_CERTAIN')) define('NICHEUR_CERTAIN', 3);
if (!defined('NICHEUR_PROBABLE')) define('NICHEUR_PROBABLE', 2);
if (!defined('NICHEUR_POSSIBLE')) define('NICHEUR_POSSIBLE', 1);
if (!defined('PAS_NICHEUR')) define('PAS_NICHEUR', 0);
if (!defined('BOBS_TAGS_NIDIF')) define('BOBS_TAGS_NIDIF', '120,121,122,123');
if (!defined('TAG_PROTOCOLE')) define('TAG_PROTOCOLE', 'ETUD');
if (!defined('TAG_STRUCTURE')) define('TAG_STRUCTURE', 'STRU');

if (!defined('WFS_SERVICE_PROVIDER')) define('WFS_SERVICE_PROVIDER', 'Clicnat - Picardie Nature');
if (!defined('WFS_CONTACT_NAME')) define('WFS_CONTACT_NAME', 'Responsable SIG');
if (!defined('WFS_CONTACT_MAIL')) define('WFS_CONTACT_MAIL', 'md@picardie-nature.org');
if (!defined('WFS_URL_BASE')) define('WFS_URL_BASE', 'http://hyla.picardie-nature.org/~nicolas/public2/?page=wfs');

if (!defined('BOBS_PREINSCRIPTION_PATH')) define('BOBS_PREINSCRIPTION_PATH', '/var/cache/bobs/preinscriptions/');
if (!defined('BOBS_JUNIOR_PATH')) define('BOBS_JUNIOR_PATH', '/var/cache/bobs/juniors/');
if (!defined('BOBS_JUNIOR_TAG')) 	define('BOBS_JUNIOR_TAG', 592);


if (!defined('SINP_PLATEFORME_URL'))
	define('SINP_PLATEFORME_URL', 'http://obs.picardie-nature.org/occtax/');

if (!defined('SINP_IDCNP'))
	define('SINP_IDCNP', '1296');

if (!defined('SINP_GESTIONNAIRE'))
	define('SINP_GESTIONNAIRE', 'Picardie Nature');

if (!defined('T_ETAT_PARCOURS')) define('T_ETAT_PARCOURS', 0);
if (!defined('T_ETAT_VAR')) define('T_ETAT_VAR', 1);
if (!defined('BOBS_TBL_TAG_CITATION')) define('BOBS_TBL_TAG_CITATION', 'citations_tags');
if (!defined('BOBS_TBL_TAG_ESPACE')) define('BOBS_TBL_TAG_ESPACE', 'espace_tags');
if (!defined('BOBS_TBL_TAG_OBSERVATION')) define('BOBS_TBL_TAG_OBSERVATION','observations_tags');
if (!defined('BOBS_TBL_TAG_ESPECE')) define('BOBS_TBL_TAG_ESPECE','especes_tags');
if (!defined('GML_NS_URL')) define('GML_NS_URL', 'http://www.opengis.net/gml/3.2');
if (!defined('SINP_NS_URL')) define('SINP_NS_URL', 'http://inpn.mnhn.fr/sinp/');
if (!defined('BOBS_BIN_EXTRACT_SELECTION')) define('BOBS_BIN_EXTRACT_SELECTION', '/usr/local/bin/extract_selection');
if (!defined('BOBS_EXTRACTSHP_TMP')) define('BOBS_EXTRACTSHP_TMP', '/tmp/bobs-sel-%d');
if (!defined('BOBS_EXTRACT_SHP_1KM')) define('BOBS_EXTRACT_SHP_1KM',	'atlas');
if (!defined('BOBS_EXTRACT_SHP_NORMAL')) define('BOBS_EXTRACT_SHP_NORMAL', 'normal');
if (!defined('BOBS_EXTRACT_SHP_MIX')) define('BOBS_EXTRACT_SHP_MIX', 'mix');
if (!defined('BOBS_EXTRACT_SHP_NCHIRO')) define('BOBS_EXTRACT_SHP_NCHIRO', 'nchiro');
if (!defined('EPSG_WGS84')) define('EPSG_WGS84', 4326);
if (!defined('EPSG_RGF93')) define('EPSG_RGF93', 2154);
if (!defined('BM_LAYER_PT')) define('BM_LAYER_PT', 'bm_layer_pt_int');
if (!defined('DEFAULT_MAPFILE')) define('DEFAULT_MAPFILE', '/carto/l93.map');
if (!defined('MAPSTORE')) define('MAPSTORE', '/mapstore');
if (!defined('MAPSTORE_BACKGROUNDS')) define('MAPSTORE_BACKGROUNDS', MAPSTORE.'/backgrounds');
if (!defined('COORD_REGION_AX')) define('COORD_REGION_AX', 1.51);
if (!defined('COORD_REGION_AY')) define('COORD_REGION_AY', 50.5679);
if (!defined('COORD_REGION_BX')) define('COORD_REGION_BX', 4,24621);
if (!defined('COORD_REGION_BY')) define('COORD_REGION_BY', 48.7815);
if (!defined('FONT_ARIAL')) define('FONT_ARIAL', '/usr/share/fonts/truetype/msttcorefonts/arial.ttf');
if (!defined('ATLAS_SHAPE_PATH')) define('ATLAS_SHAPE_PATH', '/atlas/%d/atlas.shp');

if (!defined('DOCS_BASE_DIR'))
	define('DOCS_BASE_DIR', '/docs');

if (!defined('MONGO_DB_STR')) define('MONGO_DB_STR', 'mongodb://localhost:27017');
if (!defined('MONGO_BASE')) define('MONGO_BASE', 'clicnat');

if (!defined('CLICNAT_HIRONDELLE_TAG'))
	define('CLICNAT_HIRONDELLE_TAG', 629);

if (!defined('CLICNAT_HIRONDELLE_ID_TAG_OCCUPANT'))
	define('CLICNAT_HIRONDELLE_ID_TAG_OCCUPANT', 630);

if (!defined('CLICNAT_HIRONDELLE_ID_TAG_PUBLIQUE'))
	define('CLICNAT_HIRONDELLE_ID_TAG_PUBLIQUE', 631);


if (!defined('CLICNAT_HIRONDELLE_ID_ESPECE_RUSTIQUE'))
	define('CLICNAT_HIRONDELLE_ID_ESPECE_RUSTIQUE', 725);
if (!defined('CLICNAT_HIRONDELLE_ID_ESPECE_FENETRE'))
	define('CLICNAT_HIRONDELLE_ID_ESPECE_FENETRE', 387);
if (!defined('CLICNAT_HIRONDELLE_ID_ESPECE_RIVIERE'))
	define('CLICNAT_HIRONDELLE_ID_ESPECE_RIVIERE', 815);

define('SRID_BY_DEFAULT', 4326);

if (!defined('BIN_HISTOGRAMME_MOIS'))
	define('BIN_HISTOGRAMME_MOIS', '/usr/local/bin/histogramme_mois');

if (!defined('IMPORT_MAX_COL')) define('IMPORT_MAX_COL', 37);
if (!defined('IMPORT_UPDIR')) define('IMPORT_UPDIR', '/tmp');
if (!defined('IMPORT_SESSION_N')) define('IMPORT_SESSION_N', 'imp_h');
if (!defined('IMPORT_COL_OBS_OBSERV')) define('IMPORT_COL_OBS_OBSERV', 1);
if (!defined('IMPORT_COL_OBS_LIEU')) define('IMPORT_COL_OBS_LIEU', 2);
if (!defined('IMPORT_COL_CIT_ORDRE')) define('IMPORT_COL_CIT_ORDRE', 3);
if (!defined('IMPORT_COL_CIT_ESPECE')) define('IMPORT_COL_CIT_ESPECE', 4);
if (!defined('IMPORT_COL_CIT_EFFECTIF')) define('IMPORT_COL_CIT_EFFECTIF', 5);
if (!defined('IMPORT_COL_OBS_DATE')) define('IMPORT_COL_OBS_DATE', 7);
if (!defined('IMPORT_COL_IGNORER')) define('IMPORT_COL_IGNORER', 8);
if (!defined('IMPORT_COL_TEMPERATURE')) define('IMPORT_COL_TEMPERATURE', 9);
if (!defined('IMPORT_COL_CODE_FNAT')) define('IMPORT_COL_CODE_FNAT', 10);
if (!defined('IMPORT_COL_GENRE')) define('IMPORT_COL_GENRE', 11);
if (!defined('IMPORT_COL_AGE')) define('IMPORT_COL_AGE', 12);
if (!defined('IMPORT_COL_COMMENTAIRE')) define('IMPORT_COL_COMMENTAIRE', 13);
if (!defined('IMPORT_COL_HEURE')) define('IMPORT_COL_HEURE', 14);
if (!defined('IMPORT_COL_DUREE')) define('IMPORT_COL_DUREE', 15);
if (!defined('IMPORT_COL_LATITUDE_DMS')) define('IMPORT_COL_LATITUDE_DMS', 16);
if (!defined('IMPORT_COL_LONGITUDE_DMS')) define('IMPORT_COL_LONGITUDE_DMS', 17);
if (!defined('IMPORT_COL_LATITUDE_D')) define('IMPORT_COL_LATITUDE_D', 18);
if (!defined('IMPORT_COL_LONGITUDE_D')) define('IMPORT_COL_LONGITUDE_D', 19);
if (!defined('IMPORT_COL_CD_NOM')) define('IMPORT_COL_CD_NOM', 20);
if (!defined('IMPORT_COL_INDICE_FIA')) define('IMPORT_COL_INDICE_FIA', 21);
if (!defined('IMPORT_COL_PERIODE_DATE_A')) define('IMPORT_COL_PERIODE_DATE_A', 22);
if (!defined('IMPORT_COL_PERIODE_DATE_B')) define('IMPORT_COL_PERIODE_DATE_B', 23);
if (!defined('IMPORT_COL_WKT')) define('IMPORT_COL_WKT', 24);

if (!defined('BIN_LISTE_ESPACES_SHP_ENGLOBANT'))
	define('BIN_LISTE_ESPACES_SHP_ENGLOBANT','/usr/local/bin/shp_listes_espaces_englobant');

// a garder tant qu'on utilise le double système de date
if (!defined('MAINT_ANCIENNE_DATE')) define('MAINT_ANCIENNE_DATE',1);

if (!defined('TAG_ATTENTE_VALIDATION'))
	define('TAG_ATTENTE_VALIDATION', 'ATTV');

if (!defined('TAG_HOMOLOGATION_NECESSAIRE'))
	define('TAG_HOMOLOGATION_NECESSAIRE', 'LOGN');

if (!defined('TAG_HOMLOGEE'))
	define('TAG_HOMOLOGEE', 'LOGV');

if (!defined('TAG_INVALIDE'))
	define('TAG_INVALIDE', 'INV!');

if (!defined('TAG_NOUVEL_OBSERVATEUR'))
	define('TAG_NOUVEL_OBSERVATEUR', 'NEWO');
