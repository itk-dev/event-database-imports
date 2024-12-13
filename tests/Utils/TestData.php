<?php

declare(strict_types=1);

namespace App\Tests\Utils;

/**
 * This test data is not live data, but combined data from different input sources.
 */
final class TestData
{
    public const FEED_ITEM_DATA = [
        'nid' => '30506',
        'url' => 'https://www.aakb.dk/arrangementer/teknologi/aabent-lab-60',
        'title' => 'Åbent Lab',
        'category' => 'Teknologi',
        'tags' => [
            'laserskæring',
            'lasercut',
            '3D print',
        ],
        'tags_string' => 'laserskæring,lasercut , 3D print',
        'lead' => 'Vi holder åbent i labbet - kig forbi, hvis du er nysgerrig, har en ide eller en fil du gerne vil have skåret/printet.',
        'body' => "<p><strong>Vi holder åbent i labbet - kig forbi, hvis du er nysgerrig, har en ide eller en fil du gerne vil have skåret/printet.</strong></p> <p>På Dokk1 har vi et Maker Lab, hvor der er laserskærer og 3D printer. Vi har også forskellig hobby elektronik, som Micro:bit, Arduino og Little Bits.</p>\n<p>Åbent Lab er ikke en workshop eller undervisning; det er dig, din ide og motivation, der sætter rammen!</p>\n<p>Vi har åbent de fleste onsdage og enkelte lørdage, se mere på vores <a href='http://www.aakb.dk/makerlab'> temaside</a>.</p>\n<p>Alle er velkomne fra 12 år.</p>\n<h3> </h3>\n<h3><strong>Covid-19</strong></h3>\n<p>Deltagelse kræver gyldigt coronapas</p>\n<p><br />Vi forbeholder os ret til at foretage ændringer i de enkelte arrangementer, hvis corona-situationen skulle ændre sig.</p>\n<p>Aarhus Bibliotekerne følger sundhedsmyndighedernes anvisninger, så det er trygt at deltage i bibliotekernes arrangementer både for brugere og for ansatte.</p>",
        'date' => [
            'start' => '2021-06-09T13:30:00+00:00',
            'stop' => '2021-06-09T15:30:00+00:00',
        ],
        'images' => [
            'list' => 'https://www.aakb.dk/sites/www.aakb.dk/files/list_image/event/lampeprototyper.jpg',
            'title' => 'https://www.aakb.dk/sites/www.aakb.dk/files/title_image/event/makerlab_1.jpg',
        ],
        'location' => [
            'hint' => '',
            'thoroughfare' => 'Hack Kampmanns Plads 2',
            'postal_code' => '8000',
            'locality' => 'Aarhus',
            'mail' => 'dokk1-hovedbiblioteket@aarhus.dk',
            'phone' => '89 40 92 00 Borgerservice og Bibliotekers hovednummer',
            'name' => 'Hovedbiblioteket',
            'coordinates' => [
                'lat' => '56.1535',
                'lon' => '10.2142',
            ],
        ],
        'price' => '100',
        'tickets' => [
            'url' => 'https://tickets.online.dk/id=235123451',
        ],
        'occurrences' => [
            [
                'startDate' => '2023-08-30T19:30:00+02:00',
                'endDate' => '2023-08-30T20:45:00+02:00',
            ], [
                'startDate' => '2023-08-31T20:30:00+02:00',
                'endDate' => '2023-08-31T21:45:00+02:00',
            ],
        ],
    ];

    public const LONG_STRING = 'Lorem Ipsum is simply dummy teãxt of the printing and typesettiŝng industry. Lorem Ipsum has been the industry\'s staænard dummy text ever since the 15ø0s, when an unknown printer took a galley of type and scrambled it to make a type specimen book. It survived not only five centuries, but also the leap into electronic typesetting, remaining essentially unchanged.';
}
