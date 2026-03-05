// assets/main.js
(function () {
  "use strict";

  const $ = (sel, root = document) => root.querySelector(sel);
  const $$ = (sel, root = document) => Array.from(root.querySelectorAll(sel));

  // -------------------------
  // Modal open/close + tabbing
  // -------------------------
  const modal = $("#authModal");
  const openBtns = $$("[data-open-auth]");
  const closeBtns = $$("[data-close-auth]");
  const tabs = $$("[data-tab]");
  const panes = $$("[data-pane]");

  function setTab(which) {
    tabs.forEach((t) => t.classList.toggle("is-active", t.dataset.tab === which));
    panes.forEach((p) => p.classList.toggle("is-active", p.dataset.pane === which));
  }

  function openModal(which = "login") {
    if (!modal) return;
    modal.classList.add("is-open");
    modal.setAttribute("aria-hidden", "false");
    document.body.style.overflow = "hidden";
    setTab(which);

    // focus first field
    const focusSel = which === "register" ? "#reg_user" : "#login_ident";
    const el = $(focusSel);
    if (el) setTimeout(() => el.focus(), 50);
  }

  function closeModal() {
    if (!modal) return;
    modal.classList.remove("is-open");
    modal.setAttribute("aria-hidden", "true");
    document.body.style.overflow = "";
  }

  openBtns.forEach((b) =>
    b.addEventListener("click", () => openModal(b.dataset.openAuth))
  );
  closeBtns.forEach((b) => b.addEventListener("click", closeModal));

  tabs.forEach((t) =>
    t.addEventListener("click", () => setTab(t.dataset.tab))
  );

  // ESC closes
  document.addEventListener("keydown", (e) => {
    if (e.key === "Escape") closeModal();
  });

  // -------------------------
  // FAQ accordion
  // -------------------------
  $$("[data-acc]").forEach((btn) => {
    btn.addEventListener("click", () => {
      const isOpen = btn.classList.contains("is-open");
      // close others for a clean look
      $$("[data-acc].is-open").forEach((b) => b.classList.remove("is-open"));
      if (!isOpen) btn.classList.add("is-open");
    });
  });

  // -------------------------
  // Password strength rules (your existing UI, but stable)
  // -------------------------
  const pw = $("#reg_password");
  const pw2 = $("#reg_password2");
  const pwBar = $("#pwBar");
  const pwReq = $("#pwReq");
  const match = $("#pwMatch");
  const regBtn = $("#regBtn");

  if (pw && pw2 && pwReq && pwBar && match && regBtn) {
    const reqItem = (key) => pwReq.querySelector(`[data-req="${key}"]`);

    function hasLower(s) { return /[a-z]/.test(s); }
    function hasUpper(s) { return /[A-Z]/.test(s); }
    function hasNum(s) { return /[0-9]/.test(s); }
    function hasSym(s) { return /[^A-Za-z0-9]/.test(s); }

    function setReq(key, ok) {
      const li = reqItem(key);
      if (!li) return;
      li.classList.toggle("ok", ok);
      li.classList.toggle("bad", !ok);
    }

    function update() {
      const v = pw.value || "";
      const v2 = pw2.value || "";

      const okLen = v.length >= 16;
      const okLow = hasLower(v);
      const okUp  = hasUpper(v);
      const okNum = hasNum(v);
      const okSym = hasSym(v);

      setReq("len", okLen);
      setReq("low", okLow);
      setReq("up", okUp);
      setReq("num", okNum);
      setReq("sym", okSym);

      const checks = [okLen, okLow, okUp, okNum, okSym].filter(Boolean).length;
      const pct = Math.min(100, Math.round((checks / 5) * 100));
      pwBar.style.width = pct + "%";

      const same = v.length > 0 && v === v2;
      match.textContent = v2.length ? (same ? "Passwords match ✓" : "Passwords do not match") : "";
      match.style.color = same ? "rgba(62,229,139,.95)" : "rgba(255,107,107,.92)";

      const allOk = okLen && okLow && okUp && okNum && okSym && same;
      regBtn.disabled = !allOk;
    }

    pw.addEventListener("input", update);
    pw2.addEventListener("input", update);
  }
})();