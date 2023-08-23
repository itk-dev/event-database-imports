# Separation-of-data

Date: 16-08-2023

## Status

Accepted

## Context

The basic data for the event database comes for import events for other sources mostly feeds (json/xml) which has very
varying quality of data and very different fields and information form feed to feed.

One of the main points of the event database is to normalize events and provide a fast API to display events on third
party web-site outside the control of this project.

## Decision

The input data is very different from the output data in this setup and the frontend should not be impacted by the
import/parsing of new data. The logic step will therefore be to separate the input and output model, which can be
archived be using the "Command Query Responsibility Segregation" patter, here after called [CQRS](https://martinfowler.com/bliki/CQRS.html).

For more implementation details see [https://github.com/itk-dev/event-database-imports/tree/develop/docs](https://github.com/itk-dev/event-database-imports/tree/develop/docs)

## Consequences

Knowledge about CQRS pattern is required to understand the code base and there will be an overhead in initial
development cost. But this should lead to better overall performance and easier maintainability over time due to
independence of the components and the ability to individually maintain components as technology change.
