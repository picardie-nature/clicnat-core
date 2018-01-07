#include "postgres.h"
#include "fmgr.h"
#include "funcapi.h"

#include <sys/mman.h>
#include <sys/types.h>
#include <sys/stat.h>
#include <fcntl.h>
#include <unistd.h>

#include <stdlib.h> //qsort

#define MAD_PATH "/var/cache/bobs/mads/%d"
#define MAD_PATH_MAX_LENGTH 50

#ifdef PG_MODULE_MAGIC
#ifndef MAGIC_OK
	#define MAGIC_OK 1
	PG_MODULE_MAGIC;
#endif
#endif

Datum clicnat_mad_id_citation(PG_FUNCTION_ARGS);
Datum clicnat_mad_tri(PG_FUNCTION_ARGS);
Datum clicnat_mad_liste(PG_FUNCTION_ARGS);
Datum clicnat_mad_init(PG_FUNCTION_ARGS);
Datum clicnat_mad_id_citation_ok(PG_FUNCTION_ARGS);

PG_FUNCTION_INFO_V1(clicnat_mad_id_citation);
PG_FUNCTION_INFO_V1(clicnat_mad_tri);
PG_FUNCTION_INFO_V1(clicnat_mad_liste);
PG_FUNCTION_INFO_V1(clicnat_mad_init);
PG_FUNCTION_INFO_V1(clicnat_mad_id_citation_ok);

Datum clicnat_mad_init(PG_FUNCTION_ARGS) {
	char path[MAD_PATH_MAX_LENGTH];
	int fd;

	int32 id_utilisateur = PG_GETARG_INT32(0);
	snprintf(path, MAD_PATH_MAX_LENGTH, MAD_PATH, id_utilisateur);
	unlink(path);
	fd = open(path, O_CREAT|O_WRONLY, S_IWUSR|S_IRUSR);
	close(fd);

	PG_RETURN_INT32(1);
}

Datum clicnat_mad_id_citation(PG_FUNCTION_ARGS) {
	int32 id_utilisateur = PG_GETARG_INT32(0);
	int32 id_citation = PG_GETARG_INT32(1);
	char path[MAD_PATH_MAX_LENGTH];
	int fd;

	snprintf(path, MAD_PATH_MAX_LENGTH, MAD_PATH, id_utilisateur);

	fd = open(path, O_APPEND|O_CREAT|O_WRONLY, S_IWUSR|S_IRUSR);

	if (!fd) {
		elog(ERROR, "ne peut ouvrir le fichier utilisateur");
	}
	
	write(fd, &id_citation, sizeof(int32));
	close(fd);

	PG_RETURN_INT32(1);
}

static int my_comp(const void *a, const void *b) {
	int32 *ia,*ib;

	ia = (int32 *)a;
	ib = (int32 *)b;

	if (*ia == *ib) return 0;
	if (*ia > *ib) return 1;

	return -1; // (*ia < *ib)
}

Datum clicnat_mad_id_citation_ok(PG_FUNCTION_ARGS) {
	int32 id_utilisateur = PG_GETARG_INT32(0);
	int32 id_citation = PG_GETARG_INT32(1);
	int32 *ids,*r;

	char path[MAD_PATH_MAX_LENGTH];
	int f;
	struct stat infos;

	snprintf(path, MAD_PATH_MAX_LENGTH, MAD_PATH, id_utilisateur);
	f = open(path, O_RDONLY);
	if (!f) {
		elog(ERROR, "ne peut ouvrir chemin %s", path);
	}
	fstat(f, &infos);
	ids = mmap(NULL, infos.st_size, PROT_READ, MAP_SHARED, f, 0);
	r = bsearch(&id_citation, ids, infos.st_size/sizeof(int32), sizeof(int32), my_comp);
	munmap(ids, infos.st_size);
	close(f);
	PG_RETURN_BOOL(!(r==NULL));
}

Datum clicnat_mad_tri(PG_FUNCTION_ARGS) {
	int32 id_utilisateur = PG_GETARG_INT32(0);
	int32 *ids;

	char path[MAD_PATH_MAX_LENGTH];
	int f;
	struct stat infos;

	elog(WARNING, "id_utilisateur = %d", id_utilisateur);
	snprintf(path, MAD_PATH_MAX_LENGTH, MAD_PATH, id_utilisateur);
	elog(WARNING, "ouvre %s", path);

	f = open(path, O_RDWR);
	if (!f) {
		elog(ERROR, "ne peut ouvrir chemin %s", path);
	}
	fstat(f, &infos);
	elog(WARNING, "taille du fichier : %ju", infos.st_size);
	ids = mmap(NULL, infos.st_size, PROT_READ|PROT_WRITE, MAP_SHARED, f, 0);
	qsort(ids, infos.st_size/sizeof(int32), sizeof(int32), my_comp);
	munmap(ids, infos.st_size);
	close(f);
	PG_RETURN_INT32(1);
}

typedef struct {
	int fd;
	int32 id_utilisateur;
} s_mad_liste;

Datum clicnat_mad_liste(PG_FUNCTION_ARGS) {
	FuncCallContext	*funcctx;
	MemoryContext	oldcontext;
	s_mad_liste *data;
	TupleDesc tupdesc;
	AttInMetadata *attinmeta;

	if (SRF_IS_FIRSTCALL()) {
		char path[MAD_PATH_MAX_LENGTH];
		struct stat infos;
		funcctx = SRF_FIRSTCALL_INIT();
		oldcontext = MemoryContextSwitchTo(funcctx->multi_call_memory_ctx);
		
		funcctx->user_fctx = palloc(sizeof(s_mad_liste));
		data = (s_mad_liste *)funcctx->user_fctx;
		data->id_utilisateur = PG_GETARG_INT32(0);
		snprintf(path, MAD_PATH_MAX_LENGTH, MAD_PATH, data->id_utilisateur);
		data->fd = open(path, O_RDONLY);
		fstat(data->fd, &infos);
		funcctx->max_calls = infos.st_size/sizeof(int32);

		// Build a tuple descriptor for our result type 
		if (get_call_result_type(fcinfo, NULL, &tupdesc) != TYPEFUNC_COMPOSITE)
			ereport(ERROR,
				(errcode(ERRCODE_FEATURE_NOT_SUPPORTED),
				 errmsg("function returning record called in context "
					"that cannot accept type record")));
		
		// generate attribute metadata needed later to produce tuples from raw
		// C strings
		attinmeta = TupleDescGetAttInMetadata(tupdesc);
		funcctx->attinmeta = attinmeta;

		MemoryContextSwitchTo(oldcontext);
	}

	funcctx = SRF_PERCALL_SETUP();

	if (funcctx->call_cntr < funcctx->max_calls) {
		Datum result;
		HeapTuple tuple;
		char *values[1];
		char value[11];
		int32 id_citation;

		data = (s_mad_liste *)funcctx->user_fctx;
		*values = value;


		read(data->fd, &id_citation, sizeof(int32));

		snprintf(values[0], 11, "%d", id_citation);
		tuple = BuildTupleFromCStrings(funcctx->attinmeta, values);
		result = HeapTupleGetDatum(tuple);

		SRF_RETURN_NEXT(funcctx, result);
	} else {
		data = (s_mad_liste *)funcctx->user_fctx;
		close(data->fd);
		SRF_RETURN_DONE(funcctx);
	}
}

