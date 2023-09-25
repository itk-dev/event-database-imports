<?php

namespace App\Service;

use App\Entity\Address;
use App\Exception\GeocoderException;
use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\DecodingExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;

interface GeocoderInterface
{
    /**
     * Encode address into map coordinates.
     *
     * @return array
     *   The coordinates found using geo-encoding as a set of float values
     *
     * @throws ClientExceptionInterface
     * @throws DecodingExceptionInterface
     * @throws GeocoderException
     * @throws RedirectionExceptionInterface
     * @throws ServerExceptionInterface
     * @throws TransportExceptionInterface
     */
    public function encode(Address $address): array;
}
