# Usage of message queues

Date: 18-08-2023

## Status

Accepted

## Context

We have decided to use CQRS and separate the input and output data modules. So we wish to make data processing of input
data independent and modular to be able to parallel processing data and thereby speed up the process, but without
impacting the output data.

## Decision

To archive this we use [Symfony messenger](https://symfony.com/doc/current/messenger.html) and split the processing into
steps that can be processed individually and in parallel. This will also enable us to maintain and change the single
steps of processing without impacting the other steps.

For more implementation details see [https://github.com/itk-dev/event-database-imports/tree/develop/docs](https://github.com/itk-dev/event-database-imports/tree/develop/docs)

## Consequences

This will make data processing asynchronous which requires developer to think different and also makes requirements to
the user interfaces to give feedback on the queue system and where in the process a given event is.
