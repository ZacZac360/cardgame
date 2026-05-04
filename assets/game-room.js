const shellEl = document.querySelector(".game-room-shell");
const ROOM_CODE = shellEl?.dataset.roomCode || "";
const BASE_PATH = shellEl?.dataset.basePath || "";
const IS_GUEST = shellEl?.dataset.isGuest === "1";

const seatTopEl = document.getElementById("seat-top");
const seatLeftEl = document.getElementById("seat-left");
const seatRightEl = document.getElementById("seat-right");
const tableAreaEl = document.getElementById("tableArea");
const handAreaEl = document.getElementById("handArea");
const logAreaEl = document.getElementById("logArea");
const humanSummaryEl = document.getElementById("humanSummary");

const refreshBtn = document.getElementById("refreshBtn");
const resetRoomBtn = document.getElementById("resetRoomBtn");
const destroyRoomBtn = document.getElementById("destroyRoomBtn");
const startGameBtn = document.getElementById("startGameBtn");
const playBtn = document.getElementById("playBtn");
const passBtn = document.getElementById("passBtn");
const leaveRoomBtn = document.getElementById("leaveRoomBtn");
const wildChooserEl = document.getElementById("wildChooser");

const actionMsgEl = document.getElementById("actionMsg");
const roomStatusValueEl = document.getElementById("roomStatusValue");
const roomModeValueEl = document.getElementById("roomModeValue");
const turnValueEl = document.getElementById("turnValue");
const meValueEl = document.getElementById("meValue");

const toolsPanelEl = document.getElementById("toolsPanel");
const logPanelEl = document.getElementById("logPanel");
const toolsToggleBtn = document.getElementById("toolsToggleBtn");
const logToggleBtn = document.getElementById("logToggleBtn");
const toolsCloseBtn = document.getElementById("toolsCloseBtn");
const logCloseBtn = document.getElementById("logCloseBtn");
const hostControlsSectionEl = document.getElementById("hostControlsSection");

const rulesEditorEl = document.getElementById("rulesEditor");
const saveRulesBtn = document.getElementById("saveRulesBtn");
const ruleAllowAiFillEl = document.getElementById("ruleAllowAiFill");
const ruleStartingHandSizeEl = document.getElementById("ruleStartingHandSize");
const ruleAllowStackPlus2El = document.getElementById("ruleAllowStackPlus2");
const ruleAllowStackPlus4El = document.getElementById("ruleAllowStackPlus4");
const ruleDrawUntilPlayableEl = document.getElementById("ruleDrawUntilPlayable");

const IS_TOUCH_DEVICE =
  window.matchMedia("(pointer: coarse)").matches ||
  "ontouchstart" in window ||
  navigator.maxTouchPoints > 0;

let latestState = null;
let selectedCardId = null;
let pendingPlus4CardId = null;
let busy = false;
let resultsModalShown = false;
let trainingTransitionModalShown = false;
let topnavSnapshot = null;

const SOLO_TUTORIAL_DIALOGUES = {
  training_1: {
    idle: {
      objective: "Match the table first.",
      explanation: "The table card is Wind. Your glowing Wind card is playable because matching the same element is always allowed.",
      tip: "Click the glowing Wind card beside me.",
    },
    selected: {
      objective: "Good. Now play it.",
      explanation: "Correct. Same element works even if your number is lower. Wind can go on Wind.",
      tip: "Press Play, or double-click the selected card.",
    },
    watching: {
      objective: "Watch what the opponent does.",
      explanation: "After your move, the table will change. Pay attention to the new element because your next move depends on it.",
      tip: "When your turn comes back, either match the element or play the element that beats it.",
    },
    no_target: {
      objective: "Try it yourself.",
      explanation: "No glowing card this time. Use what you learned: match the table element, or use the element that beats it.",
      tip: "Look at the table card, then choose a playable card from your hand.",
    },
  },

  training_2: {
    idle: {
      objective: "Play the glowing stronger card.",
      explanation: "This time, you are not matching the same element. You are using the element that beats the card on the table.",
      tip: "Click the glowing card beside me.",
    },
    selected: {
      objective: "Correct. Play that card.",
      explanation: "That card wins because its element is stronger than the active card.",
      tip: "Press Play, or double-click the glowing card.",
    },
    watching: {
      objective: "Good counter. Watch the table.",
      explanation: "The active card may change after the opponent moves.",
      tip: "Wait for the next glowing card.",
    },
    no_target: {
      objective: "No counter card right now.",
      explanation: "The lesson has moved past the planned counter step.",
      tip: "Press Pass to continue.",
    },
  },

  training_3: {
    idle: {
      objective: "Play the glowing special card.",
      explanation: "Special cards change the flow of the match. +2 adds pressure. +4 lets you choose the next element.",
      tip: "Click the glowing special card beside me.",
    },
    selected: {
      objective: "Special card selected.",
      explanation: "Now commit the move. If it is +4, choose the element you want next.",
      tip: "Press Play. Choose an element if asked.",
    },
    watching: {
      objective: "Special card played. Watch the effect.",
      explanation: "The next player must respond to the new pressure or chosen element.",
      tip: "Wait for your next guided move.",
    },
    no_target: {
      objective: "No special card target right now.",
      explanation: "The special-card lesson has moved forward.",
      tip: "Press Pass to continue.",
    },
  },
};

function escapeHtml(value) {
  return String(value ?? "")
    .replaceAll("&", "&amp;")
    .replaceAll("<", "&lt;")
    .replaceAll(">", "&gt;")
    .replaceAll('"', "&quot;")
    .replaceAll("'", "&#039;");
}

function addLocalMsg(el, text) {
  if (el) el.textContent = text || "";
}

function roomBackUrl() {
  return IS_GUEST
    ? `${BASE_PATH}/guest_dashboard.php`
    : `${BASE_PATH}/play.php`;
}

function goRoomBack() {
  window.location.href = roomBackUrl();
}

function getTopnavSnapshot() {
  const levelPill = document.getElementById("topnavLevelPill");
  const xpFill = document.getElementById("topnavXpFill");
  const xpText = document.getElementById("topnavXpText");

  const levelMatch = (levelPill?.textContent || "").match(/Lv\.\s*(\d+)/i);
  const textMatch = (xpText?.textContent || "").match(/([\d,]+)\s*\/\s*([\d,]+)\s*EXP/i);

  return {
    level: levelMatch ? Number(levelMatch[1]) : 1,
    progressPct: Number(xpFill?.dataset.progress || 0),
    exp: textMatch ? Number(String(textMatch[1]).replaceAll(",", "")) : 0,
    expToNext: textMatch ? Number(String(textMatch[2]).replaceAll(",", "")) : 500,
  };
}

function updateTopnavProgress(profile) {
  if (!profile) return;

  const levelPill = document.getElementById("topnavLevelPill");
  const xpFill = document.getElementById("topnavXpFill");
  const xpText = document.getElementById("topnavXpText");

  if (levelPill) {
    levelPill.textContent = `Lv. ${Number(profile.level || 1)}`;
  }

  if (xpFill) {
    const pct = Number(profile.progress_pct || 0);
    xpFill.dataset.progress = String(pct);
    xpFill.style.width = `${pct}%`;
  }

  if (xpText) {
    xpText.textContent = `${Number(profile.exp || 0).toLocaleString()} / ${Number(profile.exp_to_next || 0).toLocaleString()} EXP`;
  }
}

