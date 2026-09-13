<?php
declare(strict_types=1);

// Landing-page tracking: records public page views and reports on them.
// Every database call is defensive so the site keeps working before the
// page_views migration has been imported.

const ANALYTICS_REFERRER_MAX = 2048;
const ANALYTICS_USER_AGENT_MAX = 512;

/**
 * Public routes that count as landing pages.
 */
function is_public_route(string $path): bool
{
    if ($path === '') {
        return true;
    }
    if (in_array($path, ['about', 'products', 'impact', 'blog', 'contact'], true)) {
        return true;
    }
    return str_starts_with($path, 'products/') || str_starts_with($path, 'blog/');
}

/**
 * Records the current request as a landing-page view. Safe to call on every
 * request: it ignores non-GET traffic, admin sessions, admin routes and
 * anything that is not a public landing page.
 */
function track_current_request(string $path): void
{
    if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'GET') {
        return;
    }
    if (is_admin() || str_starts_with($path, 'admin')) {
        return;
    }
    if (!is_public_route($path)) {
        return;
    }
    record_page_view(
        current_visitor_id(),
        '/' . ltrim($path, '/'),
        referrer_url(),
        user_agent_string()
    );
}

function record_page_view(?string $visitorId, string $path, ?string $referrer, ?string $userAgent): void
{
    try {
        $stmt = db()->prepare('INSERT INTO page_views (visitor_id, path, referrer, user_agent) VALUES (?, ?, ?, ?)');
        $stmt->execute([
            $visitorId,
            substr($path, 0, 255),
            $referrer !== null ? substr($referrer, 0, ANALYTICS_REFERRER_MAX) : null,
            $userAgent !== null ? substr($userAgent, 0, ANALYTICS_USER_AGENT_MAX) : null,
        ]);
    } catch (Throwable $error) {
        error_log('page_views insert failed: ' . $error->getMessage());
    }
}

function referrer_url(): ?string
{
    $referrer = trim((string) ($_SERVER['HTTP_REFERER'] ?? ''));
    return $referrer === '' ? null : $referrer;
}

function user_agent_string(): ?string
{
    $agent = trim((string) ($_SERVER['HTTP_USER_AGENT'] ?? ''));
    return $agent === '' ? null : $agent;
}

function analytics_totals(): array
{
    return safe_query(function (): array {
        $row = db()->query('SELECT COUNT(*) AS views, COUNT(DISTINCT visitor_id) AS visitors, SUM(viewed_at >= CURDATE()) AS today FROM page_views')->fetch() ?: [];
        return [
            'views' => (int) ($row['views'] ?? 0),
            'visitors' => (int) ($row['visitors'] ?? 0),
            'today' => (int) ($row['today'] ?? 0),
        ];
    }, ['views' => 0, 'visitors' => 0, 'today' => 0]);
}

function top_landing_pages(int $limit = 25): array
{
    return safe_query(function () use ($limit): array {
        $stmt = db()->prepare('SELECT path, COUNT(*) AS views, COUNT(DISTINCT visitor_id) AS visitors, MAX(viewed_at) AS last_viewed FROM page_views GROUP BY path ORDER BY views DESC, path ASC LIMIT ?');
        $stmt->bindValue(1, $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }, []);
}

/**
 * Entry pages: the first page each unique visitor landed on.
 */
function top_entry_pages(int $limit = 15): array
{
    return safe_query(function () use ($limit): array {
        $stmt = db()->prepare('SELECT v.path, COUNT(*) AS entries FROM page_views v JOIN (SELECT visitor_id, MIN(id) AS first_id FROM page_views WHERE visitor_id IS NOT NULL GROUP BY visitor_id) f ON f.first_id = v.id GROUP BY v.path ORDER BY entries DESC, v.path ASC LIMIT ?');
        $stmt->bindValue(1, $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }, []);
}

function top_traffic_sources(int $limit = 12): array
{
    return safe_query(function () use ($limit): array {
        $sql = "SELECT CASE WHEN referrer IS NULL OR referrer = '' THEN '(direct)' ELSE SUBSTRING_INDEX(SUBSTRING_INDEX(referrer, '://', -1), '/', 1) END AS source, COUNT(*) AS views FROM page_views GROUP BY source ORDER BY views DESC, source ASC LIMIT ?";
        $stmt = db()->prepare($sql);
        $stmt->bindValue(1, $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }, []);
}

/**
 * Daily view totals for the last N days, oldest first, gaps filled with zero.
 */
function daily_views(int $days = 14): array
{
    $days = max(1, $days);
    $rows = safe_query(function () use ($days): array {
        $stmt = db()->prepare('SELECT DATE(viewed_at) AS day, COUNT(*) AS views FROM page_views WHERE viewed_at >= DATE_SUB(CURDATE(), INTERVAL ? DAY) GROUP BY DATE(viewed_at)');
        $stmt->bindValue(1, $days - 1, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }, []);

    $counts = [];
    foreach ($rows as $row) {
        $counts[(string) $row['day']] = (int) $row['views'];
    }

    $series = [];
    for ($offset = $days - 1; $offset >= 0; $offset--) {
        $date = date('Y-m-d', strtotime("-{$offset} days"));
        $series[] = ['day' => $date, 'views' => $counts[$date] ?? 0];
    }
    return $series;
}

function recent_page_views(int $limit = 25): array
{
    return safe_query(function () use ($limit): array {
        $stmt = db()->prepare('SELECT path, referrer, viewed_at FROM page_views ORDER BY id DESC LIMIT ?');
        $stmt->bindValue(1, $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }, []);
}

/**
 * Turns a stored path into a friendly label for the admin report.
 */
function landing_page_label(string $path): string
{
    $path = '/' . ltrim($path, '/');
    return $path === '/' ? 'Home (/)' : $path;
}

function traffic_source_label(?string $referrer): string
{
    $referrer = trim((string) $referrer);
    if ($referrer === '') {
        return 'direct';
    }
    $host = parse_url($referrer, PHP_URL_HOST) ?: $referrer;
    return strtolower((string) $host);
}
