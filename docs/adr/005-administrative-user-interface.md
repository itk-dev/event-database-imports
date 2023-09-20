# Administrative user interface

Date: 20-09-2023

## Status

Proposed

## Context

We need to create an administrative interface that is accessible through a browser and that we can build quickly. The
first plan about the administrative interface was to utilize the API and make it independent of the database.

## Decision

It has been decided to switch the administrative interface to [Easy admin](https://github.com/EasyCorp/EasyAdminBundle)
bundle, which utilize the database and the entities to generate an administrative user interface.

When using Easy admin one should stay within the limitation that this bundle may enforce on the user interface. We
should not make extensive changes to interface.

## Consequences

This will limit the horizontal scalability, but we will be able to quickly make an administrative user interface. This
limitation comes in the form of the hard dependency between Easy admin and the database entities.

If the project don't stay within the Easy admin limitation further upgrades can be made more expensive. With limitation
here is thought of as custom extension etc. By keeping within the limitations it should be possible to later on switch
to another user interface if the project grows and need to scale.