function ensureTrainingTransitionModal() {
  let modal = document.getElementById("trainingTransitionModal");
  if (modal) return modal;

  modal = document.createElement("div");
  modal.id = "trainingTransitionModal";
  modal.className = "results-modal hidden";
  modal.innerHTML = `
    <div class="results-modal__dialog">
      <div class="results-modal__head">
        <div class="results-modal__titlewrap">
          <div class="results-modal__eyebrow">Tutorial Checkpoint</div>
          <h2 class="results-modal__title" id="trainingTransitionTitle">Nice! You got the lesson.</h2>
          <div class="results-modal__sub" id="trainingTransitionSub">Now try the same rule without the glowing guide.</div>
        </div>

        <button type="button" class="icon-btn" id="trainingTransitionCloseBtn">✕</button>
      </div>

      <div class="results-modal__body">
        <div class="results-card">
          <div class="results-card__label">Next Round</div>
          <p id="trainingTransitionBody" style="margin:0; line-height:1.6;">
            Your hand has been reset. This time, choose the correct card yourself.
          </p>
        </div>
      </div>

      <div class="results-modal__actions">
        <button type="button" class="ui-btn" id="trainingTransitionContinueBtn">Try It Myself</button>
      </div>
    </div>
  `;

  document.body.appendChild(modal);

  const close = () => modal.classList.add("hidden");

  modal.querySelector("#trainingTransitionCloseBtn")?.addEventListener("click", close);
  modal.querySelector("#trainingTransitionContinueBtn")?.addEventListener("click", close);

  return modal;
}

function maybeShowTrainingTransitionModal() {
  const room = latestState?.room;
  const rules = room?.rules || {};

  if (!room || room.status !== "playing") return;
  if (room.room_type !== "solo") return;
  if (!["training_1", "training_2", "training_3"].includes(String(rules.solo_level_key || ""))) return;
  if (String(rules[String(rules.solo_level_key || "") + "_phase"] || "") !== "try") return;

  const storageKey = `logia_training_transition_${ROOM_CODE}`;
  if (trainingTransitionModalShown || sessionStorage.getItem(storageKey) === "1") {
    return;
  }

  trainingTransitionModalShown = true;
  sessionStorage.setItem(storageKey, "1");

  const modal = ensureTrainingTransitionModal();
  const levelKey = String(rules.solo_level_key || "");

  const copy = {
    training_1: {
      title: "Nice! You got the basics.",
      sub: "Now try the same element rule without the glowing guide.",
      body: "Your hand has been reset. This time, choose the card yourself: match the table element, or play the element that beats it.",
    },
    training_2: {
      title: "Good. You used a +2.",
      sub: "Now try a +2 round without the glowing guide.",
      body: "Your hand has been reset. Use +2 cards when they are valid. Remember: a +2 creates pressure, and stacking only works when another +2 is allowed.",
    },
    training_3: {
      title: "Good. You used a Wildcard.",
      sub: "Now try a +4 Wild round without the glowing guide.",
      body: "Your hand has been reset. Use +4 Wild to change the active element and set up your next move.",
    },
  }[levelKey] || {
    title: "Nice! You got the lesson.",
    sub: "Now try it without the glowing guide.",
    body: "Your hand has been reset. Win this round by yourself.",
  };

  modal.querySelector("#trainingTransitionTitle").textContent = copy.title;
  modal.querySelector("#trainingTransitionSub").textContent = copy.sub;
  modal.querySelector("#trainingTransitionBody").textContent = copy.body;

  modal.classList.remove("hidden");
}

function ensureResultsModal() {
  let modal = document.getElementById("resultsModal");
  if (modal) return modal;

  modal = document.createElement("div");
  modal.id = "resultsModal";
  modal.className = "results-modal hidden";
  modal.innerHTML = `
    <div class="results-modal__dialog">
      <div class="results-modal__head">
        <div class="results-modal__titlewrap">
          <div class="results-modal__eyebrow">Match Complete</div>
          <h2 class="results-modal__title">Results</h2>
          <div class="results-modal__sub" id="resultsModalSub">Placements and EXP</div>
        </div>

        <button type="button" class="icon-btn" id="resultsModalCloseBtn">✕</button>
      </div>

      <div class="results-modal__body">
        <div class="results-summary">
          <div class="results-card">
            <div class="results-card__label">Your Result</div>

            <div class="results-place">
              <div class="results-place__badge" id="resultsPlaceBadge">#1</div>
              <div class="results-place__meta">
                <div class="results-place__name" id="resultsPlaceName">You</div>
                <div class="results-place__xp" id="resultsPlaceXp">+0 EXP</div>
              </div>
            </div>

            <div class="results-xpbar">
              <div class="results-xpbar__fill" id="resultsXpFill"></div>
            </div>
            <div class="results-xpbar__text" id="resultsXpText">0 / 0 EXP</div>
          </div>

          <div class="results-card">
            <div class="results-card__label">Placements</div>
            <div class="results-placements" id="resultsPlacementsList"></div>
          </div>
        </div>
      </div>

      <div class="results-modal__actions">
        <button type="button" class="ui-btn" id="resultsBackBtn">Back to Dashboard</button>
      </div>
    </div>
  `;

  document.body.appendChild(modal);

  const close = () => modal.classList.add("hidden");
  modal.querySelector("#resultsModalCloseBtn")?.addEventListener("click", goRoomBack);

  const backBtn = modal.querySelector("#resultsBackBtn");
  if (backBtn) {
    backBtn.textContent = IS_GUEST ? "Back to Guest Dashboard" : "Back to Play";
    backBtn.addEventListener("click", goRoomBack);
  }

  return modal;
}

function showResultsModal() {
  const room = latestState?.room;
  const finalResults = latestState?.final_results || [];
  const meResult = latestState?.me_result || null;
  const viewerProgress = latestState?.viewer_progress || null;

  if (!room || room.status !== "finished" || !meResult || !finalResults.length) {
    return;
  }

  const modal = ensureResultsModal();
  const placeBadge = modal.querySelector("#resultsPlaceBadge");
  const placeName = modal.querySelector("#resultsPlaceName");
  const placeXp = modal.querySelector("#resultsPlaceXp");
  const placementsList = modal.querySelector("#resultsPlacementsList");
  const xpFill = modal.querySelector("#resultsXpFill");
  const xpText = modal.querySelector("#resultsXpText");
  const sub = modal.querySelector("#resultsModalSub");

  if (placeBadge) placeBadge.textContent = `#${meResult.place}`;
  if (placeName) placeName.textContent = meResult.player_name || "You";
  if (placeXp) placeXp.textContent = `+${Number(meResult.xp_awarded || 0)} EXP`;
  if (sub) sub.textContent = `${String(room.room_type || "match").replace(/^./, (c) => c.toUpperCase())} results for room ${room.room_code}`;

  if (placementsList) {
    placementsList.innerHTML = finalResults.map((row) => `
      <div class="results-row ${Number(row.seat_no) === Number(meResult.seat_no) ? "results-row--me" : ""}">
        <div class="results-row__place">#${Number(row.place || 0)}</div>
        <div>
          <div class="results-row__name">${escapeHtml(row.player_name || "Player")}</div>
          <div class="results-row__meta">${escapeHtml(row.player_type === "ai" ? "AI" : `Seat ${row.seat_no}`)} · ${Number(row.card_count || 0)} card(s) left</div>
        </div>
        <div class="results-row__xp">${row.player_type === "human" ? `+${Number(row.xp_awarded || 0)} EXP` : "—"}</div>
      </div>
    `).join("");
  }

  const fromPct = Number(topnavSnapshot?.progressPct || 0);
  const toPct = Number(viewerProgress?.progress_pct || 0);

  if (xpFill) {
    xpFill.style.width = `${fromPct}%`;
    requestAnimationFrame(() => {
      requestAnimationFrame(() => {
        xpFill.style.width = `${toPct}%`;
      });
    });
  }

  if (xpText) {
    xpText.textContent = `${Number(viewerProgress?.exp || 0).toLocaleString()} / ${Number(viewerProgress?.exp_to_next || 0).toLocaleString()} EXP`;
  }

  modal.classList.remove("hidden");
}

