# Enforce UTC in database and ORM layer

Date: 10-06-2024

## Status

Accepted

## Context

Doctrine ORM by design does NOT handle timezones when persisting DateTime objects. E.g. if you do for a
doctrine entity

```php
$event->start = new \DateTimeImmutable('2024-06-04 17:17:17.000000', new \DateTimeZone('UTC'));
$event->end = new \DateTimeImmutable('2024-06-04 17:17:17.000000', new \DateTimeZone('Europe/Copenhagen'));

$this->entityManager->persist($event);
$this->entityManager->flush();
```

Then in the database these will have been persisted as

| id  | start               | end                 |
|-----|---------------------|---------------------|
| xyz | 2024-06-04 17:17:17 | 2024-06-04 17:17:17 |

Thereby discarding the timezone and timezone offset.

Because we accept any valid timestamp regardless of timezone this doctrine behavior will lead to inconsistencies in the
timestamps persisted in the database and exposed through the API.

## Decision

All datetime fields must be converted to UTC before they are persisted to database. This is done by overwriting the
standard doctrine `DateTime` and `DateTimeImmutable` types by custom types as specified by Doctrine
[Handling different Timezones with the DateTime Type](https://www.doctrine-project.org/projects/doctrine-orm/en/3.2/cookbook/working-with-datetime.html#handling-different-timezones-with-the-datetime-type)

```yaml
# config/packages/doctrine.yaml
doctrine:
    dbal:
        types:
            datetime: App\Doctrine\Extensions\DBAL\Types\UTCDateTimeType
            datetime_immutable: App\Doctrine\Extensions\DBAL\Types\UTCDateTimeImmutableType
```

## Consequences

All "view layers" need to be configured to factor in the model timezone. For the API this means that all datetime fields
must be serialized with timezone (already the default). For EasyAdmin all datetime fields must be configured with
correct "model" and "view" timezones. This is done with [field configurators](https://symfony.com/bundles/EasyAdminBundle/current/fields.html#field-configurators)
and filter configurators. See `App\EasyAdmin\DateTimeFieldConfigurator` and `App\EasyAdmin\DateTimeFilterConfigurator`
