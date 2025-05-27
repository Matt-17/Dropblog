<?php
// $pageTitle kann der Controller setzen, ansonsten Default
$pageTitle = $pageTitle ?? '404 – Seite nicht gefunden';
?>
<div class="no-posts">
  <h2><?= htmlspecialchars($pageTitle) ?></h2>
  <p>Die angeforderte Seite konnte leider nicht gefunden werden.</p>
  <p><a href="/">Zurück zur Startseite</a></p>
</div>