function maybeHandleFinishedMatch() {
  const room = latestState?.room;
  if (!room || room.status !== "finished") return;

  if (!resultsModalShown) {
    showResultsModal();
    resultsModalShown = true;
    sessionStorage.setItem(`logia_results_shown_${ROOM_CODE}`, "1");
  }

  if (latestState?.viewer_progress) {
    updateTopnavProgress(latestState.viewer_progress);
  }
}

function getCurrentRoomRules() {
  return latestState?.room?.rules || {};
}

function getSoloTutorialInfo() {
  const room = latestState?.room || {};
  const tutorial = latestState?.solo_tutorial || null;

  if (room.room_type !== "solo" || !tutorial) {
    return null;
  }

  return {
    title: tutorial.title || "Solo Training",
    speaker: tutorial.speaker || "Guide",
    objective: tutorial.objective || "Complete the solo encounter.",
    explanation: tutorial.explanation || "Use the rules you learned to win the match.",
    tip: tutorial.tip || getTurnHint() || "Play the best available card.",
    type: tutorial.type || "solo",
    expectedElement: tutorial.expected_element || "",
    expectedKind: tutorial.expected_kind || "",
    levelKey: tutorial.level_key || "",
  };
}

function isSoloTrainingRoom() {
  const room = latestState?.room || {};
  const tutorial = getSoloTutorialInfo();
  const rules = room.rules || {};

  if (
    room.room_type === "solo" &&
    ["training_1", "training_2", "training_3"].includes(String(rules.solo_level_key || "")) &&
    String(rules[String(rules.solo_level_key || "") + "_phase"] || "") === "try"
  ) {
    return false;
  }

  return room.room_type === "solo" && tutorial && tutorial.type === "training";
}

function pickSoloTutorialTargetFromRaw(rawPlayable, tutorial) {
  if (!tutorial || !Array.isArray(rawPlayable) || !rawPlayable.length) {
    return null;
  }

  const expectedKind = String(tutorial.expectedKind || "");
  const expectedElement = String(tutorial.expectedElement || "");

  if (expectedKind === "" && expectedElement === "") {
    return null;
  }

  if (expectedKind === "special") {
    return rawPlayable.find((card) => card.kind === "plus2" || card.kind === "plus4") || null;
  }

  if (expectedKind !== "" && expectedElement !== "") {
    const exactTarget = rawPlayable.find((card) => {
      return card.kind === expectedKind && card.element === expectedElement;
    });

    if (exactTarget) return exactTarget;
  }

  return null;
}

function getSoloTutorialTargetCard(hand, room) {
  const tutorial = getSoloTutorialInfo();
  if (!tutorial || !Array.isArray(hand) || !room) return null;

  const activeCard = room.active_card || null;
  const pendingDraw = Number(room.pending_draw || 0);
  const rawPlayable = hand.filter((card) => canPlayCard(card, activeCard, pendingDraw, room.rules || {}));

  return pickSoloTutorialTargetFromRaw(rawPlayable, tutorial);
}

function getSoloTutorialDynamicCopy() {
  const room = latestState?.room || {};
  const me = latestState?.me || null;
  const tutorial = getSoloTutorialInfo();

  if (!tutorial || !me || room.status !== "playing") {
    return tutorial;
  }

  const levelKey = latestState?.solo_tutorial?.level_key || latestState?.room?.rules?.solo_level_key || "";
  const dialogueSet = SOLO_TUTORIAL_DIALOGUES[levelKey] || {};
  const myTurn = room.current_turn_seat === me.seat_no;
  const hand = me.hand || [];
  const target = getSoloTutorialTargetCard(hand, room);
  const selected = selectedCardId ? getCardById(selectedCardId) : null;

  let stage = "idle";

  if (!myTurn) {
    stage = "watching";
  } else if (!target) {
    stage = "no_target";
  } else if (selected && selected.id === target.id) {
    stage = "selected";
  }

  const line = dialogueSet[stage] || {};

  if (!myTurn) {
    const turnSeat = room.current_turn_seat ? getSeatByNo(room.current_turn_seat) : null;

    return {
      ...tutorial,
      objective: line.objective || "Watch the opponent.",
      explanation: line.explanation || `${turnSeat?.player_name || "The opponent"} is taking a turn.`,
      tip: line.tip || "Wait for your next guided move.",
    };
  }

  if (!target) {
    const activeElement = getEffectiveElement(room.active_card || null) || room.active_element || "the table element";

    return {
      ...tutorial,
      objective: line.objective || "Try it yourself.",
      explanation: `The table is now ${activeElement}. Choose any card that matches ${activeElement}, or any element that beats it.`,
      tip: line.tip || "Use the element rule yourself this time.",
    };
  }

  if (stage === "selected") {
    return {
      ...tutorial,
      objective: line.objective || `Now play ${cardText(target)}.`,
      explanation: line.explanation || "Correct card selected.",
      tip: line.tip || "Press Play or double-click the highlighted card.",
    };
  }

  return {
    ...tutorial,
    objective: line.objective || "Play the glowing card.",
    explanation: line.explanation || "This card is the correct tutorial move.",
    tip: line.tip || "Click the glowing card beside me.",
  };
}

function syncRulesFormFromState() {
  const rules = getCurrentRoomRules();

  if (ruleAllowAiFillEl) ruleAllowAiFillEl.checked = !!rules.allow_ai_fill;
  if (ruleStartingHandSizeEl) ruleStartingHandSizeEl.value = Number(rules.starting_hand_size ?? 5);
  if (ruleAllowStackPlus2El) ruleAllowStackPlus2El.checked = !!rules.allow_stack_plus2;
  if (ruleAllowStackPlus4El) ruleAllowStackPlus4El.checked = !!rules.allow_stack_plus4;
  if (ruleDrawUntilPlayableEl) ruleDrawUntilPlayableEl.checked = !!rules.draw_until_playable;
}

function collectRulesFromForm() {
  return {
    allow_ai_fill: !!ruleAllowAiFillEl?.checked,
    starting_hand_size: Number(ruleStartingHandSizeEl?.value || 5),
    allow_stack_plus2: !!ruleAllowStackPlus2El?.checked,
    allow_stack_plus4: !!ruleAllowStackPlus4El?.checked,
    draw_until_playable: !!ruleDrawUntilPlayableEl?.checked,
  };
}

