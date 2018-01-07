CFLAGS=-I/usr/include/postgresql -I$(shell pg_config --includedir-server) -Wall $(shell pg_config --cflags)
PKGLIBDIR=$(shell pg_config --pkglibdir)

clicnat_mad.so: clicnat_mad.o
	ld -shared -o $@ $?

clean:
	@rm -f *.so *.o

install: clicnat_mad.so
	@install -v clicnat_mad.so $(PKGLIBDIR)
