<?php
// admin/game-content.php
session_start();

require_once __DIR__ . "/../includes/db.php";
require_once __DIR__ . "/../includes/helpers.php";
require_once __DIR__ . "/../includes/auth.php";
require_once __DIR__ . "/../includes/admin_ui.php";

$bp = base_path();

admin_ui_header("Content");
?>
<div class="grid">
  <section class="card">
    <h2>Game content</h2>
    <p class="sub">Planned module for card updates, balance patches, and rule changes.</p>

    <div class="alert">
      <b>Card changes (planned)</b>
      <div style="color:var(--muted); margin-top:6px; line-height:1.55;">
        • Create/update cards and metadata<br/>
        • Submit patch proposals for review<br/>
        • Publish balance notes<br/>
        • Every change writes to <span class="kbd">audit_logs</span>
      </div>
    </div>

    <div class="btns">
      <span class="btn" style="opacity:.65; cursor:not-allowed;">New patch (soon)</span>
      <span class="btn" style="opacity:.65; cursor:not-allowed;">Approve patch (soon)</span>
    </div>
  </section>

  <section class="card">
    <h2>Compliance & traceability</h2>
    <p class="sub">Why this exists (security + governance).</p>
    <div class="alert">
      <div style="color:var(--muted); line-height:1.55;">
        • RBAC restricts patch rights to admins<br/>
        • Audit trails capture actor + metadata for each change<br/>
        • Future: rollback points and patch signing
      </div>
    </div>
  </section>
</div>
<?php admin_ui_footer(); ?>