<?php

declare(strict_types=1);

namespace App\Tests\Service;

use App\Service\TagsNormalizer;
use App\Tests\Utils\PhpUnitUtils;
use PHPUnit\Framework\Attributes\CoversClass;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

#[CoversClass(TagsNormalizer::class)]
final class TagsNormalizerTest extends KernelTestCase
{
    /**
     * @throws \Exception
     */
    public function testTrimLength()
    {
        $tagsNames = PhpUnitUtils::callPrivateMethod(
            $this->getTagsNormalizerService(),
            'trimLength',
            [[
                'laserskæring ',
                ' lasercut',
                '3D print',
                str_repeat('This is an very long long long tag name, that should be trimmed down to size', 4),
            ]]
        );

        $this->assertCount(4, $tagsNames);
        $this->assertEquals('laserskæring', $tagsNames[0]);
        $this->assertEquals('lasercut', $tagsNames[1]);
        $this->assertEquals('3D print', $tagsNames[2]);
        $this->assertEquals(255, strlen($tagsNames[3]));
    }

    /**
     * Helper function to bootstrap the service tested.
     *
     * @return TagsNormalizer
     *   The service
     *
     * @throws \Exception
     */
    private function getTagsNormalizerService(): TagsNormalizer
    {
        self::bootKernel();
        $container = TagsNormalizerTest::getContainer();

        return $container->get(TagsNormalizer::class);
    }
}