async function saveRoomSettings(nextMaxPlayers = null) {
  const currentMaxPlayers = Number(latestState?.room?.max_players || 4);
  const payload = {
    room_code: ROOM_CODE,
    max_players: Number(nextMaxPlayers || currentMaxPlayers),
    rules: collectRulesFromForm(),
  };

  return postJson(`${BASE_PATH}/api/game/update_room.php`, payload);
}

function setBusy(nextBusy) {
  busy = !!nextBusy;
  if (refreshBtn) refreshBtn.disabled = busy;
  if (startGameBtn) startGameBtn.disabled = busy;
  if (resetRoomBtn) resetRoomBtn.disabled = busy;
  if (destroyRoomBtn) destroyRoomBtn.disabled = busy;
  if (playBtn) playBtn.disabled = busy;
  if (passBtn) passBtn.disabled = busy;
  if (leaveRoomBtn) leaveRoomBtn.disabled = busy;
}

function getSeatByNo(seatNo) {
  return (latestState?.seats || []).find((s) => s && s.seat_no === seatNo) || null;
}

function getEffectiveElement(card) {
  if (!card) return null;
  if (card.kind === "plus4") return card.chosenElement || null;
  return card.element || null;
}

function cardValueText(card) {
  if (!card) return "—";
  if (card.kind === "normal") return String(card.value ?? "—");
  if (card.kind === "plus2") return "+2";
  if (card.kind === "plus4") return "+4";
  return "—";
}

function cardText(card) {
  if (!card) return "None";
  if (card.kind === "normal") return `${card.element} ${card.value}`;
  if (card.kind === "plus2") return `+2 ${card.element}`;
  if (card.kind === "plus4") {
    return card.chosenElement ? `+4 Wild → ${card.chosenElement}` : "+4 Wild";
  }
  return card.name || "Card";
}

function getCardGradient(card) {
  const palettes = {
    Fire: "linear-gradient(180deg, #ff9c7a 0%, #d4553d 100%)",
    Water: "linear-gradient(180deg, #8cc6ff 0%, #4f7fc0 100%)",
    Earth: "linear-gradient(180deg, #b89472 0%, #7f624a 100%)",
    Lightning: "linear-gradient(180deg, #d1b5ff 0%, #8058d8 100%)",
    Wind: "linear-gradient(180deg, #8fe2ea 0%, #45aebc 100%)",
    Wood: "linear-gradient(180deg, #8fd2a7 0%, #3f8d58 100%)",
    Wild: "linear-gradient(180deg, #b8c7d9 0%, #67788f 100%)",
  };

  if (!card) {
    return "linear-gradient(180deg, rgba(255,255,255,.06), rgba(255,255,255,.02))";
  }

  if (card.kind === "plus4") {
    if (!card.chosenElement) {
      return "linear-gradient(180deg, #181818 0%, #000000 100%)";
    }
    return palettes[card.chosenElement] || palettes.Wild;
  }

  return palettes[card.element] || palettes.Wild;
}

function compareElements(challenger, defender) {
  if (!challenger || !defender) return "neutral";
  if (challenger === "Wild" || defender === "Wild") return "neutral";
  if (typeof STRONG_AGAINST !== "undefined" && STRONG_AGAINST[challenger] === defender) return "strong";
  if (typeof STRONG_AGAINST !== "undefined" && STRONG_AGAINST[defender] === challenger) return "weak";
  return "neutral";
}

function canPlayCard(card, activeCard, pendingDraw, rules = null) {
  if (!activeCard) return true;

  const activeKind = String(activeCard.kind || "");
  const cardKind = String(card.kind || "");
  const allowStackPlus2 = !!rules?.allow_stack_plus2;
  const allowStackPlus4 = !!rules?.allow_stack_plus4;

  if (Number(pendingDraw || 0) > 0) {
    if (activeKind === "plus2") {
      return cardKind === "plus2" && allowStackPlus2;
    }

    if (activeKind === "plus4") {
      return cardKind === "plus4" && allowStackPlus4;
    }

    return false;
  }

  if (cardKind === "plus4") {
    return true;
  }

  const targetElement = getEffectiveElement(activeCard);
  const cardElement = card.element || null;

  if (cardKind === "plus2" || cardKind === "normal") {
    if (cardElement && targetElement && cardElement === targetElement) {
      return true;
    }

    return compareElements(cardElement, targetElement) === "strong";
  }

  return false;
}

function getPlayableCards(hand, room) {
  const activeCard = room?.active_card || null;
  const pendingDraw = Number(room?.pending_draw || 0);
  const rawPlayable = (hand || []).filter((card) => canPlayCard(card, activeCard, pendingDraw, room?.rules || {}));

  if (isSoloTrainingRoom() && latestState?.me && hand === latestState.me.hand) {
    const target = getSoloTutorialTargetCard(hand, room);

    if (target) {
      return [target];
    }

    return rawPlayable;
  }

  return rawPlayable;
}

function getCardById(cardId) {
  const hand = latestState?.me?.hand || [];
  return hand.find((card) => card.id === cardId) || null;
}

async function postJson(url, payload = {}) {
  const res = await fetch(url, {
    method: "POST",
    headers: { "Content-Type": "application/json" },
    body: JSON.stringify(payload),
  });

  let data = {};
  try {
    data = await res.json();
  } catch {
    data = { ok: false, msg: "Invalid server response." };
  }

  if (!res.ok || !data.ok) {
    throw new Error(data.msg || "Request failed.");
  }

  return data;
}

function applyState(data) {
  latestState = data;

  const myHand = latestState?.me?.hand || [];
  if (selectedCardId && !myHand.find((c) => c.id === selectedCardId)) {
    selectedCardId = null;
  }

  if (pendingPlus4CardId && !myHand.find((c) => c.id === pendingPlus4CardId)) {
    pendingPlus4CardId = null;
    wildChooserEl.classList.add("hidden");
  }

  syncRulesFormFromState();
  render();
  maybeShowTrainingTransitionModal();
  maybeHandleFinishedMatch();
}

async function fetchState() {
  const url = `${BASE_PATH}/api/game/state.php?room_code=${encodeURIComponent(ROOM_CODE)}`;
  const res = await fetch(url, { cache: "no-store" });
  const data = await res.json();

  if (!data.ok) {
    throw new Error(data.msg || "Failed to fetch state.");
  }

  applyState(data);
}

function cardIsPlayable(card) {
  const room = latestState?.room;
  const me = latestState?.me;

  if (!room || room.status !== "playing") return false;
  if (!me) return false;
  if (room.current_turn_seat !== me.seat_no) return false;

  const normallyPlayable = canPlayCard(card, room.active_card || null, Number(room.pending_draw || 0), room.rules || {});
  if (!normallyPlayable) return false;

  if (isSoloTrainingRoom()) {
    const target = getSoloTutorialTargetCard(me.hand || [], room);

    if (target && target.id !== card.id) {
      return false;
    }
  }

  return true;
}

function renderAICardBacks(count, isTop = false) {
  const visible = Math.min(Number(count || 0), isTop ? 8 : 6);
  let html = `<div class="seat-fan">`;
  for (let i = 0; i < visible; i += 1) {
    html += `<div class="ai-cardback"></div>`;
  }
  html += `</div>`;
  return html;
}

