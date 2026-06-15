<?php

/**
 * fetch-vimeo-thumbs.php
 *
 * One-time (re-runnable) helper that downloads a poster image for each Vimeo video used
 * on the home page and stores it locally under public/assets/images/video-thumbs/{id}.jpg.
 *
 * WHY: the home page embeds 12 Vimeo iframes. Each eager iframe pulls the full Vimeo player
 * JS (heavy TBT, deprecated-API + 3rd-party-cookie warnings). We replace them with a
 * lightweight click-to-load "facade" (poster image + play button). Storing the poster
 * locally means the facade has ZERO third-party cost until the user actually clicks play.
 *
 * Run:  php scripts/fetch-vimeo-thumbs.php
 *
 * Strategy per id: try Vimeo oEmbed (high-res thumbnail_url), fall back to vumbnail.com.
 */

$ids = [
    // marketing-videos.blade.php
    '1173822209', '1172166445', '1172167791', '1173823269',
    '1173821770', '1172171254', '1172166709', '1172167181',
    // marketing-videos-testimonials.blade.php
    '1172183039', '1172183086', '1172182987', '1172182943',
];

$outDir = realpath(__DIR__ . '/../public') . '/assets/images/video-thumbs';
if (!is_dir($outDir)) {
    if (!mkdir($outDir, 0775, true) && !is_dir($outDir)) {
        fwrite(STDERR, "Cannot create $outDir\n");
        exit(1);
    }
}

function httpGet(string $url, int $timeout = 20): ?string
{
    if (function_exists('curl_init')) {
        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_TIMEOUT => $timeout,
            CURLOPT_USERAGENT => 'GLS-thumb-fetcher/1.0',
            CURLOPT_SSL_VERIFYPEER => true,
        ]);
        $body = curl_exec($ch);
        $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        return ($body !== false && $code >= 200 && $code < 300) ? $body : null;
    }
    $ctx = stream_context_create(['http' => ['timeout' => $timeout, 'header' => "User-Agent: GLS-thumb-fetcher/1.0\r\n"]]);
    $body = @file_get_contents($url, false, $ctx);
    return $body !== false ? $body : null;
}

function highResThumb(string $url): string
{
    // Vimeo oEmbed returns e.g. ..._200x150?... — request a larger crop.
    return preg_replace('/_\d+x\d+/', '_1280x720', $url);
}

$ok = 0;
$fail = 0;
foreach ($ids as $id) {
    $dest = "$outDir/$id.jpg";
    if (is_file($dest) && filesize($dest) > 2000) {
        echo "skip  $id (already present)\n";
        $ok++;
        continue;
    }

    $thumbUrl = null;

    // 1) Vimeo oEmbed
    $json = httpGet('https://vimeo.com/api/oembed.json?width=1280&url=' . rawurlencode('https://vimeo.com/' . $id));
    if ($json) {
        $data = json_decode($json, true);
        if (!empty($data['thumbnail_url'])) {
            $thumbUrl = highResThumb($data['thumbnail_url']);
        }
    }

    // 2) Fallback: vumbnail.com
    if ($thumbUrl === null) {
        $thumbUrl = "https://vumbnail.com/$id.jpg";
    }

    $img = httpGet($thumbUrl);
    if ($img === null && $thumbUrl !== "https://vumbnail.com/$id.jpg") {
        $img = httpGet("https://vumbnail.com/$id.jpg");
    }

    if ($img !== null && strlen($img) > 2000) {
        file_put_contents($dest, $img);
        printf("ok    %s  (%d KB)\n", $id, round(strlen($img) / 1024));
        $ok++;
    } else {
        fwrite(STDERR, "FAIL  $id (no thumbnail)\n");
        $fail++;
    }
}

echo "\nDone. $ok ok, $fail failed. Saved to public/assets/images/video-thumbs/\n";
exit($fail > 0 ? 2 : 0);
