// assets/main.js
function $(sel){ return document.querySelector(sel); }
function $all(sel){ return Array.from(document.querySelectorAll(sel)); }

async function postForm(url, data){
  const form = new URLSearchParams();
  Object.entries(data).forEach(([k,v]) => form.append(k, v));
  const res = await fetch(url, {
    method: "POST",
    headers: { "Content-Type": "application/x-www-form-urlencoded" },
    body: form.toString()
  });
  return res.json().catch(() => ({ ok:false, error:"Bad JSON" }));
}

function closeDropdown(el){
  if (!el) return;
  el.classList.remove("open");
}

function toggleDropdown(el){
  if (!el) return;
  el.classList.toggle("open");
}

function clickOutsideClose(trigger, menu){
  document.addEventListener("click", (e) => {
    const t = e.target;
    if (menu.classList.contains("open")) {
      const inside = menu.contains(t) || trigger.contains(t);
      if (!inside) closeDropdown(menu);
    }
  });
}

/* ======================
   Logged-in navbar UI
   ====================== */
document.addEventListener("DOMContentLoaded", () => {
  const notifBtn = $("#notifBtn");
  const notifMenu = $("#notifMenu");
  const userMenuBtn = $("#userMenuBtn");
  const userMenu = $("#userMenu");

  if (notifBtn && notifMenu) {
    notifBtn.addEventListener("click", (e) => {
      e.preventDefault();
      // close other menu if open
      if (userMenu) closeDropdown(userMenu);
      toggleDropdown(notifMenu);
      notifBtn.setAttribute("aria-expanded", notifMenu.classList.contains("open") ? "true" : "false");
    });
    clickOutsideClose(notifBtn, notifMenu);

    // Mark one read when clicked
    $all(".notif[data-notif-id]").forEach((a) => {
      a.addEventListener("click", async (e) => {
        const id = a.getAttribute("data-notif-id");
        const hasLink = a.getAttribute("data-has-link") === "1";
        // if no link, prevent jump
        if (!hasLink) e.preventDefault();

        if (!a.classList.contains("read")) {
          await postForm("notifications_action.php", { action: "mark_one", id });
          a.classList.add("read");
          a.classList.remove("unread");
          const pill = a.querySelector(".notif__pill");
          if (pill) pill.remove();
        }
      });
    });

    const markAllBtn = $("#markAllReadBtn");
    if (markAllBtn) {
      markAllBtn.addEventListener("click", async () => {
        await postForm("notifications_action.php", { action: "mark_all" });
        $all(".notif.unread").forEach((n) => {
          n.classList.add("read");
          n.classList.remove("unread");
          const pill = n.querySelector(".notif__pill");
          if (pill) pill.remove();
        });
        const dot = notifBtn.querySelector(".dot");
        if (dot) dot.remove();
      });
    }
  }

  if (userMenuBtn && userMenu) {
    userMenuBtn.addEventListener("click", (e) => {
      e.preventDefault();
      if (notifMenu) closeDropdown(notifMenu);
      toggleDropdown(userMenu);
      userMenuBtn.setAttribute("aria-expanded", userMenu.classList.contains("open") ? "true" : "false");
    });
    clickOutsideClose(userMenuBtn, userMenu);
  }

  /* ======================
     Register password meter (index.php)
     ====================== */
  const form = $("#regForm");
  const pw1  = $("#reg_password");
  const pw2  = $("#reg_password2");
  const bar  = $("#pwBar");
  const req  = $("#pwReq");
  const matchText = $("#pwMatch");
  const btn  = $("#regBtn");

  function rulesFor(pw){
    return {
      len: (pw.length >= 16),
      low: /[a-z]/.test(pw),
      up:  /[A-Z]/.test(pw),
      num: /[0-9]/.test(pw),
      sym: /[^A-Za-z0-9]/.test(pw),
    };
  }
  function score(r){
    let s = 0;
    for (const k in r) if (r[k]) s++;
    return s; // 0..5
  }
  function setReq(reqList, key, ok){
    if (!reqList) return;
    const li = reqList.querySelector(`[data-req="${key}"]`);
    if (!li) return;
    li.classList.toggle("ok", ok);
    li.classList.toggle("bad", !ok);
  }

  if (form && pw1 && pw2 && bar && req && matchText && btn) {
    function sync(){
      const p = pw1.value || "";
      const r = rulesFor(p);
      setReq(req, "len", r.len);
      setReq(req, "low", r.low);
      setReq(req, "up",  r.up);
      setReq(req, "num", r.num);
      setReq(req, "sym", r.sym);

      const s = score(r);
      bar.style.width = ((s / 5) * 100) + "%";

      const hasConfirm = (pw2.value || "") !== "";
      const match = (pw2.value === p) && p.length > 0;

      if (!hasConfirm) {
        matchText.textContent = "";
      } else {
        matchText.textContent = match ? "Passwords match." : "Passwords do not match.";
        matchText.style.color = match ? "rgba(61,220,151,.95)" : "rgba(255,107,107,.92)";
      }

      btn.disabled = !((s === 5) && match);
    }

    pw1.addEventListener("input", sync);
    pw2.addEventListener("input", sync);
    form.addEventListener("input", sync);
    sync();
  }
});