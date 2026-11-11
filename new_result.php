<?php
// Question 3: store results for ALL parties for a new polling unit.
require __DIR__ . '/inc/db.php';
$title = 'Add polling unit results';

$parties = parties();
$errors = [];
$old = $_POST;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $lgaId  = (int) ($_POST['lga'] ?? 0);
    $wardId = (int) ($_POST['ward'] ?? 0);
    $name   = trim($_POST['name'] ?? '');
    $number = trim($_POST['number'] ?? '');
    $desc   = trim($_POST['description'] ?? '');
    $agent  = trim($_POST['entered_by'] ?? '');
    $scores = $_POST['score'] ?? [];

    // $wardId is ward.uniqueid (ward_id alone is not unique, and is 0 for some wards)
    $ward = query('SELECT uniqueid, ward_id FROM ward WHERE lga_id = ? AND uniqueid = ?', [$lgaId, $wardId])[0] ?? null;
    if (!$ward) $errors['ward'] = 'Choose a local government and ward.';
    if ($name === '') $errors['name'] = 'Enter the polling unit name.';
    if (mb_strlen($name) > 50) $errors['name'] = 'Name must be 50 characters or fewer.';
    if (mb_strlen($number) > 50) $errors['number'] = 'Number must be 50 characters or fewer.';
    if ($agent === '') $errors['entered_by'] = 'Enter who is recording these results.';
    if ($number !== '' && query('SELECT 1 FROM polling_unit WHERE polling_unit_number = ?', [$number])) {
        $errors['number'] = 'A polling unit with this number already exists.';
    }
    foreach ($parties as $p) {
        $v = $scores[$p['partyid']] ?? '';
        if ($v === '' || !ctype_digit((string) $v)) $errors['score'][$p['partyid']] = 'Whole number, 0 or more.';
    }

    if (!$errors) {
        $pdo = db();
        $now = date('Y-m-d H:i:s');
        $ip  = $_SERVER['REMOTE_ADDR'] ?? '';
        $pdo->beginTransaction();
        try {
            $nextId = (int) query('SELECT COALESCE(MAX(polling_unit_id), 0) + 1 AS n FROM polling_unit WHERE uniquewardid = ?', [$ward['uniqueid']])[0]['n'];
            $pdo->prepare('INSERT INTO polling_unit
                (polling_unit_id, ward_id, lga_id, uniquewardid, polling_unit_number, polling_unit_name, polling_unit_description, entered_by_user, date_entered, user_ip_address)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)')
                ->execute([$nextId, $ward['ward_id'], $lgaId, $ward['uniqueid'], $number ?: null, $name, $desc ?: null, $agent, $now, $ip]);
            $puId = (int) $pdo->lastInsertId();

            $ins = $pdo->prepare('INSERT INTO announced_pu_results
                (polling_unit_uniqueid, party_abbreviation, party_score, entered_by_user, date_entered, user_ip_address)
                VALUES (?, ?, ?, ?, ?, ?)');
            foreach ($parties as $p) {
                $ins->execute([(string) $puId, party_abbr($p['partyid']), (int) $scores[$p['partyid']], $agent, $now, $ip]);
            }
            $pdo->commit();
            header('Location: polling_unit.php?pu=' . $puId . '&saved=1');
            exit;
        } catch (Throwable $ex) {
            $pdo->rollBack();
            $errors['form'] = 'Could not save the results. Please try again.';
        }
    }
}

function err(array $errors, string $k): string {
    return isset($errors[$k]) ? '<span class="err">' . e($errors[$k]) . '</span>' : '';
}
require __DIR__ . '/inc/header.php';
?>
<h1>Add results for a new polling unit</h1>
<p class="lead">Register the polling unit, then enter the score for every party.</p>

<?php if (!empty($errors['form'])): ?><div class="notice warn"><?= e($errors['form']) ?></div><?php endif ?>
<?php if ($errors && empty($errors['form'])): ?><div class="notice warn">Please fix the highlighted fields.</div><?php endif ?>

<form method="post" class="stack" data-chain data-total>
  <section class="panel">
    <h2>Polling unit</h2>
    <div class="grid">
      <label>Local government
        <select name="lga" data-lga required>
          <option value="">Select LGA…</option>
          <?php foreach (lgas() as $l): ?>
            <option value="<?= e($l['lga_id']) ?>" <?= ($old['lga'] ?? '') == $l['lga_id'] ? 'selected' : '' ?>><?= e($l['lga_name']) ?></option>
          <?php endforeach ?>
        </select>
      </label>
      <label>Ward
        <select name="ward" data-ward data-selected="<?= e($old['ward'] ?? '') ?>" required disabled><option value="">Select ward…</option></select>
        <?= err($errors, 'ward') ?>
      </label>
      <label>Polling unit name
        <input name="name" maxlength="50" value="<?= e($old['name'] ?? '') ?>" placeholder="e.g. Primary School, Abraka" required>
        <?= err($errors, 'name') ?>
      </label>
      <label>Polling unit number <small>(optional)</small>
        <input name="number" maxlength="50" value="<?= e($old['number'] ?? '') ?>" placeholder="e.g. DT0702020">
        <?= err($errors, 'number') ?>
      </label>
      <label class="wide">Description <small>(optional)</small>
        <input name="description" value="<?= e($old['description'] ?? '') ?>" placeholder="Location details">
      </label>
      <label>Entered by
        <input name="entered_by" maxlength="50" value="<?= e($old['entered_by'] ?? '') ?>" placeholder="Agent name" required>
        <?= err($errors, 'entered_by') ?>
      </label>
    </div>
  </section>

  <section class="panel">
    <h2>Party scores</h2>
    <div class="scores">
      <?php foreach ($parties as $p): $id = $p['partyid']; ?>
        <label class="score">
          <span class="party"><?= e($p['partyname']) ?></span>
          <input type="number" name="score[<?= e($id) ?>]" min="0" step="1" inputmode="numeric" value="<?= e($old['score'][$id] ?? '') ?>" placeholder="0" required data-score>
          <?php if (isset($errors['score'][$id])): ?><span class="err"><?= e($errors['score'][$id]) ?></span><?php endif ?>
        </label>
      <?php endforeach ?>
    </div>
    <p class="total-line">Total votes: <b data-total-out>0</b></p>
  </section>

  <button type="submit" class="primary">Save results</button>
</form>
<?php require __DIR__ . '/inc/footer.php';
