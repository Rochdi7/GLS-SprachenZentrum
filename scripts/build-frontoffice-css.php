<?php

/**
 * build-frontoffice-css.php
 *
 * Flattens the @import chain of public/assets/css/frontoffice/style.css into a single
 * file: public/assets/css/frontoffice/style.bundle.css
 *
 * WHY: style.css is a list of ~16 @import url(...) statements. Each @import is only
 * discovered AFTER its parent stylesheet downloads, creating a serial request waterfall
 * that blocks first paint (and the CSS-background hero LCP) for many round-trips on
 * mobile. Concatenating them into one file removes the waterfall entirely.
 *
 * SAFE/REVERSIBLE: style.css is never modified. If the bundle is absent the Blade layout
 * falls back to style.css. Re-run after editing any imported CSS file:
 *
 *     php scripts/build-frontoffice-css.php
 *
 * url() rewriting: each imported file may reference assets relative to ITS OWN folder.
 * The bundle lives in public/assets/css/frontoffice/, so we rewrite every relative url()
 * to an absolute /assets/... path based on the source file's directory. Absolute urls
 * (starting with /, http, data:) are left untouched.
 */

$publicDir = realpath(__DIR__ . '/../public');
if ($publicDir === false) {
    fwrite(STDERR, "Cannot resolve public/ directory.\n");
    exit(1);
}

$entry = $publicDir . '/assets/css/frontoffice/style.css';
$output = $publicDir . '/assets/css/frontoffice/style.bundle.css';

if (!is_file($entry)) {
    fwrite(STDERR, "Entry stylesheet not found: $entry\n");
    exit(1);
}

/**
 * Convert a CSS file path (absolute on disk) into its web path (/assets/...).
 */
function webPathOf(string $absPath, string $publicDir): string
{
    $rel = str_replace('\\', '/', substr($absPath, strlen($publicDir)));
    return '/' . ltrim($rel, '/');
}

/**
 * Resolve an @import target (the string inside url('...')) to an absolute disk path,
 * relative to the directory of the file that contains the @import.
 */
function resolveImport(string $target, string $currentDir, string $publicDir): ?string
{
    $target = trim($target);

    // Strip media query suffix if any (e.g. @import url(x.css) screen;) — handled by caller.
    if ($target === '') {
        return null;
    }

    if (str_starts_with($target, 'http://') || str_starts_with($target, 'https://') || str_starts_with($target, '//')) {
        return null; // remote import — cannot inline, leave as-is
    }

    if (str_starts_with($target, '/')) {
        $abs = $publicDir . $target;
    } else {
        $abs = $currentDir . '/' . $target;
    }

    $real = realpath($abs);
    return $real !== false ? $real : null;
}

/**
 * Rewrite relative url(...) references inside a CSS body so they resolve from the
 * bundle location. Source dir is where the CSS file physically lives.
 */
function rewriteUrls(string $css, string $sourceDir, string $publicDir): string
{
    return preg_replace_callback(
        '/url\(\s*([\'"]?)([^\'")]+)\1\s*\)/i',
        function ($m) use ($sourceDir, $publicDir) {
            $quote = $m[1];
            $url = trim($m[2]);

            // Leave absolute, remote, data and fragment urls untouched.
            if (
                $url === '' ||
                str_starts_with($url, '/') ||
                str_starts_with($url, 'http://') ||
                str_starts_with($url, 'https://') ||
                str_starts_with($url, '//') ||
                str_starts_with($url, 'data:') ||
                str_starts_with($url, '#')
            ) {
                return 'url(' . $quote . $url . $quote . ')';
            }

            // Resolve relative to the source file directory.
            $abs = realpath($sourceDir . '/' . $url);
            if ($abs === false) {
                // Could not resolve (e.g. missing file) — keep original to avoid breakage.
                return 'url(' . $quote . $url . $quote . ')';
            }

            $web = webPathOf($abs, $publicDir);
            return 'url(' . $quote . $web . $quote . ')';
        },
        $css
    );
}

/**
 * Inline @import statements recursively. Returns flattened CSS.
 */
function inlineCss(string $file, string $publicDir, array &$seen): string
{
    $real = realpath($file);
    if ($real === false) {
        return "/* missing: $file */\n";
    }
    if (isset($seen[$real])) {
        return "/* already inlined: " . webPathOf($real, $publicDir) . " */\n";
    }
    $seen[$real] = true;

    $dir = dirname($real);
    $css = file_get_contents($real);
    if ($css === false) {
        return "/* unreadable: $file */\n";
    }

    // Process line-leading @import url(...) statements. We only inline @import at the top
    // of the file structure (CSS spec requires them first anyway). Replace each with the
    // inlined+url-rewritten content of the target.
    $css = preg_replace_callback(
        '/@import\s+url\(\s*([\'"]?)([^\'")]+)\1\s*\)\s*([^;]*);/i',
        function ($m) use ($dir, $publicDir, &$seen) {
            $target = $m[2];
            $media = trim($m[3]);
            $resolved = resolveImport($target, $dir, $publicDir);
            if ($resolved === null) {
                // Remote or unresolved import: keep it verbatim (valid only if still first,
                // but these are local in this project, so this path is the remote-safe net).
                return $m[0];
            }
            $inner = inlineCss($resolved, $publicDir, $seen);
            $header = "\n/* ===== inlined: " . webPathOf($resolved, $publicDir) . " ===== */\n";
            if ($media !== '') {
                return $header . "@media $media {\n" . $inner . "\n}\n";
            }
            return $header . $inner;
        },
        $css
    );

    // After inlining children, rewrite this file's own relative url() refs.
    $css = rewriteUrls($css, $dir, $publicDir);

    return $css;
}

$seen = [];
$bundle = "/* AUTO-GENERATED by scripts/build-frontoffice-css.php — DO NOT EDIT BY HAND. */\n"
    . "/* Source of truth: assets/css/frontoffice/style.css (edit that, then re-run the script). */\n\n";
$bundle .= inlineCss($entry, $publicDir, $seen);

if (file_put_contents($output, $bundle) === false) {
    fwrite(STDERR, "Failed to write bundle: $output\n");
    exit(1);
}

$kb = round(strlen($bundle) / 1024, 1);
echo "Wrote " . webPathOf(realpath($output), $publicDir) . " ({$kb} KB, " . count($seen) . " files inlined).\n";
