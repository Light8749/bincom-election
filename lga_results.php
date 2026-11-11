<?php
// Question 2: summed total of all polling-unit results under an LGA.
// Computed from announced_pu_results only; announced_lga_results is used just for the comparison column.
require __DIR__ . '/inc/db.php';
require __DIR__ . '/inc/results_table.php';
$title = 'LGA summed results';

$lgaId = (int) ($_GET['lga'] ?? 0);
$lgas = lgas();
$lga = null;
foreach ($lgas as $l) if ($l['lga_id'] == $lgaId) $lga = $l;

$rows = [];
$compare = null;
$unitCount = 0;
if ($lga) {
    $rows = query(
        'SELECT r.party_abbreviation AS party, SUM(r.party_score) AS score
           FROM announced_pu_results r
           JOIN polling_unit pu ON CAST(pu.uniqueid AS CHAR) = r.polling_unit_uniqueid
          WHERE pu.lga_id = ?
          GROUP BY r.party_abbreviation
          ORDER BY score DESC', [$lgaId]);
    $unitCount = (int) query(
        'SELECT COUNT(DISTINCT r.polling_unit_uniqueid) AS n
           FROM announced_pu_results r
           JOIN polling_unit pu ON CAST(pu.uniqueid AS CHAR) = r.polling_unit_uniqueid
          WHERE pu.lga_id = ?', [$lgaId])[0]['n'];
    try {
        // announced_lga_results.lga_name holds the LGA id in the supplied data; accept the name too.
        $c = query('SELECT party_abbreviation AS party, SUM(party_score) AS score FROM announced_lga_results
                     WHERE lga_name = ? OR lga_name = ? GROUP BY party_abbreviation', [(string) $lgaId, $lga['lga_name']]);
        if ($c) $compare = array_column($c, 'score', 'party');
    } catch (Throwable $ex) {
        $compare = null; // comparison is optional
    }
}
require __DIR__ . '/inc/header.php';
?>
<h1>LGA summed results</h1>
<p class="lead">Adds up every polling-unit result in the selected local government.</p>

<form class="panel chain" method="get">
  <label>Local government
    <select name="lga" onchange="this.form.submit()" required>
      <option value="">Select LGA…</option>
      <?php foreach ($lgas as $l): ?>
        <option value="<?= e($l['lga_id']) ?>" <?= $lgaId == $l['lga_id'] ? 'selected' : '' ?>><?= e($l['lga_name']) ?></option>
      <?php endforeach ?>
    </select>
  </label>
  <noscript><button type="submit">Show total</button></noscript>
</form>

<?php if ($lga): ?>
  <section class="panel">
    <h2><?= e($lga['lga_name']) ?> LGA</h2>
    <p class="muted">Sum of <?= number_format($unitCount) ?> polling unit<?= $unitCount == 1 ? '' : 's' ?> with announced results.</p>
    <?php if ($rows): ?>
      <?php results_table($rows, $compare, 'Announced LGA'); ?>
      <?php if ($compare !== null): ?><p class="muted small">“Announced LGA” is the figure in announced_lga_results, shown only for cross-checking.</p><?php endif ?>
    <?php else: ?>
      <div class="notice">No polling units in this LGA have announced results.</div>
    <?php endif ?>
  </section>
<?php endif ?>
<?php require __DIR__ . '/inc/footer.php';