function renderSeat(mountEl, seatNo) {
  const seat = getSeatByNo(seatNo);
  const room = latestState?.room;
  const isTurn = room?.current_turn_seat === seatNo;
  const isTopSeat = seatNo === 3;

  if (!mountEl) return;

  if (!seat || !seat.occupied) {
    mountEl.innerHTML = `
      <div class="seat-box seat-box--open ${isTopSeat ? "seat-box--top" : ""}">
        <div class="seat-head ${isTopSeat ? "seat-head--top" : ""}">
          <div>
            <div class="seat-name">Open Seat</div>
            <p class="seat-sub">Seat ${seatNo}</p>
          </div>
          <div class="seat-badges">
            <div class="seat-badge">Waiting</div>
          </div>
        </div>
      </div>
    `;
    return;
  }

  const badges = [];
  if (seat.is_me) badges.push(`<div class="seat-badge seat-badge--you">You</div>`);
  if (isTurn) badges.push(`<div class="seat-badge seat-badge--turn">Turn</div>`);

  if (isTopSeat) {
    mountEl.innerHTML = `
      <div class="seat-box seat-box--top ${seat.is_me ? "seat-box--me" : ""} ${isTurn ? "seat-box--turn" : ""}">
        <div class="seat-head seat-head--top">
          <div class="seat-top-main">
            <div class="seat-top-id">
              <div class="seat-name">${escapeHtml(seat.player_name)}</div>
              <p class="seat-sub">Seat ${seatNo} · ${seat.player_type === "ai" ? "AI" : "Human"}</p>
            </div>

            <div class="seat-top-meta">
              <div class="seat-count">${seat.card_count} card${seat.card_count === 1 ? "" : "s"}</div>
              <div class="seat-badges">${badges.join("")}</div>
            </div>
          </div>

          <div class="seat-top-fanwrap">
            ${renderAICardBacks(seat.card_count, true)}
          </div>
        </div>
      </div>
    `;
    return;
  }

  mountEl.innerHTML = `
    <div class="seat-box ${seat.is_me ? "seat-box--me" : ""} ${isTurn ? "seat-box--turn" : ""}">
      <div class="seat-head">
        <div>
          <div class="seat-name">${escapeHtml(seat.player_name)}</div>
          <p class="seat-sub">Seat ${seatNo} · ${seat.player_type === "ai" ? "AI" : "Human"}</p>
        </div>
        <div class="seat-badges">${badges.join("")}</div>
      </div>

      <div class="seat-meta">
        <div class="seat-count">${seat.card_count} card${seat.card_count === 1 ? "" : "s"}</div>
      </div>

      ${renderAICardBacks(seat.card_count, false)}
    </div>
  `;
}

function getTurnHint() {
  const room = latestState?.room;
  const me = latestState?.me;

  if (!room || !me) return "";
  if (room.status === "waiting") return "Waiting for host.";
  if (room.status === "finished") {
    return room.winner_seat === me.seat_no ? "You won." : "Game finished.";
  }

  const turnSeatData = room.current_turn_seat ? getSeatByNo(room.current_turn_seat) : null;

  if (room.current_turn_seat !== me.seat_no) {
    if (turnSeatData?.player_type === "ai") {
      return `${turnSeatData.player_name} is thinking...`;
    }
    return `Waiting for ${turnSeatData?.player_name || "other player"}.`;
  }

  const playable = getPlayableCards(me.hand || [], room);
  const pendingDraw = Number(room.pending_draw || 0);

  if (pendingDraw > 0) {
    if (playable.length > 0) {
      return `Stack ${room.active_card?.kind === "plus4" ? "+4" : "+2"} or pass.`;
    }
    return `No stack available. Pass and draw ${pendingDraw}.`;
  }

  if (playable.length > 0) {
    return IS_TOUCH_DEVICE
      ? `${playable.length} playable card(s). Tap to select. Tap again to play.`
      : `${playable.length} playable card(s). Double click to play.`;
  }

  return "No matching or stronger element. Pass or use +4.";
}

function renderHudAndSummary() {
  const room = latestState?.room;
  const me = latestState?.me;
  const meResult = latestState?.me_result || null;
  if (!room) return;

  const turnSeat = room.current_turn_seat;
  const turnSeatData = turnSeat ? getSeatByNo(turnSeat) : null;
  const playableCount = me ? getPlayableCards(me.hand || [], room).length : 0;
  const activeText = room.active_card ? cardText(room.active_card) : "None";
  const modeLabel = room.room_type
    ? `${room.max_players}P ${String(room.room_type).replace(/^./, (c) => c.toUpperCase())}`
    : `${room.max_players}P`;

  roomStatusValueEl.textContent = room.status;
  roomModeValueEl.textContent = modeLabel;
  turnValueEl.textContent = turnSeatData?.player_name || "-";
  meValueEl.textContent = me ? `${me.player_name} (Seat ${me.seat_no})` : "Not Joined";

  if (room.status === "finished" && meResult) {
    humanSummaryEl.innerHTML = `
      <div class="summary-pill">Result #${meResult.place}</div>
      <div class="summary-pill">EXP +${meResult.xp_awarded || 0}</div>
      <div class="summary-pill">Cards Left ${meResult.card_count ?? 0}</div>
      <div class="summary-pill">Mode ${escapeHtml(modeLabel)}</div>
      <div class="summary-pill">${escapeHtml(getTurnHint() || "Finished")}</div>
    `;
    return;
  }

  humanSummaryEl.innerHTML = `
    <div class="summary-pill">Mode ${escapeHtml(modeLabel)}</div>
    <div class="summary-pill">Pending ${room.pending_draw || 0}</div>
    <div class="summary-pill">Playable ${playableCount}</div>
    <div class="summary-pill">Active ${escapeHtml(activeText)}</div>
    <div class="summary-pill">${escapeHtml(getTurnHint() || "Ready")}</div>
  `;
}

