<?php
// Question 1: display the result for any individual polling unit.
require __DIR__ . '/inc/db.php';
require __DIR__ . '/inc/results_table.php';
$title = 'Polling unit result';

$puId = (int) ($_GET['pu'] ?? 0);
$unit = null;
$rows = [];
if ($puId) {
    $unit = query(
        'SELECT pu.*, w.uniqueid AS ward_uid, w.ward_name, l.lga_name
           FROM polling_unit pu
           LEFT JOIN ward w ON ' . unit_in_ward() . '
           LEFT JOIN lga l ON l.lga_id = pu.lga_id
          WHERE pu.uniqueid = ?', [$puId])[0] ?? null;
    if ($unit) {
        // polling_unit_uniqueid is a VARCHAR column that stores polling_unit.uniqueid
        $rows = query(
            'SELECT party_abbreviation AS party, SUM(party_score) AS score, MAX(entered_by_user) AS entered_by, MAX(date_entered) AS date_entered
               FROM announced_pu_results
              WHERE polling_unit_uniqueid = ?
              GROUP BY party_abbreviation
              ORDER BY score DESC', [(string) $puId]);
    }
}
require __DIR__ . '/inc/header.php';
?>
<h1>Polling unit result</h1>
<p class="lead">Choose a local government, then a ward, then a polling unit. Only areas with registered polling units are listed.</p>

<form class="panel chain" method="get" data-chain data-with-units>
  <label>Local government
    <select name="lga" data-lga required>
      <option value="">Select LGA…</option>
      <?php foreach (lgas(true) as $l): ?>
        <option value="<?= e($l['lga_id']) ?>" <?= $unit && $unit['lga_id'] == $l['lga_id'] ? 'selected' : '' ?>><?= e($l['lga_name']) ?></option>
      <?php endforeach ?>
    </select>
  </label>
  <label>Ward
    <select name="ward" data-ward data-selected="<?= e($unit['ward_uid'] ?? '') ?>" required disabled><option value="">Select ward…</option></select>
  </label>
  <label>Polling unit
    <select name="pu" data-unit data-selected="<?= e($unit['uniqueid'] ?? '') ?>" data-autosubmit required disabled><option value="">Select polling unit…</option></select>
  </label>
  <button type="submit">Show result</button>
</form>

<?php if (!empty($_GET['saved']) && $unit): ?>
  <div class="notice ok">Results saved for the new polling unit.</div>
<?php endif ?>
<?php if ($puId && !$unit): ?>
  <div class="notice warn">That polling unit was not found.</div>
<?php elseif ($unit): ?>
  <section class="panel">
    <div class="unit-head">
      <div>
        <h2><?= e($unit['polling_unit_name'] ?: 'Polling unit #' . $unit['uniqueid']) ?></h2>
        <p class="muted"><?= e($unit['polling_unit_number']) ?> · <?= e($unit['ward_name'] ?? 'Ward ' . $unit['ward_id']) ?> ward · <?= e($unit['lga_name'] ?? '') ?> LGA</p>
        <?php if (!empty($unit['polling_unit_description'])): ?><p class="muted"><?= e($unit['polling_unit_description']) ?></p><?php endif ?>
      </div>
    </div>
    <?php if ($rows): ?>
      <?php results_table($rows); ?>
      <p class="muted small">Entered by <?= e($rows[0]['entered_by']) ?> on <?= e($rows[0]['date_entered']) ?></p>
    <?php else: ?>
      <div class="notice">No results have been announced for this polling unit yet. <a href="new_result.php">Add results</a> for a new unit, or pick another one — units with results are marked ✓.</div>
    <?php endif ?>
  </section>
<?php endif ?>
<?php require __DIR__ . '/inc/footer.php';
