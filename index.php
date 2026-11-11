<?php
require __DIR__ . '/inc/db.php';
$title = 'Home';
$stats = query("SELECT
    (SELECT COUNT(*) FROM lga WHERE state_id = ?) AS lgas,
    (SELECT COUNT(*) FROM ward) AS wards,
    (SELECT COUNT(*) FROM polling_unit) AS units,
    (SELECT COUNT(DISTINCT polling_unit_uniqueid) FROM announced_pu_results) AS reported", [config('state_id')])[0];
require __DIR__ . '/inc/header.php';
?>
<section class="hero">
  <h1>2011 Election Results — Delta State</h1>
  <p>Browse results announced at each polling unit, add up polling-unit results for any local government, and record results for a new polling unit.</p>
</section>
<section class="stats">
  <div><b><?= number_format($stats['lgas']) ?></b><span>Local governments</span></div>
  <div><b><?= number_format($stats['wards']) ?></b><span>Wards</span></div>
  <div><b><?= number_format($stats['units']) ?></b><span>Polling units</span></div>
  <div><b><?= number_format($stats['reported']) ?></b><span>Units with results</span></div>
</section>
<section class="cards">
  <a class="card" href="polling_unit.php"><h2>1. Polling unit result</h2><p>Pick an LGA, ward and polling unit to see each party's score.</p></a>
  <a class="card" href="lga_results.php"><h2>2. LGA summed total</h2><p>Total of all polling-unit results in a local government, checked against the announced LGA result.</p></a>
  <a class="card" href="new_result.php"><h2>3. Add new polling unit</h2><p>Register a new polling unit and store the scores for every party.</p></a>
</section>
<?php require __DIR__ . '/inc/footer.php';