function renderCenterTable() {
  const room = latestState?.room;
  if (!room || !tableAreaEl) return;

  const activeCard = room.active_card || null;
  const activeElement = room.active_element || getEffectiveElement(activeCard) || "None";
  const turnSeatData = room.current_turn_seat ? getSeatByNo(room.current_turn_seat) : null;
  const winnerSeatData = room.winner_seat ? getSeatByNo(room.winner_seat) : null;
  const leadSeatData = room.lead_seat ? getSeatByNo(room.lead_seat) : null;

  const centerTitle = winnerSeatData
    ? `${winnerSeatData.player_name} wins`
    : room.status === "playing"
      ? (turnSeatData?.player_name || "In play")
      : room.status;

  const helperLine = winnerSeatData
    ? `Winner: ${winnerSeatData.player_name}`
    : `Lead: ${leadSeatData?.player_name || "-"} · Turn: ${turnSeatData?.player_name || "-"}`;

  const useImage = !!(activeCard && activeCard.has_image && activeCard.image_url);
  const activeCardStyle = useImage
    ? `background-image:url('${activeCard.image_url}');`
    : `background:${getCardGradient(activeCard)};`;

  const tutorialInfo = getSoloTutorialDynamicCopy();
  const tutorialBanner = tutorialInfo
    ? `
      <div class="solo-coach">
        <div class="solo-coach__portrait">
          <div class="solo-coach__avatar">🧙</div>
          <div class="solo-coach__name">${escapeHtml(tutorialInfo.speaker)}</div>
        </div>

        <div class="solo-coach__bubble">
          <div class="solo-coach__top">
            <span class="solo-coach__badge">${escapeHtml(String(tutorialInfo.type).toUpperCase())}</span>
            <strong>${escapeHtml(tutorialInfo.title)}</strong>
          </div>

          <div class="solo-coach__objective">
            ${escapeHtml(tutorialInfo.objective)}
          </div>

          <div class="solo-coach__text">
            ${escapeHtml(tutorialInfo.explanation)}
          </div>

          <div class="solo-coach__tip">
            ${escapeHtml(tutorialInfo.tip)}
          </div>
        </div>
      </div>
    `
    : "";

  tableAreaEl.innerHTML = `
    <div class="board-center">
      <div class="center-meta">
        <div class="meta-pill">${escapeHtml(room.status.toUpperCase())}</div>
        <div class="meta-pill">Element ${escapeHtml(activeElement)}</div>
        <div class="meta-pill">Pending ${room.pending_draw || 0}</div>
        <div class="meta-pill">Passes ${room.pass_count || 0}</div>
        <div class="meta-pill meta-pill--turn">${escapeHtml(centerTitle)}</div>
      </div>

      <div class="center-play">
        <div class="deck-tutorial-row">
          <div class="stack-wrap">
            <div class="stack-label">Draw Pile</div>
            <div class="deck-stack">DECK</div>
          </div>


        </div>

        ${
          activeCard
            ? `
              <div class="active-card ${useImage ? "active-card--image" : ""}" style="${activeCardStyle}">
                <div class="active-card__top">
                  <div class="active-card__kind">${escapeHtml(activeCard.kind.toUpperCase())}</div>
                  <div class="active-card__mini">${escapeHtml(activeCard.element || activeCard.chosenElement || "Wild")}</div>
                </div>

                <div class="active-card__value">${escapeHtml(cardValueText(activeCard))}</div>
                <div class="active-card__name">${escapeHtml(cardText(activeCard))}</div>

                <div class="active-card__foot">
                  <span>${escapeHtml(activeElement)}</span>
                  <span>${escapeHtml(getEffectiveElement(activeCard) || "Unset")}</span>
                </div>
              </div>
            `
            : `
              <div class="active-card active-card--empty">
                <div class="active-card__top">
                  <div class="active-card__kind">EMPTY</div>
                  <div class="active-card__mini">Table</div>
                </div>

                <div class="active-card__value">—</div>
                <div class="active-card__name">No card on table</div>

                <div class="active-card__foot">
                  <span>Lead starts</span>
                  <span>None</span>
                </div>
              </div>
            `
        }

        <div class="center-sideinfo">
          <div class="center-sideinfo__card">
            <div class="center-sideinfo__label">Match State</div>
            <div class="center-sideinfo__value">${escapeHtml(centerTitle)}</div>
            <div class="center-sideinfo__sub">${escapeHtml(helperLine)}</div>
          </div>

          <div class="center-sideinfo__card">
            <div class="center-sideinfo__label">Hint</div>
            <div class="center-sideinfo__sub">${escapeHtml(getTurnHint() || "Shared room state")}</div>
          </div>
        </div>
      </div>

      <div class="center-statusbar">
        <div class="center-statusbar__main">${escapeHtml(helperLine)}</div>
        <div class="center-statusbar__sub">${escapeHtml(getTurnHint() || "Shared room state")}</div>
      </div>
    </div>
  `;
}

function getHandFanTransform(index, total, selected, playable) {
  const mid = (total - 1) / 2;
  const offset = index - mid;
  const rotate = offset * 3.2;
  const yBase = Math.abs(offset) * -1.2;

  let y = yBase;
  if (playable) y -= 5;
  if (selected) y -= 11;

  return `transform: translateY(${y}px) rotate(${rotate}deg); z-index:${100 + index};`;
}

async function handleCardPrimaryAction(cardId) {
  const card = getCardById(cardId);
  if (!card) return;

  const isSameSelected = selectedCardId === cardId;
  const playable = cardIsPlayable(card);

  if (IS_TOUCH_DEVICE) {
    if (isSameSelected && playable && !busy) {
      await tryPlaySelectedCard();
      return;
    }

    selectedCardId = cardId;
    pendingPlus4CardId = null;
    wildChooserEl.classList.add("hidden");

    renderHand();
    updateControls();

    if (!playable) {
      addLocalMsg(actionMsgEl, "That card does not match the same element and is not stronger.");
    } else {
      addLocalMsg(actionMsgEl, card.kind === "plus4"
        ? `Selected ${cardText(card)}. Tap again to choose element and play.`
        : `Selected ${cardText(card)}. Tap again to play.`);
    }
    return;
  }

  selectedCardId = isSameSelected ? null : cardId;
  pendingPlus4CardId = null;
  wildChooserEl.classList.add("hidden");

  renderHand();
  updateControls();

  if (selectedCardId === null) {
    addLocalMsg(actionMsgEl, "Selection cleared.");
    return;
  }

  if (!playable) {
    addLocalMsg(actionMsgEl, "That card cannot be played right now.");
  } else {
    addLocalMsg(actionMsgEl, `Selected ${cardText(card)}. Double click to play.`);
  }
}

