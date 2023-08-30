<?php

namespace App\Services\Feeds\Mapper;

use App\Model\Feed\FeedConfiguration;
use CuyZ\Valinor\Mapper\MappingError;
use CuyZ\Valinor\Mapper\Tree\Message\Messages;
use CuyZ\Valinor\MapperBuilder;

final class FeedConfigurationMapperService
{
    /**
     * Get feed configuration object from array.
     *
     * @param array $configuration
     *   Raw configuration array
     *
     * @return feedConfiguration
     *   Parsed configuration object
     *
     * @throws MappingError
     */
    public function getConfigurationFromArray(array $configuration): FeedConfiguration
    {
        try {
            return (new MapperBuilder())
                ->allowPermissiveTypes()
                ->allowSuperfluousKeys()
                ->enableFlexibleCasting()
                ->mapper()
                ->map(
                    FeedConfiguration::class,
                    $configuration
                );
        } catch (MappingError $error) {
            // @todo: Log mapping error for later debugging.
            // Get flatten list of all messages through the whole nodes tree
            $messages = Messages::flattenFromNode(
                $error->node()
            );
            foreach ($messages as $message) {
                echo $message,"\n";
            }
            throw $error;
        }
    }
}
