<?php

namespace App\Command;

use App\Services\Etl\Transformers\DataTimeTransformer;
use Cerbero\JsonParser\Exceptions\SyntaxException;
use Cerbero\JsonParser\JsonParser;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Wizaplace\Etl\Etl;
use Wizaplace\Etl\Extractors\Json;
use Wizaplace\Etl\Loaders\MemoryLoader;

#[AsCommand(
    name: 'app:json:parse',
    description: 'Test library parsing',
)]
class JsonParseTestCommand extends Command
{
    public function __construct(
        private readonly Etl $etl,
        private readonly Json $jsonExtractor,
        private readonly MemoryLoader $memoryLoader,
        private readonly DataTimeTransformer $dataTimeTransformer,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {

    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $data = '[{"Id":18354,"Name:"Jazz nbrunch med Dayyani /Kwella /Sejthen 0807","Title":"Jazz n´brunch med Dayyani /Kwella /Sejthen","Place":" Restauranten, Niv. 8","Url":"https://www.aros.dk/da/besoeg/kalender/jazz-n-brunch-med-dayyani-kwella-sejthen-0807/","Type":null,"Teaser":"Kom til koncert med brunch og bobler på toppen af ARoS lørdag den 8. juli.","Image":"https://www.aros.dk/media/4935/87-dayyani.jpeg","ImageLocalUrl":"/media/4935/87-dayyani.jpeg?center=0.30111111111111111,0.42833333333333334&mode=crop&width=4480&height=6720&upscale=false&rnd=133306855920000000","Tags":"","DateReplacementText":"","DateFrom":"2023-07-08T11:00:00","DateTo":"2023-07-08T14:00:00","Date":"8 juli - 8 juli 2023","OpeningHours":"","ExternalLink":"","ExternalLinkText":"","Price":"350 kr. pr. person inkl. brunch. (Billetten gælder ikke entré til museet)","BuyTicketsLink":"","BuyTicketsText":"","FacebookLink":"","FaceBookLinkText":"","ReadMore":"Læs mere","ReadLess":"Læs mindre","Description":"","Label":"Koncert","Time":"11.00 - 14.00"},{"Id":22652,"Name":"9. juli / Familierundvisning","Title":"Familierundvisning","Place":"Niv. 4","Url":"https://www.aros.dk/da/besoeg/kalender/9-juli-familierundvisning/","Type":null,"Teaser":"Kom med på en tur ind i kunstens verden for hele familien.","Image":"https://www.aros.dk/media/4009/lisebalsby_42_okt2018.jpg","ImageLocalUrl":"/media/4009/lisebalsby_42_okt2018.jpg?anchor=center&mode=crop&width=5760&height=3840&upscale=false&rnd=132908684440000000","Tags":"","DateReplacementText":"","DateFrom":"2023-07-09T11:00:00","DateTo":"2023-07-09T11:45:00","Date":"9 juli - 9 juli 2023","OpeningHours":"","ExternalLink":"","ExternalLinkText":"","Price":"","BuyTicketsLink":"","BuyTicketsText":"","FacebookLink":"","FaceBookLinkText":"","ReadMore":"Læs mere","ReadLess":"Læs mindre","Description":"","Label":"For hele familien","Time":"11.00 – 11.45"},{"Id":18355,"Name":"Jazz n´brunch med Rasmus Bøgelund med Kwella & Friis 1507","Title":"Jazz n´brunch: Rasmus Bøgelund med Kwella & Friis ","Place":" Restauranten, Niv. 8","Url":"https://www.aros.dk/da/besoeg/kalender/jazz-n-brunch-med-rasmus-boegelund-med-kwella-friis-1507/","Type":null,"Teaser":"Kom til koncert med brunch og bobler på toppen af ARoS lørdag den 15. juli.","Image":"https://www.aros.dk/media/4913/rasmus_boegelund_2.jpg","ImageLocalUrl":"/media/4913/rasmus_boegelund_2.jpg?center=0.61,0.48833333333333334&mode=crop&width=4608&height=3072&upscale=false&rnd=133210183500000000","Tags":"","DateReplacementText":"","DateFrom":"2023-07-15T11:00:00","DateTo":"2023-07-15T14:00:00","Date":"15 juli - 15 juli 2023","OpeningHours":"","ExternalLink":"","ExternalLinkText":"","Price":"350 kr. pr. person inkl. brunch. (Billetten gælder ikke entré til museet)","BuyTicketsLink":"","BuyTicketsText":"","FacebookLink":"","FaceBookLinkText":"","ReadMore":"Læs mere","ReadLess":"Læs mindre","Description":"","Label":"Koncert","Time":"11.00 - 14.00"},{"Id":22647,"Name":"16. juli / Familierundvisning","Title":"Familierundvisning","Place":"Niv. 4","Url":"https://www.aros.dk/da/besoeg/kalender/16-juli-familierundvisning/","Type":null,"Teaser":"Kom med på en tur ind i kunstens verden for hele familien.","Image":"https://www.aros.dk/media/4009/lisebalsby_42_okt2018.jpg","ImageLocalUrl":"/media/4009/lisebalsby_42_okt2018.jpg?anchor=center&mode=crop&width=5760&height=3840&upscale=false&rnd=132908684440000000","Tags":"","DateReplacementText":"","DateFrom":"2023-07-16T11:00:00","DateTo":"2023-07-16T11:45:00","Date":"16 juli - 16 juli 2023","OpeningHours":"","ExternalLink":"","ExternalLinkText":"","Price":"","BuyTicketsLink":"","BuyTicketsText":"","FacebookLink":"","FaceBookLinkText":"","ReadMore":"Læs mere","ReadLess":"Læs mindre","Description":"","Label":"For hele familien","Time":"11.00 – 11.45"},{"Id":22648,"Name":"23. juli / Familierundvisning","Title":"Familierundvisning","Place":"Niv. 4","Url":"https://www.aros.dk/da/besoeg/kalender/23-juli-familierundvisning/","Type":null,"Teaser":"Kom med på en tur ind i kunstens verden for hele familien.","Image":"https://www.aros.dk/media/4009/lisebalsby_42_okt2018.jpg","ImageLocalUrl":"/media/4009/lisebalsby_42_okt2018.jpg?anchor=center&mode=crop&width=5760&height=3840&upscale=false&rnd=132908684440000000","Tags":"","DateReplacementText":"","DateFrom":"2023-07-23T11:00:00","DateTo":"2023-07-23T11:45:00","Date":"23 juli - 23 juli 2023","OpeningHours":"","ExternalLink":"","ExternalLinkText":"","Price":"","BuyTicketsLink":"","BuyTicketsText":"","FacebookLink":"","FaceBookLinkText":"","ReadMore":"Læs mere","ReadLess":"Læs mindre","Description":"","Label":"For hele familien","Time":"11.00 – 11.45"},{"Id":22649,"Name":"30. juli / Familierundvisning","Title":"Familierundvisning","Place":"Niv. 4","Url":"https://www.aros.dk/da/besoeg/kalender/30-juli-familierundvisning/","Type":null,"Teaser":"Kom med på en tur ind i kunstens verden for hele familien.","Image":"https://www.aros.dk/media/4009/lisebalsby_42_okt2018.jpg","ImageLocalUrl":"/media/4009/lisebalsby_42_okt2018.jpg?anchor=center&mode=crop&width=5760&height=3840&upscale=false&rnd=132908684440000000","Tags":"","DateReplacementText":"","DateFrom":"2023-07-30T11:00:00","DateTo":"2023-07-30T11:45:00","Date":"30 juli - 30 juli 2023","OpeningHours":"","ExternalLink":"","ExternalLinkText":"","Price":"","BuyTicketsLink":"","BuyTicketsText":"","FacebookLink":"","FaceBookLinkText":"","ReadMore":"Læs mere","ReadLess":"Læs mindre","Description":"","Label":"For hele familien","Time":"11.00 – 11.45"},{"Id":22650,"Name":"6. august / Familierundvisning","Title":"Familierundvisning","Place":"Niv. 4","Url":"https://www.aros.dk/da/besoeg/kalender/6-august-familierundvisning/","Type":null,"Teaser":"Kom med på en tur ind i kunstens verden for hele familien.","Image":"https://www.aros.dk/media/4009/lisebalsby_42_okt2018.jpg","ImageLocalUrl":"/media/4009/lisebalsby_42_okt2018.jpg?anchor=center&mode=crop&width=5760&height=3840&upscale=false&rnd=132908684440000000","Tags":"","DateReplacementText":"","DateFrom":"2023-08-06T11:00:00","DateTo":"2023-08-06T11:45:00","Date":"6 august - 6 august 2023","OpeningHours":"","ExternalLink":"","ExternalLinkText":"","Price":"","BuyTicketsLink":"","BuyTicketsText":"","FacebookLink":"","FaceBookLinkText":"","ReadMore":"Læs mere","ReadLess":"Læs mindre","Description":"","Label":"For hele familien","Time":"11.00 – 11.45"},{"Id":21546,"Name":"17. august / Annette Messager – Désirs désordonnés","Title":"Annette Messager – Désirs désordonnés / Disordered desires","Place":"Niv. 3, Auditoriet","Url":"https://www.aros.dk/da/besoeg/kalender/17-august-annette-messager-désirs-désordonnés/","Type":null,"Teaser":"I forbindelse med udstillingen Désirs désordonnés af Annette Messager viser vi den franske film Une aussi longue absence af Henri Colpi.","Image":"https://www.aros.dk/media/5014/skaermbillede-2023-05-26-kl-125826.png","ImageLocalUrl":"/media/5014/skaermbillede-2023-05-26-kl-125826.png?anchor=center&mode=crop&width=738&height=977&upscale=false&rnd=133295795180000000","Tags":"","DateReplacementText":"","DateFrom":"2023-08-17T19:00:00","DateTo":"2023-08-17T20:30:00","Date":"17 august - 17 august 2023","OpeningHours":"","ExternalLink":"","ExternalLinkText":"","Price":"Gratis med årskort eller efter betalt entré.","BuyTicketsLink":"","BuyTicketsText":"","FacebookLink":"","FaceBookLinkText":"","ReadMore":"Læs mere","ReadLess":"Læs mindre","Description":"","Label":"Fransk film","Time":"19.00 – 20.30"},{"Id":22637,"Name":"Koncert på taget med Karmen Rõivassepp + Machina Mundi 1808","Title":"Koncert på taget med Karmen Rõivassepp + Machina Mundi","Place":"Tagterrasen","Url":"https://www.aros.dk/da/besoeg/kalender/koncert-paa-taget-med-karmen-rõivassepp-plus-machina-mundi-1808/","Type":null,"Teaser":"Kom på ARoS fredag den 18. august og hør den fantastiske sangerinde Karmen Rõivassepp med jazzorkestret Machina Mundi.","Image":"https://www.aros.dk/media/5066/180823-karmen-roivassepp-plus-machina-mundi-1.jpg","ImageLocalUrl":"/media/5066/180823-karmen-roivassepp-plus-machina-mundi-1.jpg?anchor=center&mode=crop&width=6720&height=4480&upscale=false&rnd=133329544290000000","Tags":"","DateReplacementText":"","DateFrom":"2023-08-18T19:00:00","DateTo":"2023-08-18T21:00:00","Date":"18 august - 18 august 2023","OpeningHours":"","ExternalLink":"","ExternalLinkText":"","Price":"Med årskort: 100 kr. / Uden årskort: 150 kr. ex. entré til museet.","BuyTicketsLink":"","BuyTicketsText":"","FacebookLink":"","FaceBookLinkText":"","ReadMore":"Læs mere","ReadLess":"Læs mindre","Description":"","Label":"Koncert","Time":"19.00 – 21.00"},{"Id":21475,"Name":"Yoga på tagterrassen 2308","Title":"UDSOLGT • Yoga på taget","Place":"Tagterrasen","Url":"https://www.aros.dk/da/besoeg/kalender/yoga-paa-tagterrassen-2308/","Type":null,"Teaser":"Kom og vær med til yoga i skæret af regnbuens farver på ARoS tagterrasse, når yogalærer Zanne Kilden byder på udendørs yoga, hvor du får styrket krop og sjæl i unikke omgivelser onsdag den. 23 august fra kl. 16.30 – 18.00.","Image":"https://www.aros.dk/media/3587/file-22.jpeg","ImageLocalUrl":"/media/3587/file-22.jpeg?anchor=center&mode=crop&width=4928&height=3264&upscale=false&rnd=132646786610000000","Tags":"","DateReplacementText":"","DateFrom":"2023-08-23T16:30:00","DateTo":"2023-08-23T18:00:00","Date":"23 august - 23 august 2023","OpeningHours":"","ExternalLink":"","ExternalLinkText":"","Price":"80 kr. med årskort / 130 kr. uden årskort ekskl. entré til museet.","BuyTicketsLink":"","BuyTicketsText":"","FacebookLink":"","FaceBookLinkText":"","ReadMore":"Læs mere","ReadLess":"Læs mindre","Description":"","Label":"UDSOLGT • Lige under regnbuen","Time":"16.30 – 18.00"}]';

        foreach ($this->parseFn($data, 0) as $str) {
            $io->writeln($str);
        }

        return Command::SUCCESS;
    }

    private function parseFn($data, $index = 0) {
        $parse = new JsonParser($data);

        try {
            $parse->lazyPointer('/' . $index);


            foreach ($parse as $key => $subParser) {
                try {
                    foreach ($subParser as $innerKey => $value) {
                        if (!is_object($value))
                            yield $key.' => '.$value;
                    }
                    $key++;
                    yield from $this->parseFn($data, $key);
                } catch (SyntaxException|\Exception $e) {
                    // Just ignore and go to next element.
                    $key++;
                    yield from $this->parseFn($data, $key);
                    break;
                }
            }
        } catch (SyntaxException|\Exception $e) {
            // Hit's here every time we try to read an element after the syntax error.
            $index++;
            yield from $this->parseFn($data, $index);
            return;
        }
    }
}


