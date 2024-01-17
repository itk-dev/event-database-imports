# Danish event database (version 2.x)

This is the next iteration of [the event database](https://github.com/itk-event-database/event-database-api) used by the
municipality of Aarhus.

The event database is an API platform for event aggregation from the public vendors throughout the cites. It gets data
mainly from feeds (JSON/XML) or APIs provided by the vendors. It is highly configurable in doing custom feed mappings
and extendable to read data from APIs and map this data to event. It also has a user interface to allow manual entering
of events.

The data input is pulled/pushed from a range of differently formatted sources and normalized into an event format that
can be used across platforms.

For more detailed and technical documentation, see the [docs](docs/README.md) folder in this repository.

## Record Architecture Decisions

This project utilizes record architecture decisions documents which can be located in [docs/adr](docs/adr) in this
repository.

## Installation

The application is built around Symfony and event messages for more information see the technical documentation in the
[docs](docs/README.md) folder in this repository.

```shell
docker compose up -d
docker compose exec phpfpm composer install
docker compose exec phpfpm bin/console doctrine:migrations:migrate
docker compose exec phpfpm bin/console app:index:create
docker compose exec phpfpm bin/console messenger:setup-transports
```

### Consume messages

In development, you need to consume messages by stating the consumer using the command below. Production setup uses the
supervisor container to automatically consume messages and process them. The service is defined in the
[docker-compose.server.override.yml](docker-compose.server.override.yml) composer file.

Manual consume messages with this command.

```shell
docker compose exec phpfpm bin/console messenger:consume async
```

### Load feeds

Import/read feeds and create events based on their data you need to set up cron jobs that with regular intervals execute
the command below. If you need to have different import intervals, you can add the database id of the feed you what to
run with `--id <id>`. If you want to loop over all feeds configured, omit the id parameter.

```shell
docker compose exec phpfpm bin/console app:feed:import
```

### Search index (front end data)

The front end [API](https://github.com/itk-dev/event-database-api) connects to ElasticSearch for fast event look up.
The index is automatically built when data is entered in the UI or feeds are parsed. But if you need to populate the
indexes, you can run this command:

```shell
docker compose exec phpfpm bin/console app:index:populate
```

This command is also helpful if the index gets out-of-sync with the database or if the index changes and needs
re-indexing.

### Fixtures

The project comes with doctrine fixtures to help development on local machines. They can be loaded with the standard
doctrine fixture load command:

```shell
docker compose exec phpfpm bin/console doctrine:fixtures:load
```

### Production

When installing composer and Symfony based application in production, you should not install development packages,
hence use this command:

```shell
docker compose exec phpfpm composer install --no-dev --optimize-autoloader
```

#### Recommend setup

Using all three repositories, you can create the setup depicted below and have communication between the backend
(imports) and the API (frontend) by using the
[shared service's repository](https://github.com/itk-dev/event-database-services.git).

![Network setup production](./docs/images/networks.png)
