<?php

namespace App\Model\Indexing;

final class IndexFieldTypes
{
    // ISO 8601 date "2004-02-12T15:19:21+00:00"
    public const DATEFORMAT = 'c';

    // One would think one could use the named ISO formats (but, NO) and Elastic say they use the format in the link.
    // Here it also needs to be "z" and not "Z" to match the ISO format.
    // @see https://docs.oracle.com/javase/8/docs/api/java/time/format/DateTimeFormatter.html
    public const DATEFORMAT_ES = "yyyy-MM-dd'T'HH:mm:ssz";
}
