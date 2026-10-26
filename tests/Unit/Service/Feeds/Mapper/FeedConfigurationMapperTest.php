<?php

declare(strict_types=1);

namespace App\Tests\Unit\Service\Feeds\Mapper;

use App\Model\Feed\FeedConfiguration;
use App\Service\Feeds\Mapper\FeedConfigurationMapper;
use CuyZ\Valinor\Mapper\MappingError;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

/**
 * Guards the Valinor configuration mapping. These behaviours (scalar casting,
 * superfluous keys, permissive types and the MappingError branch) exercise the
 * exact MapperBuilder options that change across the Valinor 1.x → 2.x upgrade.
 */
#[CoversClass(FeedConfigurationMapper::class)]
final class FeedConfigurationMapperTest extends TestCase
{
    private FeedConfigurationMapper $mapper;

    protected function setUp(): void
    {
        parent::setUp();
        $this->mapper = new FeedConfigurationMapper();
    }

    /**
     * The shipped configuration template must map into a fully populated
     * FeedConfiguration, including the nested pagination object.
     */
    public function testMapsTemplateConfiguration(): void
    {
        $config = $this->mapper->getConfigurationFromArray(FeedConfiguration::getConfigurationTemplate());

        $this->assertInstanceOf(FeedConfiguration::class, $config);
        $this->assertSame('json', $config->type);
        $this->assertSame('Europe/Copenhagen', $config->timezone);
        $this->assertSame("Y-m-d\TH:i:sP", $config->dateFormat);
        $this->assertArrayHasKey('id', $config->mapping);
        $this->assertInstanceOf(\App\Model\Feed\FeedPagination::class, $config->pagination);
    }

    /**
     * allowSuperfluousKeys(): unknown top-level keys are ignored rather than
     * raising a mapping error.
     */
    public function testSuperfluousKeysAreIgnored(): void
    {
        $data = $this->baseConfiguration();
        $data['unknownKey'] = 'should-be-ignored';

        $config = $this->mapper->getConfigurationFromArray($data);

        $this->assertSame('https://example.test/feed', $config->url);
    }

    /**
     * Scalar value casting: numeric strings for the pagination page/limit are
     * cast to int. This guards the enableFlexibleCasting() -> allowScalarValueCasting()
     * migration in Valinor 2.x.
     */
    public function testNumericStringsAreCastToInt(): void
    {
        $data = $this->baseConfiguration();
        $data['pagination'] = [
            'pageParameter' => 'page',
            'limitParameter' => 'limit',
            'page' => '3',
            'limit' => '25',
        ];

        $config = $this->mapper->getConfigurationFromArray($data);

        $this->assertInstanceOf(\App\Model\Feed\FeedPagination::class, $config->pagination);
        $this->assertSame(3, $config->pagination->page);
        $this->assertSame(25, $config->pagination->limit);
    }

    /**
     * The MappingError branch: a missing required key throws. This guards the
     * Messages::flattenFromNode() -> MappingError::messages() migration.
     */
    public function testMissingRequiredKeyThrowsMappingError(): void
    {
        $data = $this->baseConfiguration();
        unset($data['url']);

        $this->expectException(MappingError::class);

        // The mapper echoes the flattened messages on failure; keep test output clean.
        ob_start();
        try {
            $this->mapper->getConfigurationFromArray($data);
        } finally {
            ob_end_clean();
        }
    }

    /**
     * @return array<string, mixed>
     */
    private function baseConfiguration(): array
    {
        return [
            'type' => 'json',
            'url' => 'https://example.test/feed',
            'base' => 'https://example.test/',
            'timezone' => 'Europe/Copenhagen',
            'rootPointer' => '/-',
            'dateFormat' => "Y-m-d\TH:i:sP",
            'mapping' => [
                'id' => 'id',
            ],
        ];
    }
}