function renderHand() {
  const room = latestState?.room;
  const me = latestState?.me;

  if (!handAreaEl) return;

  if (!me) {
    handAreaEl.innerHTML = `<div class="empty">Join the room first.</div>`;
    return;
  }

  if (!room || room.status === "waiting") {
    handAreaEl.innerHTML = `<div class="empty">Waiting room. Host can start once ready.</div>`;
    return;
  }

  const myHand = me.hand || [];
  if (!myHand.length) {
    handAreaEl.innerHTML = `<div class="empty">No cards left.</div>`;
    return;
  }

  const tutorialInfo = getSoloTutorialDynamicCopy();
  const total = myHand.length;

  const cardsHtml = myHand.map((card, index) => {
    const selected = selectedCardId === card.id;
    const playable = cardIsPlayable(card);
    const selectedClass = selected ? "selected-card" : "";
    const disabledClass = playable ? "" : "is-unplayable";

    let tutorialClass = "";
    if (tutorialInfo && playable && isSoloTrainingRoom()) {
      const target = getSoloTutorialTargetCard(myHand, room);

      if (target && target.id === card.id) {
        tutorialClass = "tutorial-target-card";
      }
    }
    const useImage = !!(card.has_image && card.image_url);
    const inlineStyle = useImage
      ? `${getHandFanTransform(index, total, selected, playable)} background-image:url('${card.image_url}');`
      : `${getHandFanTransform(index, total, selected, playable)} background:${getCardGradient(card)};`;

    return `
      <button
        type="button"
        class="hand-card ${selectedClass} ${disabledClass} ${tutorialClass}"
        data-card-id="${escapeHtml(card.id)}"
        data-playable="${playable ? "1" : "0"}"
        title="${escapeHtml(cardText(card))}"
        style="${inlineStyle}"
      >
        <div class="hand-card__top">
          <div class="hand-card__kind">${escapeHtml(card.kind.toUpperCase())}</div>
          <div class="hand-card__value">${escapeHtml(cardValueText(card))}</div>
        </div>

        <div class="hand-card__name">${escapeHtml(cardText(card))}</div>
        <div class="hand-card__meta">${escapeHtml(card.element || card.chosenElement || "Wild")}</div>
        <div class="hand-card__status">${playable ? "Playable" : "Cannot play now"}</div>
      </button>
    `;
  }).join("");

  const handCoach = tutorialInfo && isSoloTrainingRoom()
    ? `
      <div class="hand-tutorial-coach">
        <div class="solo-coach__portrait">
          <div class="solo-coach__avatar">🧙</div>
          <div class="solo-coach__name">${escapeHtml(tutorialInfo.speaker)}</div>
        </div>

        <div class="solo-coach__bubble">
          <div class="solo-coach__top">
            <span class="solo-coach__badge">${escapeHtml(String(tutorialInfo.type).toUpperCase())}</span>
            <strong>${escapeHtml(tutorialInfo.title)}</strong>
          </div>
          <div class="solo-coach__objective">${escapeHtml(tutorialInfo.objective)}</div>
          <div class="solo-coach__text">${escapeHtml(tutorialInfo.explanation)}</div>
          <div class="solo-coach__tip">${escapeHtml(tutorialInfo.tip)}</div>
        </div>
      </div>
    `
    : "";

  handAreaEl.innerHTML = `
    <div class="hand-tutorial-row">
      ${handCoach}
      <div class="hand-fan">${cardsHtml}</div>
    </div>
  `;

  handAreaEl.querySelectorAll("[data-card-id]").forEach((btn) => {
    btn.addEventListener("click", async () => {
      const cardId = btn.dataset.cardId;
      if (!cardId || busy) return;
      await handleCardPrimaryAction(cardId);
    });

    if (!IS_TOUCH_DEVICE) {
      btn.addEventListener("dblclick", async () => {
        const cardId = btn.dataset.cardId;
        if (!cardId || busy) return;

        selectedCardId = cardId;
        renderHand();
        updateControls();
        await tryPlaySelectedCard();
      });
    }
  });
}

function renderLogs() {
  const logs = latestState?.logs || [];
  if (!logAreaEl) return;

  if (!logs.length) {
    logAreaEl.innerHTML = `<div class="empty">No log entries yet.</div>`;
    return;
  }

  logAreaEl.innerHTML = [...logs]
    .reverse()
    .map((entry) => `<div class="log-entry">${escapeHtml(entry)}</div>`)
    .join("");
}

function updateModeButtons() {
  const currentMode = String(latestState?.room?.max_players || "4");
  document.querySelectorAll("[data-mode]").forEach((btn) => {
    btn.classList.toggle("selected", btn.dataset.mode === currentMode);
  });
}

function updatePanelsVisibility() {
  const room = latestState?.room;
  const isHost = !!room?.is_host;
  const isWaiting = room?.status === "waiting";

  if (hostControlsSectionEl) {
    hostControlsSectionEl.classList.toggle("hidden", !isHost);
  }

  if (rulesEditorEl) {
    rulesEditorEl.classList.toggle("hidden", !isHost || !isWaiting);
  }

  document.querySelectorAll(".mode-buttons").forEach((wrap) => {
    wrap.classList.toggle("hidden", !isHost || !isWaiting);
  });

  if (startGameBtn) {
    startGameBtn.classList.toggle("hidden", !isHost || !isWaiting);
  }

  if (resetRoomBtn) {
    resetRoomBtn.classList.toggle("hidden", !isHost);
    resetRoomBtn.disabled = busy || !isHost;
  }

  if (destroyRoomBtn) {
    destroyRoomBtn.classList.toggle("hidden", !isHost);
    destroyRoomBtn.disabled = busy || !isHost;
  }
}


function updateControls() {
  const room = latestState?.room;
  const me = latestState?.me;
  const selectedCard = selectedCardId ? getCardById(selectedCardId) : null;
  const myTurn = !!(room && me && room.status === "playing" && room.current_turn_seat === me.seat_no);
  const canPlaySelected = !!(selectedCard && cardIsPlayable(selectedCard));
  const waitingForWild = !!(
    selectedCard &&
    selectedCard.kind === "plus4" &&
    canPlaySelected &&
    pendingPlus4CardId === selectedCard.id
  );
  const hostCanEditWaitingRoom = !!(room?.is_host && room?.status === "waiting");

  if (refreshBtn) refreshBtn.disabled = busy;
  if (startGameBtn) startGameBtn.disabled = busy || !room?.is_host || room?.status !== "waiting";
  if (passBtn) passBtn.disabled = busy || !myTurn;
  if (playBtn) playBtn.disabled = busy || !myTurn || !selectedCard || !canPlaySelected || waitingForWild;
  if (saveRulesBtn) saveRulesBtn.disabled = busy || !hostCanEditWaitingRoom;

  [
    ruleAllowAiFillEl,
    ruleStartingHandSizeEl,
    ruleAllowStackPlus2El,
    ruleAllowStackPlus4El,
    ruleDrawUntilPlayableEl,
  ].forEach((el) => {
    if (el) el.disabled = busy || !hostCanEditWaitingRoom;
  });

  if (!selectedCard || selectedCard.kind !== "plus4" || !canPlaySelected) {
    wildChooserEl.classList.add("hidden");
    pendingPlus4CardId = null;
  }
}

function render() {
  renderSeat(seatTopEl, 3);
  renderSeat(seatLeftEl, 2);
  renderSeat(seatRightEl, 4);
  renderHudAndSummary();
  renderCenterTable();
  renderHand();
  renderLogs();
  updateModeButtons();
  updatePanelsVisibility();
  updateControls();
}

async function tryPlaySelectedCard() {
  const card = selectedCardId ? getCardById(selectedCardId) : null;
  if (!card) {
    addLocalMsg(actionMsgEl, "Select a card first.");
    return;
  }

  if (!cardIsPlayable(card)) {
    addLocalMsg(actionMsgEl, "That selected card cannot be played right now.");
    return;
  }

  if (card.kind === "plus4") {
    pendingPlus4CardId = card.id;
    wildChooserEl.classList.remove("hidden");
    updateControls();
    addLocalMsg(actionMsgEl, "Choose an element for +4.");
    return;
  }

  try {
    setBusy(true);
    const data = await postJson(`${BASE_PATH}/api/game/play_card.php`, {
      room_code: ROOM_CODE,
      card_id: selectedCardId
    });

    addLocalMsg(actionMsgEl, "Card played.");
    selectedCardId = null;
    pendingPlus4CardId = null;
    wildChooserEl.classList.add("hidden");

    applyState(data);
  } catch (err) {
    addLocalMsg(actionMsgEl, err.message);
  } finally {
    setBusy(false);
    updateControls();
  }
}

document.querySelectorAll("[data-mode]").forEach((btn) => {
  btn.addEventListener("click", async () => {
    if (busy) return;

    try {
      setBusy(true);
      await saveRoomSettings(Number(btn.dataset.mode || 4));
      addLocalMsg(actionMsgEl, `Room mode set to ${btn.dataset.mode} players.`);
      await fetchState();
    } catch (err) {
      addLocalMsg(actionMsgEl, err.message);
    } finally {
      setBusy(false);
      updateControls();
    }
  });
});

