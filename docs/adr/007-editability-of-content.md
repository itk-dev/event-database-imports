# Editability of content

Date: 27-11-2023

## Status

Accepted

## Context

The forms UI (administrative user interface) allows users to add new content and change existing data.

## Decision

All data imported through feeds are marked as read-only as changes made in the administrative UI are overwritten by
changes in the feed.

All other data is editable and will trigger a reindex of the data changes and all related data.

## Consequences

Imported data from feeds are not editable, and some changes to data not from feeds will trigger a reindex of data,
which may trigger a lager job to be processed based on which data is changed. E.g. if an address is changed, that will
trigger an update of all content in indexes that contains that address. Which may be a large number of events. 
