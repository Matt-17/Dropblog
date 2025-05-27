<?php
http_response_code(404);
?>
<div class="error-page">
    <h2>404 - Seite nicht gefunden</h2>
    <p>Die angeforderte Seite konnte leider nicht gefunden werden.</p>
    <p><a href="/">Zurück zur Startseite</a></p>
    
    <?php if (isset($debug)): ?>
    <div class="debug-info" style="margin-top: 20px; padding: 10px; background: #f5f5f5; border: 1px solid #ddd;">
        <h3>Debug Information:</h3>
        <pre><?php print_r($debug); ?></pre>
    </div>
    <?php endif; ?>
</div> 