if (refreshBtn) {
  refreshBtn.addEventListener("click", async () => {
    try {
      setBusy(true);
      await fetchState();
      addLocalMsg(actionMsgEl, "Refreshed.");
    } catch (err) {
      addLocalMsg(actionMsgEl, err.message);
    } finally {
      setBusy(false);
      updateControls();
    }
  });
}

if (saveRulesBtn) {
  saveRulesBtn.addEventListener("click", async () => {
    try {
      setBusy(true);
      await saveRoomSettings();
      addLocalMsg(actionMsgEl, "Room rules saved.");
      await fetchState();
    } catch (err) {
      addLocalMsg(actionMsgEl, err.message);
    } finally {
      setBusy(false);
      updateControls();
    }
  });
}

if (startGameBtn) {
  startGameBtn.addEventListener("click", async () => {
    try {
      setBusy(true);
      const data = await postJson(`${BASE_PATH}/api/game/start_game.php`, {
        room_code: ROOM_CODE
      });
      addLocalMsg(actionMsgEl, "Game started.");
      selectedCardId = null;
      pendingPlus4CardId = null;
      wildChooserEl.classList.add("hidden");

      applyState(data);
    } catch (err) {
      addLocalMsg(actionMsgEl, err.message);
    } finally {
      setBusy(false);
      updateControls();
    }
  });
}

if (playBtn) {
  playBtn.addEventListener("click", async () => {
    await tryPlaySelectedCard();
  });
}

if (passBtn) {
  passBtn.addEventListener("click", async () => {
    try {
      setBusy(true);
      const data = await postJson(`${BASE_PATH}/api/game/pass_turn.php`, {
        room_code: ROOM_CODE
      });
      addLocalMsg(actionMsgEl, "Turn passed.");
      selectedCardId = null;
      pendingPlus4CardId = null;
      wildChooserEl.classList.add("hidden");

      applyState(data);
    } catch (err) {
      addLocalMsg(actionMsgEl, err.message);
    } finally {
      setBusy(false);
      updateControls();
    }
  });
}

if (resetRoomBtn) {
  resetRoomBtn.addEventListener("click", async () => {
    try {
      setBusy(true);
      const data = await postJson(`${BASE_PATH}/api/game/reset_room.php`, {
        room_code: ROOM_CODE
      });
      addLocalMsg(actionMsgEl, data.msg || "Room reset.");
      selectedCardId = null;
      pendingPlus4CardId = null;
      wildChooserEl.classList.add("hidden");

      applyState(data);
    } catch (err) {
      addLocalMsg(actionMsgEl, err.message);
    } finally {
      setBusy(false);
      updateControls();
    }
  });
}

if (leaveRoomBtn) {
  leaveRoomBtn.addEventListener("click", async () => {
    const room = latestState?.room;
    const isHost = !!room?.is_host;

    const ok = window.confirm(
      isHost
        ? "Leave room? Since you are the host, this will destroy the room."
        : "Leave this room?"
    );
    if (!ok) return;

    try {
      setBusy(true);
      const data = await postJson(`${BASE_PATH}/api/game/leave_room.php`, {
        room_code: ROOM_CODE
      });

      addLocalMsg(actionMsgEl, data.msg || "Left room.");
      window.location.href = data.redirect_url || roomBackUrl();
    } catch (err) {
      addLocalMsg(actionMsgEl, err.message);
    } finally {
      setBusy(false);
      updateControls();
    }
  });
}

if (destroyRoomBtn) {
  destroyRoomBtn.addEventListener("click", async () => {
    const ok = window.confirm("Are you sure you want to destroy this room? This cannot be undone.");
    if (!ok) return;

    try {
      setBusy(true);
      const data = await postJson(`${BASE_PATH}/api/game/destroy_room.php`, {
        room_code: ROOM_CODE
      });

      addLocalMsg(actionMsgEl, data.msg || "Room destroyed.");

      const redirectUrl = data.redirect_url || roomBackUrl();
      window.location.href = redirectUrl;
    } catch (err) {
      addLocalMsg(actionMsgEl, err.message);
    } finally {
      setBusy(false);
      updateControls();
    }
  });
}

if (wildChooserEl) {
  wildChooserEl.querySelectorAll("[data-wild-element]").forEach((btn) => {
    btn.addEventListener("click", async () => {
      const chosenElement = btn.dataset.wildElement;
      const card = pendingPlus4CardId ? getCardById(pendingPlus4CardId) : null;
      if (!card || !chosenElement) return;

      if (!cardIsPlayable(card)) {
        addLocalMsg(actionMsgEl, "That +4 cannot be played right now.");
        wildChooserEl.classList.add("hidden");
        pendingPlus4CardId = null;
        updateControls();
        return;
      }

      try {
        setBusy(true);
        const data = await postJson(`${BASE_PATH}/api/game/play_card.php`, {
          room_code: ROOM_CODE,
          card_id: pendingPlus4CardId,
          chosen_element: chosenElement,
        });
        addLocalMsg(actionMsgEl, `+4 played as ${chosenElement}.`);
        selectedCardId = null;
        pendingPlus4CardId = null;
        wildChooserEl.classList.add("hidden");

        applyState(data);
      } catch (err) {
        addLocalMsg(actionMsgEl, err.message);
      } finally {
        setBusy(false);
        updateControls();
      }
    });
  });
}

if (toolsToggleBtn) {
  toolsToggleBtn.addEventListener("click", () => {
    toolsPanelEl.classList.toggle("hidden");
  });
}

if (logToggleBtn) {
  logToggleBtn.addEventListener("click", () => {
    logPanelEl.classList.toggle("hidden");
  });
}

if (toolsCloseBtn) {
  toolsCloseBtn.addEventListener("click", () => {
    toolsPanelEl.classList.add("hidden");
  });
}

if (logCloseBtn) {
  logCloseBtn.addEventListener("click", () => {
    logPanelEl.classList.add("hidden");
  });
}

document.addEventListener("keydown", (e) => {
  if (busy) return;

  if (e.key.toLowerCase() === "r") {
    refreshBtn?.click();
  }

  if (e.key === " " || e.key === "Spacebar") {
    if (passBtn && !passBtn.disabled) {
      e.preventDefault();
      passBtn.click();
    }
  }

  if (e.key === "Enter") {
    if (!playBtn.disabled) {
      e.preventDefault();
      playBtn.click();
    }
  }

  if (e.key === "Escape") {
    selectedCardId = null;
    pendingPlus4CardId = null;
    wildChooserEl.classList.add("hidden");
    renderHand();
    updateControls();
    addLocalMsg(actionMsgEl, "Selection cleared.");
  }
});

setInterval(() => {
  if (busy) return;
  fetchState()
    .then(() => updateControls())
    .catch(() => {});
}, 1000);

topnavSnapshot = getTopnavSnapshot();
resultsModalShown = sessionStorage.getItem(`logia_results_shown_${ROOM_CODE}`) === "1";
ensureResultsModal();
ensureTrainingTransitionModal();

fetchState()
  .then(() => {
    updateControls();
    toolsPanelEl?.classList.remove("hidden");
  })
  .catch((err) => {
    addLocalMsg(actionMsgEl, err.message);
  });