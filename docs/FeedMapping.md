# How to do feed mapping

When importing events from feeds one needs to map the feeds structure into that of the event database's typed objects.

The API accepts the following JSON object as configuration for a given feed. There are more information about the
mapping and defaults objects in this document.

```json
{
  "type": "json",
  "url": "",
  "base": "https://aakb.dk/",
  "timezone": "Europe/Copenhagen",
  "rootPointer": "/-",
  "dateFormat": "Y-m-d\TH:i:sP",
  "mapping": {},
  "defaults": {}
}
```

## Root element (JSON pointer path)

Event feeds may have different root elements that wraps the feed items. To select the root element we use a
[JSON pointer](https://datatracker.ietf.org/doc/html/rfc6901) to the root element.

### Examples

Pointer: `\-`

```json
[
  {},
  {}
]
```

Pointer: `/events/-`

```json
{
  "events": [ 
    {},
    {}
  ]
}
```

## Date formats

The date format defined in the configuration object have to match the format in the feed it is defined by using PHP's
[data format](https://www.php.net/manual/en/datetime.format.php) notation.

## Array mapping

To map feeds array fields into array in the system you can use the wildcard array index selector. The example below maps
occurrences dates into the system.

```json
{
  "mapping": {
    "occurrences.*.startDate": "occurrences.*.start", 
    "occurrences.*.endDate": "occurrences.*.end"
  }
}
```

## String to array

To support mapping strings into arrays the following syntax can be used.

This example will map simple string into tags array.

```json
{
  "mapping": {
    "tags": "tags.[]"
  }
}
```

This example will map a comma separated string into the tags array. Spilt by the separator given in `[]`.

```json
{
  "mapping": {
    "tags": "tags.[,]"
  }
}
```

## Default values

**@todo**: write about this when the code supports it :-/

## Destination fields

| Field       | Data type       | Required (do not reflect recommended) | Comment                                                                            |
|-------------|-----------------|---------------------------------------|------------------------------------------------------------------------------------|
| feedId      | int             | no                                    | Should not be set will be overridden doing import                                  |
| id          | integer\|string | yes                                   | A unique identifier for the event. Used to detect if an event is new or an update. |
| title       | string          | no                                    | Event title                                                                        |
| description | string          | no                                    | Long event description, which may contain html                                     |
| excerpt     | string          | no                                    | Short description.                                                                 |
| image       | string          | no                                    | Url to image location for the event                                                |
| ticketUrl   | string          | no                                    | Url to buy ticket                                                                  |
| url         | string          | no                                    | Url of the event                                                                   |
| start       | date            | no                                    | Date of event start (optional as this may be located in occurrences)               |
| end         | date            | no                                    | Date of event end (optional as this may be located in occurrences)                 |
| price       | string          | no                                    | Price as string as this may be a price range                                       |
| tags        | array\|string   | no                                    | Tags from the event. See string to array mapping for more information              |

### Location mapping

| Field                     | Data type | Required (do not reflect recommended) | Comment |
|---------------------------|-----------|---------------------------------------|---------|
| location.city             | string    | no                                    |         |
| location.country          | string    | no                                    |         |
| location.postalCode       | string    | no                                    |         |
| location.street           | string    | no                                    |         |
| location.suite            | string    | no                                    |         |
| location.name             | string    | no                                    |         |
| location.mail             | string    | no                                    |         |
| location.telephone        | string    | no                                    |         |
| location.url              | string    | no                                    |         |
| location.image            | string    | no                                    |         |
| location.logo             | string    | no                                    |         |
| location.region           | string    | no                                    |         |
| location.coordinates.lat  | string    | no                                    |         |
| location.coordinates.long | string    | no                                    |         |

### Occurrences mapping

| Field               | Data type | Required (do not reflect recommended) | Comment                       |
|---------------------|-----------|---------------------------------------|-------------------------------|
| occurrences.*.start | date      | no                                    | Start date of the occurrences |
| occurrences.*.end   | date      | no                                    | End date of the occurrences   |
