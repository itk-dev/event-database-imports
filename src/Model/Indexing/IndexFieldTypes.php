<?php

namespace App\Model\Indexing;

final class IndexFieldTypes
{
    // ISO 8601 date "2004-02-12T15:19:21+00:00"
    public const string DATEFORMAT = \DateTimeInterface::ATOM;

    // One would think one could use the named ISO formats (but, NO) and Elastic says they use the format in the link.
    // Here it also needs to be "z" (“time-zone name”) and not "Z" (“zone-offset”) to match the ISO format.
    //
    // Note that "z" and "Z" have been switched in defining the format to ES. So "z" is correct here.
    //
    // @see https://docs.oracle.com/javase/8/docs/api/java/time/format/DateTimeFormatter.html
    public const string DATEFORMAT_ES = "yyyy-MM-dd'T'HH:mm:ssz";
}
