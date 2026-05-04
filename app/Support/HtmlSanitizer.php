<?php

namespace App\Support;

use DOMDocument;
use DOMElement;
use DOMNode;
use DOMXPath;

/**
 * Minimal allowlist HTML sanitizer for trusted-but-untrusted admin-authored
 * rich content. Intended to mitigate stored XSS where backoffice editors can
 * write raw HTML that is then rendered on the public site.
 *
 * Strips disallowed tags, disallowed attributes, javascript:/data: URLs (with
 * narrow exceptions), inline event handlers, and dangerous iframe attributes.
 */
class HtmlSanitizer
{
    /**
     * Tags allowed in rich-text blog/article content.
     *
     * @var array<string,array<int,string>>
     */
    private const RICH_TEXT_TAGS = [
        'a'          => ['href', 'title', 'target', 'rel'],
        'p'          => ['class', 'style'],
        'br'         => [],
        'hr'         => [],
        'span'       => ['class', 'style'],
        'div'        => ['class', 'style'],
        'strong'     => [],
        'b'          => [],
        'em'         => [],
        'i'          => [],
        'u'          => [],
        's'          => [],
        'blockquote' => ['class'],
        'code'       => ['class'],
        'pre'        => ['class'],
        'ul'         => ['class'],
        'ol'         => ['class', 'start'],
        'li'         => ['class'],
        'h1'         => ['class'],
        'h2'         => ['class'],
        'h3'         => ['class'],
        'h4'         => ['class'],
        'h5'         => ['class'],
        'h6'         => ['class'],
        'table'      => ['class'],
        'thead'      => [],
        'tbody'      => [],
        'tfoot'      => [],
        'tr'         => [],
        'td'         => ['colspan', 'rowspan'],
        'th'         => ['colspan', 'rowspan', 'scope'],
        'img'        => ['src', 'alt', 'title', 'width', 'height', 'class', 'style'],
        'figure'     => ['class'],
        'figcaption' => ['class'],
        'iframe'     => ['src', 'width', 'height', 'frameborder', 'allowfullscreen', 'loading', 'referrerpolicy', 'allow', 'title', 'style'],
    ];

    /**
     * Sanitize rich-text content (e.g. blog posts).
     */
    public static function richText(?string $html): string
    {
        return self::sanitize((string) $html, self::RICH_TEXT_TAGS);
    }

    /**
     * Sanitize a Google Maps embed snippet. Only an iframe pointing at
     * google.com / openstreetmap.org embeds is allowed; everything else is
     * stripped.
     */
    public static function mapEmbed(?string $html): string
    {
        $html = (string) $html;
        if ($html === '') {
            return '';
        }

        $allowed = [
            'iframe' => ['src', 'width', 'height', 'style', 'frameborder', 'allowfullscreen', 'loading', 'referrerpolicy', 'allow', 'title'],
        ];

        $clean = self::sanitize($html, $allowed);

        if ($clean === '') {
            return '';
        }

        // Re-parse the sanitized output and enforce that every iframe src
        // points at an approved mapping origin.
        $dom = self::loadDom($clean);
        if ($dom === null) {
            return '';
        }

        $root = self::findRoot($dom);
        if ($root === null) {
            return '';
        }

        $xpath = new DOMXPath($dom);
        foreach (iterator_to_array($xpath->query('.//iframe', $root)) as $iframe) {
            /** @var DOMElement $iframe */
            $src = $iframe->getAttribute('src');
            if (!self::isAllowedMapSrc($src)) {
                $iframe->parentNode?->removeChild($iframe);
            }
        }

        return self::serializeRoot($dom, $root);
    }

    /**
     * @param array<string,array<int,string>> $allowedTags
     */
    private static function sanitize(string $html, array $allowedTags): string
    {
        if (trim($html) === '') {
            return '';
        }

        $dom = self::loadDom($html);
        if ($dom === null) {
            return '';
        }

        $root = self::findRoot($dom);
        if ($root === null) {
            return '';
        }

        $xpath = new DOMXPath($dom);

        // Skip the synthetic wrapper itself; only its descendants are processed.
        foreach (iterator_to_array($xpath->query('.//*', $root)) as $node) {
            /** @var DOMElement $node */
            $tag = strtolower($node->nodeName);

            if (!array_key_exists($tag, $allowedTags)) {
                self::unwrapOrRemove($node, $tag);
                continue;
            }

            $allowedAttrs = $allowedTags[$tag];
            foreach (iterator_to_array($node->attributes ?? []) as $attr) {
                $name = strtolower($attr->nodeName);
                $value = (string) $attr->nodeValue;

                if (!in_array($name, $allowedAttrs, true)) {
                    $node->removeAttribute($attr->nodeName);
                    continue;
                }

                if (in_array($name, ['href', 'src'], true) && !self::isSafeUrl($value, $tag)) {
                    $node->removeAttribute($attr->nodeName);
                    continue;
                }

                if ($name === 'style' && !self::isSafeStyle($value)) {
                    $node->removeAttribute($attr->nodeName);
                    continue;
                }

                if ($name === 'target') {
                    // Force safe rel when target=_blank to avoid tabnabbing.
                    if (strtolower($value) === '_blank') {
                        $node->setAttribute('rel', 'noopener noreferrer');
                    }
                }
            }
        }

        return self::serializeRoot($dom, $root);
    }

