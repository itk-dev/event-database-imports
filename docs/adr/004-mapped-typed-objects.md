# Mapped typed object

Date: 22-08-2023

## Status

Accepted

## Context

The system has different input data that needs to be normalized for storage and output.

## Decision

When mapping and normalizing input data we use typed objects in the mapping process to ensure strict typing and thereby
getting a type safe system. We use [Valinor](https://valinor.cuyz.io/) to preform this mapping.

## Consequences

The develops need to use the typed object when parsing data round the system, and they may need to look into Valinor's
documentation.
