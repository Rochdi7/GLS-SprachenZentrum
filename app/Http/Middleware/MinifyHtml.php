<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class MinifyHtml
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        // Only minify HTML responses in production
        if (!$this->shouldMinify($request, $response)) {
            return $response;
        }

        $content = $response->getContent();
        $minified = $this->minify($content);
        $response->setContent($minified);

        return $response;
    }

    /**
     * Check if response should be minified
     */
    protected function shouldMinify(Request $request, Response $response): bool
    {
        // Skip if not production
        if (app()->environment('local', 'testing')) {
            return false;
        }

        // Skip sitemap.xml
        if ($request->path() === 'sitemap.xml') {
            return false;
        }

        // Skip AJAX/JSON requests
        if ($request->ajax() || $request->wantsJson()) {
            return false;
        }

        // Only minify HTML responses
        $contentType = $response->headers->get('Content-Type', '');
        if (!str_contains($contentType, 'text/html') && $contentType !== '') {
            return false;
        }

        // Skip binary/empty responses
        $content = $response->getContent();
        if (empty($content) || !is_string($content)) {
            return false;
        }

        return true;
    }

    /**
     * Minify HTML content
     */
    protected function minify(string $html): string
    {
        // Preserve content in <pre>, <code>, <textarea>, <script>, <style>
        $preserved = [];
        $preservePatterns = [
            '/<pre[^>]*>.*?<\/pre>/is',
            '/<code[^>]*>.*?<\/code>/is',
            '/<textarea[^>]*>.*?<\/textarea>/is',
            '/<script[^>]*>.*?<\/script>/is',
            '/<style[^>]*>.*?<\/style>/is',
        ];

        foreach ($preservePatterns as $pattern) {
            $html = preg_replace_callback($pattern, function ($matches) use (&$preserved) {
                $key = '<!--PRESERVED_' . count($preserved) . '-->';
                $preserved[$key] = $matches[0];
                return $key;
            }, $html);
        }

        // Remove HTML comments (except IE conditionals and preserved markers)
        $html = preg_replace('/<!--(?!\[|PRESERVED_).*?-->/s', '', $html);

        // Remove whitespace between tags
        $html = preg_replace('/>\s+</', '><', $html);

        // Remove multiple spaces
        $html = preg_replace('/\s{2,}/', ' ', $html);

        // Remove spaces around = in attributes
        $html = preg_replace('/\s*=\s*/', '=', $html);

        // Remove newlines and tabs
        $html = str_replace(["\r\n", "\r", "\n", "\t"], '', $html);

        // Trim each line
        $html = preg_replace('/^\s+|\s+$/m', '', $html);

        // Restore preserved content
        foreach ($preserved as $key => $value) {
            $html = str_replace($key, $value, $html);
        }

        return trim($html);
    }
}
