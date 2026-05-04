const shell = document.querySelector(".ranked-page-shell");
const BASE_PATH = shell?.dataset.basePath || "";

const tierBadge = document.getElementById("rankedTierBadge");
const tierEl = document.getElementById("rankedTier");
const trophyEl = document.getElementById("rankedTrophy");
const entryFeeEl = document.getElementById("rankedEntryFee");
const winsEl = document.getElementById("rankedWins");
const lossesEl = document.getElementById("rankedLosses");
const streakEl = document.getElementById("rankedStreak");
const expMultEl = document.getElementById("rankedExpMult");
const queueStatusEl = document.getElementById("rankedQueueStatus");
const timerEl = document.getElementById("rankedTimer");
const joinBtn = document.getElementById("rankedJoinBtn");
const cancelBtn = document.getElementById("rankedCancelBtn");
const msgEl = document.getElementById("rankedMessage");

let queueStartedAt = null;
let busy = false;

function setMsg(text) {
  if (msgEl) msgEl.textContent = text || "";
}

async function postJson(url, payload = {}) {
  const res = await fetch(url, {
    method: "POST",
    headers: { "Content-Type": "application/json" },
    body: JSON.stringify(payload),
  });

  const data = await res.json().catch(() => ({ ok: false, msg: "Invalid server response." }));

  if (!res.ok || !data.ok) {
    throw new Error(data.msg || "Request failed.");
  }

  return data;
}

async function getJson(url) {
  const res = await fetch(url, { cache: "no-store" });
  const data = await res.json().catch(() => ({ ok: false, msg: "Invalid server response." }));

  if (!res.ok || !data.ok) {
    throw new Error(data.msg || "Request failed.");
  }

  return data;
}

function formatZeny(value) {
  return `${Number(value || 0).toLocaleString()} Zeny`;
}

function formatTimer(seconds) {
  seconds = Math.max(0, Number(seconds || 0));
  const m = Math.floor(seconds / 60);
  const s = seconds % 60;
  return `${String(m).padStart(2, "0")}:${String(s).padStart(2, "0")}`;
}

function updateTimer() {
  if (!timerEl) return;

  if (!queueStartedAt) {
    timerEl.textContent = "00:00";
    return;
  }

  const diff = Math.floor((Date.now() - queueStartedAt.getTime()) / 1000);
  timerEl.textContent = formatTimer(diff);
}

function renderStatus(status) {
  const profile = status.profile || {};
  const queue = status.queue || {};
  const match = status.match || {};

  if (tierBadge) tierBadge.textContent = profile.tier || "Unranked";
  if (tierEl) tierEl.textContent = profile.tier || "Unranked";
  if (trophyEl) trophyEl.textContent = `${Number(profile.trophy || 0).toLocaleString()} trophies`;
  if (entryFeeEl) entryFeeEl.textContent = formatZeny(profile.entry_fee);
  if (winsEl) winsEl.textContent = Number(profile.wins || 0).toLocaleString();
  if (lossesEl) lossesEl.textContent = Number(profile.losses || 0).toLocaleString();
  if (streakEl) streakEl.textContent = Number(profile.win_streak || 0).toLocaleString();
  if (expMultEl) expMultEl.textContent = `${Number(profile.exp_multiplier || 1).toFixed(2)}x`;

  if (match.found && match.room_code) {
    queueStatusEl.textContent = "Match found. Redirecting...";
    window.location.href = `${BASE_PATH}/room.php?code=${encodeURIComponent(match.room_code)}`;
    return;
  }

  if (queue.in_queue) {
    queueStatusEl.textContent = `Searching... ${Number(queue.queue_count || 1)}/4 players found. Position #${Number(queue.queue_position || 1)}.`;

    if (!queueStartedAt && queue.joined_at) {
      const parsed = new Date(String(queue.joined_at).replace(" ", "T"));
      queueStartedAt = Number.isNaN(parsed.getTime()) ? new Date() : parsed;
    }
  } else {
    queueStatusEl.textContent = "Not queued.";
    queueStartedAt = null;
  }

  if (joinBtn) joinBtn.disabled = busy || !!queue.in_queue;
  if (cancelBtn) cancelBtn.disabled = busy || !queue.in_queue;
}

async function refreshStatus() {
  const data = await getJson(`${BASE_PATH}/api/game/ranked_status.php`);
  renderStatus(data.status);
}

joinBtn?.addEventListener("click", async () => {
  try {
    busy = true;
    setMsg("Entering ranked queue...");
    joinBtn.disabled = true;

    const data = await postJson(`${BASE_PATH}/api/game/ranked_join.php`);

    if (data.redirect_url) {
      window.location.href = data.redirect_url;
      return;
    }

    const roomCode = data?.status?.match?.room_code || "";

    if (roomCode) {
      window.location.href = `${BASE_PATH}/room.php?code=${encodeURIComponent(roomCode)}`;
      return;
    }

    window.location.href = `${BASE_PATH}/dashboard.php?queued=ranked`;
  } catch (err) {
    setMsg(err.message);
    busy = false;
    await refreshStatus().catch(() => {});
  }
});

cancelBtn?.addEventListener("click", async () => {
  try {
    busy = true;
    setMsg("Cancelling queue...");

    const data = await postJson(`${BASE_PATH}/api/game/ranked_cancel.php`);
    renderStatus(data.status);
    setMsg("Queue cancelled. Entry fee refunded.");
  } catch (err) {
    setMsg(err.message);
  } finally {
    busy = false;
    await refreshStatus().catch(() => {});
  }
});

setInterval(() => {
  refreshStatus().catch((err) => setMsg(err.message));
}, 1500);

setInterval(updateTimer, 1000);

refreshStatus().catch((err) => setMsg(err.message));