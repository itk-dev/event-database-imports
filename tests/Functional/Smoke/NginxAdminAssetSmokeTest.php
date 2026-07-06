<?php

declare(strict_types=1);

namespace App\Tests\Functional\Smoke;

use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpClient\HttpClient;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;

/**
 * Guards the nginx APP_PATH_PREFIX rewrite (regression fixed in PR #79).
 *
 * The admin UI is served under APP_PATH_PREFIX (e.g. "/admin") while Symfony
 * installs its assets at the web root, so nginx must rewrite "/admin/<asset>"
 * to "/<asset>". Without that rewrite the admin loads with no CSS/JS. This
 * exercises the running nginx over HTTP — a kernel-based functional test would
 * bypass nginx and never catch a broken rewrite.
 */
final class NginxAdminAssetSmokeTest extends TestCase
{
    /**
     * A versioned admin-prefixed asset is served through nginx (200), proving the rewrite is active.
     */
    public function testAdminPrefixedAssetIsServedThroughNginx(): void
    {
        $asset = $this->findInstalledAsset();
        if (null === $asset) {
            self::markTestSkipped('EasyAdmin assets are not installed; run "composer install" (assets:install).');
        }

        $prefix = rtrim((string) ($_SERVER['APP_PATH_PREFIX'] ?? '/admin'), '/');
        $baseUrl = rtrim((string) (getenv('SMOKE_NGINX_URL') ?: 'http://nginx:8080'), '/');
        $url = sprintf('%s%s/%s', $baseUrl, $prefix, $asset);

        try {
            $status = HttpClient::create()->request('GET', $url)->getStatusCode();
        } catch (TransportExceptionInterface $e) {
            self::markTestSkipped(sprintf('nginx not reachable at %s (requires the docker stack): %s', $baseUrl, $e->getMessage()));
        }

        self::assertSame(
            200,
            $status,
            sprintf(
                'Expected admin-prefixed asset "%s" to be served via the nginx APP_PATH_PREFIX rewrite, got %d. '
                .'The "rewrite ^${APP_PATH_PREFIX}/(.*) /$1 break;" rule in .docker/templates/default.conf.template is likely missing.',
                $url,
                $status,
            ),
        );
    }

    /**
     * Locates a content-hashed EasyAdmin asset installed under the web root.
     */
    private function findInstalledAsset(): ?string
    {
        $publicDir = dirname(__DIR__, 3).'/public';
        foreach (['bundles/easyadmin/*.css', 'bundles/easyadmin/*.js'] as $pattern) {
            $matches = glob($publicDir.'/'.$pattern) ?: [];
            if ([] !== $matches) {
                return 'bundles/easyadmin/'.basename($matches[0]);
            }
        }

        return null;
    }
}
