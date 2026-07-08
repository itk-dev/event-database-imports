<?php

namespace App\Service\Feeds\Mapper;

use App\Model\Feed\FeedConfiguration;
use CuyZ\Valinor\Mapper\MappingError;
use CuyZ\Valinor\MapperBuilder;

final class FeedConfigurationMapper
{
    /**
     * Get feed configuration object from array.
     *
     * @param array $configuration
     *   Raw configuration array
     *
     * @return FeedConfiguration
     *   Parsed configuration object
     *
     * @throws MappingError
     */
    public function getConfigurationFromArray(array $configuration): FeedConfiguration
    {
        try {
            return new MapperBuilder()
                ->allowPermissiveTypes()
                ->allowSuperfluousKeys()
                ->allowScalarValueCasting()
                ->allowNonSequentialList()
                ->allowUndefinedValues()
                ->mapper()
                ->map(
                    FeedConfiguration::class,
                    $configuration
                );
        } catch (MappingError $error) {
            // @todo: Log mapping error for later debugging.
            // Get flatten list of all messages through the whole nodes tree
            foreach ($error->messages() as $message) {
                echo $message,"\n";
            }
            throw $error;
        }
    }
}