    private static function unwrapOrRemove(DOMElement $node, string $tag): void
    {
        $dangerous = ['script', 'style', 'iframe', 'object', 'embed', 'link', 'meta', 'form', 'input', 'button', 'textarea', 'select', 'option', 'svg', 'math'];

        if (in_array($tag, $dangerous, true)) {
            $node->parentNode?->removeChild($node);
            return;
        }

        // For unknown but non-dangerous tags, unwrap children into parent.
        $parent = $node->parentNode;
        if ($parent === null) {
            return;
        }

        while ($node->firstChild) {
            $parent->insertBefore($node->firstChild, $node);
        }
        $parent->removeChild($node);
    }

    private static function isSafeUrl(string $url, string $tag): bool
    {
        $url = trim($url);
        if ($url === '') {
            return false;
        }

        // Reject URLs with control characters that can be used to smuggle schemes.
        if (preg_match('/[\x00-\x1F\x7F]/', $url) === 1) {
            return false;
        }

        // Allow relative URLs, fragment, query, and protocol-relative.
        if ($url[0] === '/' || $url[0] === '#' || $url[0] === '?') {
            return true;
        }

        // Anything that looks like a scheme.
        if (preg_match('/^([a-z][a-z0-9+\-.]*):/i', $url, $m) === 1) {
            $scheme = strtolower($m[1]);
            $allowedSchemes = ['http', 'https', 'mailto', 'tel'];
            if ($tag === 'img') {
                $allowedSchemes[] = 'data'; // permit inline images only
            }
            return in_array($scheme, $allowedSchemes, true);
        }

        // No scheme — relative path.
        return true;
    }

    private static function isSafeStyle(string $style): bool
    {
        // Disallow expressions, url(javascript:...), and behavior properties.
        $lower = strtolower($style);
        $blocked = ['expression(', 'javascript:', 'vbscript:', 'behavior:', '@import', 'url(javascript', 'url("javascript', "url('javascript"];
        foreach ($blocked as $needle) {
            if (str_contains($lower, $needle)) {
                return false;
            }
        }
        return true;
    }

    private static function isAllowedMapSrc(string $src): bool
    {
        $src = trim($src);
        if ($src === '') {
            return false;
        }

        $parts = parse_url($src);
        if ($parts === false || empty($parts['scheme']) || empty($parts['host'])) {
            return false;
        }

        if (!in_array(strtolower($parts['scheme']), ['http', 'https'], true)) {
            return false;
        }

        $host = strtolower($parts['host']);
        $allowedHosts = [
            'www.google.com',
            'maps.google.com',
            'www.google.de',
            'www.openstreetmap.org',
            'www.bing.com',
        ];

        return in_array($host, $allowedHosts, true);
    }

    private static function loadDom(string $html): ?DOMDocument
    {
        $dom = new DOMDocument();
        $wrapped = '<?xml encoding="utf-8"?><div data-sanitizer-root="1">' . $html . '</div>';

        $previous = libxml_use_internal_errors(true);
        $loaded = $dom->loadHTML($wrapped, LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD | LIBXML_NONET);
        libxml_clear_errors();
        libxml_use_internal_errors($previous);

        if (!$loaded) {
            return null;
        }

        return $dom;
    }

    private static function findRoot(DOMDocument $dom): ?DOMElement
    {
        $xpath = new DOMXPath($dom);
        $node = $xpath->query('//*[@data-sanitizer-root="1"]')->item(0);
        if ($node instanceof DOMElement) {
            return $node;
        }
        return null;
    }

    private static function serializeRoot(DOMDocument $dom, DOMElement $root): string
    {
        $out = '';
        foreach ($root->childNodes as $child) {
            $out .= $dom->saveHTML($child);
        }
        return $out;
    }
}
