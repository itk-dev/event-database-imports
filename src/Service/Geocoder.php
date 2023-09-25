<?php

namespace App\Service;

use App\Entity\Address;
use App\Exception\GeocoderException;
use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\DecodingExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

final class Geocoder implements GeocoderInterface
{
    public function __construct(
        private readonly HttpClientInterface $client,
    ) {
    }

    public function encode(Address $address): array
    {
        $query = $this->buildQuery($address);
        $url = $this->runQuery($query);
        $response = $this->client->request(
            'GET',
            $url
        );
        if (200 !== $response->getStatusCode()) {
            throw new GeocoderException('Non 200 status returned from service', $response->getStatusCode());
        }
        $content = $response->toArray();

        if (isset($content['adgangspunkt']['koordinater'])) {
            $longitude = $content['adgangspunkt']['koordinater'][0];
            $latitude = $content['adgangspunkt']['koordinater'][1];

            return [$latitude, $longitude];
        }

        throw new GeocoderException('Unable to look up address');
    }

    /**
     * Build query to use with DAWA "datavask".
     *
     * @param address $address
     *   Address entity to extract address from
     *
     * @return string
     *   The extracted address as a string
     */
    private function buildQuery(Address $address): string
    {
        $query = [];
        $query[] = $address->getStreet() ?? '';
        $query[] = $address->getSuite() ?? '';
        $query[] = $address->getPostalCode() ?? '';
        $query[] = $address->getCity() ?? '';

        return implode(', ', array_filter($query));
    }

    /**
     * Call DAWA "datavask" to get link to address with coordinates.
     *
     * The "datavask" may return many address references, but as there are no indication in the data to which on is the
     * best match we simply assumes that the result are sorted and takes the first one returned.
     *
     * @return string
     *   URL to the detailed record for the address if found
     *
     * @throws ClientExceptionInterface
     * @throws DecodingExceptionInterface
     * @throws GeocoderException
     * @throws RedirectionExceptionInterface
     * @throws ServerExceptionInterface
     * @throws TransportExceptionInterface
     */
    private function runQuery(string $query): string
    {
        $response = $this->client->request(
            'GET',
            'https://api.dataforsyningen.dk/datavask/adgangsadresser?betegnelse='.$query
        );
        if (200 !== $response->getStatusCode()) {
            throw new GeocoderException('Non 200 status returned from service', $response->getStatusCode());
        }
        $content = $response->toArray();

        if (isset($content['resultater'][0]['adresse']['href'])) {
            return $content['resultater'][0]['adresse']['href'];
        }

        throw new GeocoderException('Unable to look up address');
    }
}
