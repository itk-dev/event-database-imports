<?php

namespace App\Asset;

use EasyCorp\Bundle\EasyAdminBundle\Asset\AssetPackage;
use Symfony\Component\Asset\Context\RequestStackContext;
use Symfony\Component\Asset\PackageInterface;
use Symfony\Component\Asset\PathPackage;
use Symfony\Component\Asset\VersionStrategy\JsonManifestVersionStrategy;
use Symfony\Component\DependencyInjection\Attribute\AsDecorator;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * AssetPackage decorator.
 *
 * EasyAdmin asset url's does not respect the 'framework.assets.base_path' setting, but are hardcoded to
 * the '/bundles/easyadmin' path. To support the 'base_path' configuration we have to decorate the
 * EasyCorp\Bundle\EasyAdminBundle\Asset\AssetPackage class.
 *
 * This is needed because we co-host the new EventDB v2 and the legacy EventDB on the same domain. so we need
 * to be able to route by path prefix.
 *
 * /bundles/easyadmin -> LegacyDB
 * /admin/bundles/easyadmin -> EventDB v2
 *
 * @see https://github.com/EasyCorp/EasyAdminBundle/issues/5382
 */
#[AsDecorator(decorates: AssetPackage::class)]
readonly class EasyAdminAssetPackage implements PackageInterface
{
    private PackageInterface $package;

    public function __construct(RequestStack $requestStack, string $assetBasePath)
    {
        $this->package = new PathPackage(
            $assetBasePath.'/bundles/easyadmin',
            new JsonManifestVersionStrategy(__DIR__.'/../../public/bundles/easyadmin/manifest.json'),
            new RequestStackContext($requestStack)
        );
    }

    public function getUrl(string $path): string
    {
        return $this->package->getUrl($path);
    }

    public function getVersion(string $path): string
    {
        return $this->package->getVersion($path);
    }
}
