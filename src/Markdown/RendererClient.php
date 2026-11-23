<?php

namespace App\Markdown;

use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Contracts\HttpClient\Exception\ExceptionInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

/**
 * Client of the `renderer` Docker Compose service (docker/renderer): Shiki highlighting and
 * Mermaid diagrams. Every method returns null when the service fails or rejects the content
 * (e.g. a Mermaid syntax error), so callers can fall back to a plain rendering.
 */
final readonly class RendererClient
{
    public function __construct(
        private HttpClientInterface $httpClient,
        #[Autowire(env: 'RENDERER_URL')]
        private string $rendererUrl,
        private LoggerInterface $logger,
    ) {
    }

    /**
     * @return string|null Shiki's `<pre class="shiki">` HTML, with light and dark colours
     */
    public function highlight(string $code, string $language): ?string
    {
        return $this->post('/highlight', ['code' => $code, 'lang' => $language])['html'] ?? null;
    }

    /**
     * @param string $svgId id of the `<svg>`, which Mermaid also uses to scope its styles: unique per page
     * @param string $theme `light` or `dark`
     */
    public function mermaid(string $code, string $svgId, string $theme = 'light'): ?string
    {
        return $this->post('/mermaid', ['code' => $code, 'svgId' => $svgId, 'theme' => $theme])['svg'] ?? null;
    }

    /**
     * @param array<string, string> $payload
     *
     * @return array<string, mixed>|null
     */
    private function post(string $path, array $payload): ?array
    {
        try {
            return $this->httpClient->request('POST', $this->rendererUrl.$path, [
                'json' => $payload,
                'timeout' => 30,
            ])->toArray();
        } catch (ExceptionInterface $exception) {
            $this->logger->warning('Renderer service call {path} failed: {message}', [
                'path' => $path,
                'message' => $exception->getMessage(),
            ]);

            return null;
        }
    }
}
