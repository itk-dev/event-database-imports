<?php

namespace App\Command\Schedule;

use Psr\Log\LoggerInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

trait MonitorTrait
{
    private function ping(string $command, string $monitoringUrl, HttpClientInterface $client, LoggerInterface $logger): void
    {
        if ('' !== $monitoringUrl) {
            try {
                $client->request('GET', $monitoringUrl);

                $logger->info($command.': Successfully called monitoringUrl');
            } catch (\Throwable $e) {
                $logger->error($command.': Error calling monitoringUrl: '.$e->getMessage());
            }
        }
    }
}
