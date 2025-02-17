<?php

namespace App\Factory;

use App\Entity\Location;
use App\Entity\Organization;
use App\Model\Feed\FeedItemOrganization;
use App\Repository\OrganizationRepository;
use App\Utils\UriHelper;
use Psr\Log\LoggerInterface;

final readonly class OrganizationFactory
{
    public function __construct(
        private OrganizationRepository $organizationRepository,
        private LoggerInterface $logger,
    ) {
    }

    /**
     * Create or update location entity.
     *
     * @param FeedItemOrganization $input
     *   Organization information from feed item
     *
     * @return Organization
     *   Organization entity base on feed data
     */
    public function createOrUpdate(FeedItemOrganization $input, ?string $base = null): Organization
    {
        $organization = $this->getOrganization($input);
        $organization ??= new Organization();

        if (is_null($organization->getUrl()) && !is_null($input->url)) {
            try {
                $organization->setUrl(UriHelper::getAbsoluteUrl($input->url, $base));
            } catch (\Exception $e) {
                $this->logger->error('Organization: '.$e->getMessage());
            }
        }
        if (is_null($organization->getName()) && !is_null($input->name)) {
            $organization->setName($input->name);
        }
        if (is_null($organization->getMail()) && !is_null($input->mail)) {
            $organization->setMail($input->mail);
        }
        $this->organizationRepository->save($organization);

        return $organization;
    }

    /**
     * Try to get organization from database.
     *
     * @return Organization|null
     *   Fund location entity or null
     */
    private function getOrganization(FeedItemOrganization $organization): ?Organization
    {
        if (null === $organization->name || '' === trim($organization->name)) {
            return null;
        }

        return $this->organizationRepository->findOneBy([
            'name' => $organization->name,
        ]);
    }
}
