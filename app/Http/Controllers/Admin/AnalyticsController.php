<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\PosthogService;

class AnalyticsController extends Controller
{
    public function index(PosthogService $posthog)
    {
        $qTotal24h = <<<HOGQL
SELECT count() AS total
FROM events
WHERE timestamp > now() - INTERVAL 24 HOUR
HOGQL;

        $qLikes7d = <<<HOGQL
SELECT formatDateTime(timestamp, '%Y-%m-%d') AS dia, count() AS c
FROM events
WHERE event = 'demo_like_image'
  AND timestamp > now() - INTERVAL 7 DAY
GROUP BY dia
ORDER BY dia ASC
HOGQL;

        $qSearch7d = <<<HOGQL
SELECT formatDateTime(timestamp, '%Y-%m-%d') AS dia, count() AS c
FROM events
WHERE event = 'demo_search_clicked'
  AND timestamp > now() - INTERVAL 7 DAY
GROUP BY dia
ORDER BY dia ASC
HOGQL;

        $qFav7d = <<<HOGQL
SELECT count() AS total
FROM events
WHERE event = 'demo_favorite_saved'
  AND timestamp > now() - INTERVAL 7 DAY
HOGQL;

        $total24h = $posthog->query($qTotal24h);
        $likes7d = $posthog->query($qLikes7d);
        $search7d = $posthog->query($qSearch7d);
        $fav7d = $posthog->query($qFav7d);

        $likesLabels = [];
        $likesData = [];
        if ($likes7d['ok']) {
            foreach ($likes7d['rows'] as $row) {
                $likesLabels[] = $row[0];
                $likesData[] = (int) ($row[1] ?? 0);
            }
        }

        $searchLabels = [];
        $searchData = [];
        if ($search7d['ok']) {
            foreach ($search7d['rows'] as $row) {
                $searchLabels[] = $row[0];
                $searchData[] = (int) ($row[1] ?? 0);
            }
        }

        return view('admin.settings.analytics', [
            'total24h'     => $total24h['ok'] ? (int) ($total24h['rows'][0][0] ?? 0) : 0,
            'fav7d'        => $fav7d['ok'] ? (int) ($fav7d['rows'][0][0] ?? 0) : 0,
            'likesLabels'  => $likesLabels,
            'likesData'    => $likesData,
            'searchLabels' => $searchLabels,
            'searchData'   => $searchData,
            'apiErrors'    => [
                'total24h' => $total24h['ok'] ? null : ($total24h['error'] ?? null),
                'likes7d'  => $likes7d['ok'] ? null : ($likes7d['error'] ?? null),
                'search7d' => $search7d['ok'] ? null : ($search7d['error'] ?? null),
                'fav7d'    => $fav7d['ok'] ? null : ($fav7d['error'] ?? null),
            ],
        ]);
    }
}
