<?php

namespace App\Service\Feeds;

use App\Model\Feed\FeedConfiguration;

final class FeedDefaultsMapper
{
    /**
     * Apply default values to input data based on feed configuration.
     *
     * @param array $input
     *   Array feed parsed data
     * @param feedConfiguration $configuration
     *   Feed configuration
     *
     * @return array
     *   Parsed feed data apply default values
     */
    public function apply(array $input, FeedConfiguration $configuration): array
    {
        $defaults = $configuration->defaults;

        $output = [];
        foreach ($input as $key => $data) {
            if (array_key_exists($key, $defaults)) {
                if (is_array($data)) {
                    // Append values into array.
                    $output[$key] = array_merge($defaults[$key], $data);
                } else {
                    // Use default value if data is empty. Otherwise, copy the data.
                    $output[$key] = empty($data) ? $defaults[$key] : $data;
                }

                // Removed processed default for later merge of left-overs.
                unset($defaults[$key]);
            } else {
                // No default so ignore.
                $output[$key] = $data;
            }
        }

        // Merge defaults not in input data.
        return array_merge_recursive($output, $defaults);
    }
}
