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

  const bio = document.querySelector('textarea[name="bio"]');
  const counter = document.getElementById('bioCount');

if (bio && counter) {
  const update = () => counter.textContent = bio.value.length + " / 280";
  bio.addEventListener("input", update);
  update();
}

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

  const initialAuthOpen = document.body?.dataset?.authOpen || "";
  if (initialAuthOpen) {
    setTimeout(() => openModal(initialAuthOpen), 80);
  }

  openBtns.forEach((b) =>
    b.addEventListener("click", () => openModal(b.dataset.openAuth))
  );
  closeBtns.forEach((b) => b.addEventListener("click", closeModal));

  tabs.forEach((t) =>
    t.addEventListener("click", () => setTab(t.dataset.tab))
  );

    $$("[data-toggle-password]").forEach((btn) => {
    btn.addEventListener("click", () => {
      const target = btn.getAttribute("data-toggle-password");
      const input = target ? document.querySelector(target) : null;

      if (!input) return;

      const isPassword = input.type === "password";
      input.type = isPassword ? "text" : "password";
      btn.textContent = isPassword ? "Hide" : "Show";
      btn.setAttribute("aria-label", isPassword ? "Hide password" : "Show password");
    });
  });

  // ESC closes
  document.addEventListener("keydown", (e) => {
    if (e.key === "Escape") {
      closeModal();
      closeGuide();
      closeComingSoon();
    }
  });

    // -------------------------
  // Global guide modal
  // -------------------------
  const guideModal = $("#globalGuideModal");
  const guideOpenBtns = $$("[data-guide-open]");
  const guideCloseBtns = $$("[data-guide-close]");
  const guideTabs = $$("[data-guide-tab]");
  const guidePanes = $$("[data-guide-pane]");

  function setGuideTab(which = "getting-started") {
    const target = which || "getting-started";

    guideTabs.forEach((tab) => {
      tab.classList.toggle("is-active", tab.dataset.guideTab === target);
    });

    guidePanes.forEach((pane) => {
      pane.classList.toggle("is-active", pane.dataset.guidePane === target);
    });
  }

  function openGuide(which = "getting-started") {
    if (!guideModal) return;

    setGuideTab(which);
    guideModal.classList.add("is-open");
    guideModal.setAttribute("aria-hidden", "false");
    document.body.style.overflow = "hidden";
  }

  function closeGuide() {
    if (!guideModal) return;

    guideModal.classList.remove("is-open");
    guideModal.setAttribute("aria-hidden", "true");
    document.body.style.overflow = "";
  }

  guideOpenBtns.forEach((btn) => {
    btn.addEventListener("click", () => {
      openGuide(btn.dataset.guideOpen || "getting-started");
    });
  });

  guideCloseBtns.forEach((btn) => {
    btn.addEventListener("click", closeGuide);
  });

  guideTabs.forEach((tab) => {
    tab.addEventListener("click", () => {
      setGuideTab(tab.dataset.guideTab || "getting-started");
    });
  });

  if (guideModal) {
    guideModal.addEventListener("click", (e) => {
      if (e.target === guideModal) closeGuide();
    });
  }

    // -------------------------
  // Coming soon modal
  // -------------------------
  const comingSoonModal = $("#comingSoonModal");
  const comingSoonTitle = $("#comingSoonTitle");
  const comingSoonBtns = $$("[data-coming-soon]");
  const comingSoonCloseBtns = $$("[data-coming-soon-close]");

  function openComingSoon(title = "Feature Coming Soon") {
    if (!comingSoonModal) return;

    if (comingSoonTitle) {
      comingSoonTitle.textContent = title;
    }

    comingSoonModal.classList.add("is-open");
    comingSoonModal.setAttribute("aria-hidden", "false");
    document.body.style.overflow = "hidden";
  }

  function closeComingSoon() {
    if (!comingSoonModal) return;

    comingSoonModal.classList.remove("is-open");
    comingSoonModal.setAttribute("aria-hidden", "true");
    document.body.style.overflow = "";
  }

  comingSoonBtns.forEach((btn) => {
    btn.addEventListener("click", () => {
      openComingSoon(btn.dataset.comingSoon || "Feature Coming Soon");
    });
  });

  comingSoonCloseBtns.forEach((btn) => {
    btn.addEventListener("click", closeComingSoon);
  });

  if (comingSoonModal) {
    comingSoonModal.addEventListener("click", (e) => {
      if (e.target === comingSoonModal) closeComingSoon();
    });
  }

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
(function(){
  const popup = document.getElementById('chatPopup');
  const basePath = window.LOGIA_BASE_PATH || '';
  if (!popup) return;

  popup.hidden = true;
  popup.style.display = 'none';
  popup.classList.remove('is-min');

  const bodyEl = document.getElementById('chatPopupBody');
  const nameEl = document.getElementById('chatPopupName');
  const metaEl = document.getElementById('chatPopupMeta');
  const avatarImgEl = document.getElementById('chatPopupAvatarImg');
  const avatarFallbackEl = document.getElementById('chatPopupAvatarFallback');
  const formEl = document.getElementById('chatPopupForm');
  const inputEl = document.getElementById('chatPopupInput');
  const receiverEl = document.getElementById('chatPopupReceiverId');
  const conversationEl = document.getElementById('chatPopupConversationId');
  const closeBtn = document.getElementById('chatPopupCloseBtn');
  const minBtn = document.getElementById('chatPopupMinBtn');

  if (!bodyEl || !nameEl || !metaEl || !formEl || !inputEl || !receiverEl || !conversationEl || !closeBtn || !minBtn) {
    console.error('Chat popup elements missing.');
    return;
  }

  conversationEl.value = '';
  receiverEl.value = '';
  inputEl.value = '';
  nameEl.textContent = 'Chat';
  metaEl.textContent = 'Direct message';
  bodyEl.innerHTML = '';
  avatarImgEl.src = '';
  avatarImgEl.hidden = true;
  avatarFallbackEl.hidden = false;
  avatarFallbackEl.textContent = 'C';

  let activeConversationId = 0;
  let activeReceiverId = 0;
  let pollTimer = null;
  let chatClosedManually = false;

  function esc(s){
    return String(s ?? '').replace(/[&<>"']/g, m => ({
      '&':'&amp;',
      '<':'&lt;',
      '>':'&gt;',
      '"':'&quot;',
      "'":'&#039;'
    }[m]));
  }

    function initialsFromName(name){
    const text = String(name || '').trim();
    if (!text) return 'C';

    const parts = text.split(/\s+/).filter(Boolean).slice(0, 2);
    return parts.map(p => p.charAt(0).toUpperCase()).join('') || text.charAt(0).toUpperCase();
  }

  function formatChatTime(value){
    if (!value) return '';

    const normalized = String(value).replace(' ', 'T');
    const dt = new Date(normalized);

    if (Number.isNaN(dt.getTime())) {
      return String(value);
    }

    return dt.toLocaleTimeString([], {
      hour: 'numeric',
      minute: '2-digit'
    });
  }

  function setChatHeader(otherUser){
    const displayName = otherUser?.display_name || otherUser?.username || 'Chat';
    const username = otherUser?.username || 'player';
    const avatarPath = String(otherUser?.avatar_path || '').trim();

    nameEl.textContent = displayName;
    metaEl.textContent = '@' + username + ' • Direct message';

    avatarFallbackEl.textContent = initialsFromName(displayName);

    if (avatarPath) {
      const src = avatarPath.startsWith('http')
        ? avatarPath
        : (basePath + '/' + avatarPath.replace(/^\/+/, ''));

      avatarImgEl.src = src;
      avatarImgEl.hidden = false;
      avatarFallbackEl.hidden = true;
    } else {
      avatarImgEl.src = '';
      avatarImgEl.hidden = true;
      avatarFallbackEl.hidden = false;
    }
  }

  function resetComposerHeight(){
    inputEl.style.height = 'auto';
    inputEl.style.height = Math.min(inputEl.scrollHeight, 110) + 'px';
  }

  function renderMessages(messages, currentUserId){
    bodyEl.innerHTML = '';

    if (!Array.isArray(messages) || !messages.length){
      bodyEl.innerHTML = '<div class="chat-pop__empty">No messages yet. Say hi and start the conversation.</div>';
      return;
    }

    messages.forEach(msg => {
      const mine = Number(msg.sender_id) === Number(currentUserId);
      const wrap = document.createElement('div');
      wrap.className = 'chat-msg' + (mine ? ' mine' : '');

      wrap.innerHTML = `
        <div class="chat-msg__bubble">
          <div class="chat-msg__text">${esc(msg.body)}</div>
          <div class="chat-msg__time">${esc(formatChatTime(msg.created_at ?? ''))}</div>
        </div>
      `;

      bodyEl.appendChild(wrap);
    });

    bodyEl.scrollTop = bodyEl.scrollHeight;
  }

  async function post(url, data){
    const res = await fetch(url, {
      method: 'POST',
      headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
      body: new URLSearchParams(data)
    });

    let json = null;
    try {
      json = await res.json();
    } catch (err) {
      console.error('Invalid JSON response from', url, err);
      throw err;
    }

    return json;
  }

  async function loadThread(params){
    try {
      chatClosedManually = false;

      const url = new URL(basePath + '/api/messages/thread.php', window.location.origin);
      Object.entries(params).forEach(([k, v]) => url.searchParams.set(k, v));

      const res = await fetch(url);
      const data = await res.json();

      console.log('thread response', data);

      if (!data.ok) {
        alert(data.msg || 'Failed to load conversation.');
        return;
      }

      if (chatClosedManually) {
        return;
      }

      popup.hidden = false;
      popup.style.display = 'grid';
      popup.classList.remove('is-min');

      activeConversationId = Number(data.conversation_id || 0);
      activeReceiverId = Number(data.other_user?.id || 0);

      conversationEl.value = activeConversationId;
      receiverEl.value = activeReceiverId;

      setChatHeader(data.other_user || {});
      renderMessages(data.messages || [], window.LOGIA_USER_ID || 0);

      if (activeConversationId > 0) {
        await post(basePath + '/api/messages/mark_read.php', {
          conversation_id: activeConversationId
        });
      }

      startPolling();
    } catch (err) {
      console.error('loadThread failed', err);
      alert('Failed to load chat thread.');
    }
  }

  async function sendMessage(e){
    e.preventDefault();

    const body = inputEl.value.trim();

    if (!body) {
      return;
    }

    if (!activeReceiverId) {
      console.error('No activeReceiverId set.');
      alert('No chat receiver found for this conversation.');
      return;
    }

    try {
      const data = await post(basePath + '/api/messages/send.php', {
        receiver_id: activeReceiverId,
        body: body
      });

      console.log('send response', data);

      if (!data.ok) {
        alert(data.msg || 'Failed to send message.');
        return;
      }

      inputEl.value = '';
      resetComposerHeight();

      await loadThread({
        conversation_id: data.conversation_id
      });
    } catch (err) {
      console.error('sendMessage failed', err);
      alert('Message send failed.');
    }
  }

  function startPolling(){
    stopPolling();

    pollTimer = setInterval(() => {
      if (!popup.hidden && activeConversationId > 0 && !popup.classList.contains('is-min')) {
        loadThread({ conversation_id: activeConversationId });
      }
    }, 8000);
  }

  function stopPolling(){
    if (pollTimer) {
      clearInterval(pollTimer);
      pollTimer = null;
    }
  }

  document.addEventListener('click', (e) => {
    const btn = e.target.closest('[data-chat-open]');
    if (!btn) return;

    e.preventDefault();
    e.stopPropagation();

    const conversationId = btn.getAttribute('data-conversation-id');
    const userId = btn.getAttribute('data-user-id');

    const dropdown = btn.closest('.dd');
    if (dropdown) {
      dropdown.classList.remove('is-open');
    }

    chatClosedManually = false;

    if (conversationId) {
      loadThread({ conversation_id: conversationId });
      return;
    }

    if (userId) {
      loadThread({ target_user_id: userId });
    }
  }, true);

    document.addEventListener('click', async (e) => {
    const acceptBtn = e.target.closest('[data-friend-accept]');
    if (!acceptBtn) return;

    e.preventDefault();
    e.stopPropagation();

    const requestId = acceptBtn.getAttribute('data-request-id');
    if (!requestId) return;

    try {
      const data = await post(basePath + '/api/friends/accept.php', {
        request_id: requestId
      });

      console.log('friend accept response', data);

      if (!data.ok) {
        alert(data.msg || 'Failed to accept friend request.');
        return;
      }

      const card = acceptBtn.closest('[data-friend-request-card]');
      if (card) {
        card.remove();
      }

      const badge = document.querySelector('[data-dd-btn="friends"]')?.closest('.md-ico-wrap')?.querySelector('.md-badge');
      if (badge) {
        const nextCount = Math.max(0, Number(badge.textContent || '0') - 1);
        if (nextCount > 0) {
          badge.textContent = String(nextCount);
        } else {
          badge.remove();
        }
      }

      const ddBody = document.querySelector('#dd-friends .dd__body');
      if (ddBody && !ddBody.querySelector('[data-friend-request-card]')) {
        ddBody.innerHTML = '<div style="color: var(--muted); font-size:13px; padding:6px 2px;">No pending friend requests.</div>';
      }
    } catch (err) {
      console.error('friend accept failed', err);
      alert('Failed to accept friend request.');
    }
  }, true);

  document.addEventListener('click', async (e) => {
    const rejectBtn = e.target.closest('[data-friend-reject]');
    if (!rejectBtn) return;

    e.preventDefault();
    e.stopPropagation();

    const requestId = rejectBtn.getAttribute('data-request-id');
    if (!requestId) return;

    try {
      const data = await post(basePath + '/api/friends/reject.php', {
        request_id: requestId
      });

      console.log('friend reject response', data);

      if (!data.ok) {
        alert(data.msg || 'Failed to reject friend request.');
        return;
      }

      const card = rejectBtn.closest('[data-friend-request-card]');
      if (card) {
        card.remove();
      }

      const badge = document.querySelector('[data-dd-btn="friends"]')?.closest('.md-ico-wrap')?.querySelector('.md-badge');
      if (badge) {
        const nextCount = Math.max(0, Number(badge.textContent || '0') - 1);
        if (nextCount > 0) {
          badge.textContent = String(nextCount);
        } else {
          badge.remove();
        }
      }

      const ddBody = document.querySelector('#dd-friends .dd__body');
      if (ddBody && !ddBody.querySelector('[data-friend-request-card]')) {
        ddBody.innerHTML = '<div style="color: var(--muted); font-size:13px; padding:6px 2px;">No pending friend requests.</div>';
      }
    } catch (err) {
      console.error('friend reject failed', err);
      alert('Failed to reject friend request.');
    }
  }, true);
  
    document.addEventListener('click', async (e) => {
    const readBtn = e.target.closest('[data-notif-read]');
    if (!readBtn) return;

    e.preventDefault();
    e.stopPropagation();

    const notifId = readBtn.getAttribute('data-notif-id');
    if (!notifId) return;

    try {
      const data = await post(basePath + '/api/notifications/read.php', {
        notification_id: notifId
      });

      console.log('notification read response', data);

      if (!data.ok) {
        alert(data.msg || 'Failed to mark notification as read.');
        return;
      }

      const card = readBtn.closest('[data-notif-card]');
      if (card) {
        const pill = card.querySelector('.notif-new-pill');
        if (pill) pill.remove();
      }

      readBtn.remove();

      const badge = document.querySelector('[data-dd-btn="notif"]')?.closest('.md-ico-wrap')?.querySelector('.md-badge');
      if (badge) {
        const nextCount = Math.max(0, Number(badge.textContent || '0') - 1);
        if (nextCount > 0) {
          badge.textContent = String(nextCount);
        } else {
          badge.remove();
        }
      }
    } catch (err) {
      console.error('notification read failed', err);
      alert('Failed to mark notification as read.');
    }
  }, true);

  document.addEventListener('click', async (e) => {
    const openBtn = e.target.closest('[data-notif-open]');
    if (!openBtn) return;

    e.preventDefault();
    e.stopPropagation();

    const notifId = openBtn.getAttribute('data-notif-id');
    const notifType = openBtn.getAttribute('data-notif-type') || '';
    const linkUrl = (openBtn.getAttribute('data-link-url') || '').trim();

    if (notifId) {
      try {
        await post(basePath + '/api/notifications/read.php', {
          notification_id: notifId
        });

        const card = openBtn.closest('[data-notif-card]');
        const hadUnreadPill = !!card?.querySelector('.notif-new-pill');

        if (card) {
          const pill = card.querySelector('.notif-new-pill');
          if (pill) pill.remove();
        }

        const readBtn = card?.querySelector('[data-notif-read]');
        if (readBtn) {
          readBtn.remove();
        }

        const badge = document.querySelector('[data-dd-btn="notif"]')
          ?.closest('.md-ico-wrap')
          ?.querySelector('.md-badge');

        if (hadUnreadPill && badge) {
          const nextCount = Math.max(0, Number(badge.textContent || '0') - 1);
          if (nextCount > 0) {
            badge.textContent = String(nextCount);
          } else {
            badge.remove();
          }
        }
      } catch (err) {
        console.error('notification auto-read failed', err);
      }
    }

    const dropdown = openBtn.closest('.dd');
    if (dropdown) {
      dropdown.classList.remove('is-open');
    }

    if (!linkUrl) return;

    if (notifType === 'message') {
      const conversationMatch = linkUrl.match(/[?&]conversation_id=(\d+)/i);
      const targetUserMatch = linkUrl.match(/[?&]target_user_id=(\d+)/i);

      chatClosedManually = false;

      if (conversationMatch && conversationMatch[1]) {
        loadThread({ conversation_id: conversationMatch[1] });
        return;
      }

      if (targetUserMatch && targetUserMatch[1]) {
        loadThread({ target_user_id: targetUserMatch[1] });
        return;
      }
    }

    window.location.href = linkUrl.startsWith('http')
      ? linkUrl
      : (basePath + '/' + linkUrl.replace(/^\/+/, ''));
  }, true);

    document.addEventListener('click', async (e) => {
    const readAllBtn = e.target.closest('[data-notif-read-all]');
    if (!readAllBtn) return;

    e.preventDefault();
    e.stopPropagation();

    try {
      const data = await post(basePath + '/api/notifications/read_all.php', {});

      console.log('notification read all response', data);

      if (!data.ok) {
        alert(data.msg || 'Failed to mark all notifications as read.');
        return;
      }

      document.querySelectorAll('#dd-notif .notif-new-pill').forEach(el => el.remove());
      document.querySelectorAll('#dd-notif [data-notif-read]').forEach(el => el.remove());

      const badge = document.querySelector('[data-dd-btn="notif"]')
        ?.closest('.md-ico-wrap')
        ?.querySelector('.md-badge');

      if (badge) {
        badge.remove();
      }
    } catch (err) {
      console.error('notification read all failed', err);
      alert('Failed to mark all notifications as read.');
    }
  }, true);

  inputEl.addEventListener('input', () => {
    resetComposerHeight();
  });

  inputEl.addEventListener('keydown', (e) => {
    if (e.key === 'Enter' && !e.shiftKey) {
      e.preventDefault();
      formEl.requestSubmit();
    }
  });

  resetComposerHeight();

  formEl.addEventListener('submit', sendMessage);

  closeBtn.addEventListener('click', (e) => {
    e.preventDefault();
    e.stopPropagation();

    chatClosedManually = true;
    popup.hidden = true;
    popup.style.display = 'none';
    popup.classList.remove('is-min');

    activeConversationId = 0;
    activeReceiverId = 0;
    conversationEl.value = '';
    receiverEl.value = '';
    inputEl.value = '';
    resetComposerHeight();

    avatarImgEl.src = '';
    avatarImgEl.hidden = true;
    avatarFallbackEl.hidden = false;
    avatarFallbackEl.textContent = 'C';

    nameEl.textContent = 'Chat';
    metaEl.textContent = 'Direct message';
    bodyEl.innerHTML = '';

    stopPolling();
  });

  minBtn.addEventListener('click', (e) => {
    e.preventDefault();
    e.stopPropagation();

    popup.classList.toggle('is-min');
  });
})();
})();