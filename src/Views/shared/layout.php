<!DOCTYPE html>
<html lang="pt-br">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= htmlspecialchars($titulo ?? 'PlayZone') ?> — PlayZone</title>
  <link href="/assets/style.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/htmx.org@2.0.8/dist/htmx.min.js"
    integrity="sha384-/TgkGk7p307TH7EXJDuUlgG3Ce1UVolAOFopFekQkkXihi5u/6OCvVKyz1W+idaz"
    crossorigin="anonymous"></script>
</head>
<body style="display:flex;flex-direction:column;min-height:100vh;">

<?php require __DIR__ . '/header.php'; ?>

<div id="page-content" style="flex:1;">
  <?= $content ?? '' ?>
</div>

<?php require __DIR__ . '/footer.php'; ?>

<script src="/assets/js/tempo-relativo.js"></script>
</body>
</html>