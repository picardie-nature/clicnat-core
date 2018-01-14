# clicnat-core

[![Build Status](https://travis-ci.org/picardie-nature/clicnat-core.svg?branch=master)](https://travis-ci.org/picardie-nature/clicnat-core) [![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/picardie-nature/clicnat-core/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/picardie-nature/clicnat-core/?branch=master)

Classes de base de Clicnat, base naturaliste créée par Picardie Nature

## Comment démarrer

Pour déployer rapidement, un environnement de test, vous pouvez utiliser un
fichier docker-compose comme suit :

docker-compose.yml

```
version: '2'

services:
  postgres:
    image: 'mdillon/postgis:9.4'
    volumes:
      - ./volumes/postgres:/var/lib/postgresql/data
    environment:
      POSTGRES_PASSWORD: 'plop'
      POSTGRES_USER: 'baseobs'
      POSTGRES_DB: 'baseobsdev'
    ports:
      - 0.0.0.0:65433:5432
```

ensuite vous pouvez créer une variable d'environnement comme ceci :

```
export POSTGRES_DB_TEST=postgres://baseobs:plop@localhost:65433/baseobsdev
```

pour rendre ça permanent

```
echo "POSTGRES_DB_TEST=postgres://baseobs:plop@localhost:65433/baseobsdev" >> ~/.bash_aliases
```

une fois la variable créée vous pouvez lancer les tests (drop de la base
existante, création d'une vierge, et lancement de phpunit) en lançant le script
`clear_and_run_tests.sh`
