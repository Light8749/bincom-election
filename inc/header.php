<?php $page = basename($_SERVER['SCRIPT_NAME']); ?>
<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title><?= e($title ?? 'Election Results') ?> · Delta State 2011</title>
<link rel="stylesheet" href="assets/style.css">
</head>
<body>
<header class="topbar">
  <div class="wrap">
    <a class="brand" href="index.php">Delta State <span>Election Results 2011</span></a>
    <nav>
      <a href="polling_unit.php" class="<?= $page === 'polling_unit.php' ? 'active' : '' ?>">Polling unit</a>
      <a href="lga_results.php" class="<?= $page === 'lga_results.php' ? 'active' : '' ?>">LGA totals</a>
      <a href="new_result.php" class="<?= $page === 'new_result.php' ? 'active' : '' ?>">Add results</a>
    </nav>
  </div>
</header>
<main class="wrap">
