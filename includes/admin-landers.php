<?php
declare(strict_types=1);

/**
 * Admin report: landing-page traffic. Rendered inside the standard page shell
 * by the /admin/landers route in index.php.
 */
function render_landers_report(): void
{
    $totals = analytics_totals();
    $daily = daily_views(14);
    $pages = top_landing_pages(25);
    $entries = top_entry_pages(12);
    $sources = top_traffic_sources(10);
    $recent = recent_page_views(20);
    $peak = 0;
    foreach ($daily as $point) {
        $peak = max($peak, (int) $point['views']);
    }
    ?>
    <section class="section"><div class="container">
      <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
        <div><p class="eyebrow">Traffic</p><h1 class="mb-0">Landing pages</h1></div>
        <div class="d-flex gap-2"><a class="btn btn-outline-dark sans" href="<?= e(url('admin')) ?>">Dashboard</a><a class="btn btn-outline-secondary sans" href="<?= e(url('admin/landers')) ?>">Refresh</a></div>
      </div>

      <?php if ((int) $totals['views'] === 0): ?>
        <div class="alert alert-secondary sans">No page views recorded yet. As visitors browse the public site, landings will appear here. If you just deployed, make sure the <code>page_views</code> table from <code>migrations/2026_01_page_views.sql</code> has been imported.</div>
      <?php endif; ?>

      <div class="row g-4 mb-4">
        <?php foreach ([['Total page views', $totals['views']], ['Unique visitors', $totals['visitors']], ['Views today', $totals['today']], ['Tracked pages', count($pages)]] as $card): ?>
          <div class="col-6 col-lg-3"><div class="card p-4 h-100"><p class="eyebrow mb-1"><?= e($card[0]) ?></p><strong class="display-5"><?= e((string) $card[1]) ?></strong></div></div>
        <?php endforeach; ?>
      </div>

      <div class="card p-4 mb-4">
        <div class="d-flex justify-content-between align-items-baseline flex-wrap gap-2"><h2 class="h5 mb-0">Views &mdash; last 14 days</h2><span class="sans text-muted small">Peak: <?= e((string) $peak) ?> / day</span></div>
        <div class="d-flex align-items-end gap-1 mt-4" style="height:140px" role="img" aria-label="Daily page views for the last 14 days">
          <?php foreach ($daily as $point): $height = $peak > 0 ? max(4, (int) round(((int) $point['views'] / $peak) * 120)) : 4; ?>
            <div class="flex-fill d-flex flex-column justify-content-end align-items-center" style="height:140px" title="<?= e($point['day']) ?>: <?= e((string) $point['views']) ?> views">
              <div class="w-100 rounded-top bg-success" style="height:<?= $height ?>px"></div>
              <span class="sans" style="font-size:10px;color:#6c757d;white-space:nowrap"><?= e(date('j M', strtotime((string) $point['day']))) ?></span>
            </div>
          <?php endforeach; ?>
        </div>
      </div>

      <div class="row g-4 mb-4">
        <div class="col-lg-6">
          <div class="card p-4 h-100"><h2 class="h5">Views by landing page</h2>
            <div class="table-responsive"><table class="table table-sm sans align-middle mb-0"><thead><tr><th>Page</th><th class="text-end">Views</th><th class="text-end">Visitors</th><th>Last seen</th></tr></thead><tbody>
            <?php foreach ($pages as $page): ?>
              <tr><td><a href="<?= e(url('admin/landers?path=' . urlencode((string) $page['path']))) ?>"><?= e(landing_page_label((string) $page['path'])) ?></a></td><td class="text-end"><?= e((string) $page['views']) ?></td><td class="text-end"><?= e((string) $page['visitors']) ?></td><td class="small text-muted"><?= e((string) $page['last_viewed']) ?></td></tr>
            <?php endforeach; ?>
            <?php if (!$pages): ?><tr><td colspan="4" class="text-muted">Nothing recorded yet.</td></tr><?php endif; ?>
            </tbody></table></div>
          </div>
        </div>
        <div class="col-lg-6">
          <div class="card p-4 h-100"><h2 class="h5">Entry pages <span class="text-muted small">(first page of a visit)</span></h2>
            <div class="table-responsive"><table class="table table-sm sans align-middle mb-0"><thead><tr><th>Landing page</th><th class="text-end">Entries</th></tr></thead><tbody>
            <?php foreach ($entries as $entry): ?>
              <tr><td><?= e(landing_page_label((string) $entry['path'])) ?></td><td class="text-end"><?= e((string) $entry['entries']) ?></td></tr>
            <?php endforeach; ?>
            <?php if (!$entries): ?><tr><td colspan="2" class="text-muted">Nothing recorded yet.</td></tr><?php endif; ?>
            </tbody></table></div>
          </div>
        </div>
      </div>

      <div class="row g-4">
        <div class="col-lg-5">
          <div class="card p-4 h-100"><h2 class="h5">Traffic sources</h2>
            <div class="table-responsive"><table class="table table-sm sans align-middle mb-0"><thead><tr><th>Source</th><th class="text-end">Views</th></tr></thead><tbody>
            <?php foreach ($sources as $source): ?>
              <tr><td><?= e((string) $source['source']) ?></td><td class="text-end"><?= e((string) $source['views']) ?></td></tr>
            <?php endforeach; ?>
            <?php if (!$sources): ?><tr><td colspan="2" class="text-muted">Nothing recorded yet.</td></tr><?php endif; ?>
            </tbody></table></div>
          </div>
        </div>
        <div class="col-lg-7">
          <div class="card p-4 h-100"><h2 class="h5">Recent views</h2>
            <div class="table-responsive"><table class="table table-sm sans align-middle mb-0"><thead><tr><th>Page</th><th>Source</th><th>When</th></tr></thead><tbody>
            <?php foreach ($recent as $view): ?>
              <tr><td><?= e(landing_page_label((string) $view['path'])) ?></td><td class="small text-muted"><?= e(traffic_source_label($view['referrer'] ?? null)) ?></td><td class="small text-muted"><?= e((string) $view['viewed_at']) ?></td></tr>
            <?php endforeach; ?>
            <?php if (!$recent): ?><tr><td colspan="3" class="text-muted">Nothing recorded yet.</td></tr><?php endif; ?>
            </tbody></table></div>
          </div>
        </div>
      </div>
    </div></section>
    <?php
}
