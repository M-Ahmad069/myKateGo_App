/**
 * Shared FitGo AI chat UI (dashboard widget + coach page).
 */
(function (global) {
  'use strict';

  function csrfToken() {
    return document.querySelector('meta[name="csrf-token"]')?.content || '';
  }

  function appendMessage(messagesEl, role, text, options) {
    const opts = options || {};
    const isUser = role === 'user';
    const row = document.createElement('div');
    row.className = 'fitgo-ai-row fitgo-ai-row-' + (isUser ? 'user' : 'assistant');

    if (!isUser && opts.showAvatar) {
      const avatar = document.createElement('div');
      avatar.className = 'fitgo-ai-avatar';
      avatar.setAttribute('aria-hidden', 'true');
      avatar.textContent = 'AI';
      row.appendChild(avatar);
    }

    const bubble = document.createElement('div');
    bubble.className = 'fitgo-ai-bubble fitgo-ai-bubble-' + (isUser ? 'user' : 'assistant');
    bubble.textContent = text || '';
    row.appendChild(bubble);
    messagesEl.appendChild(row);
    messagesEl.scrollTop = messagesEl.scrollHeight;
  }

  function renderChips(container, list, onPick) {
    if (!container) return;
    container.innerHTML = '';
    (list || []).forEach(function (label) {
      const btn = document.createElement('button');
      btn.type = 'button';
      btn.className = 'coach-chip';
      btn.textContent = label;
      btn.addEventListener('click', function () {
        if (typeof onPick === 'function') onPick(label);
      });
      container.appendChild(btn);
    });
  }

  function setTyping(typingEl, on) {
    if (typingEl) typingEl.style.display = on ? 'block' : 'none';
  }

  async function loadHistory(cfg) {
    if (!cfg.historyUrl || !cfg.messagesEl) return;
    try {
      const res = await fetch(cfg.historyUrl, {
        credentials: 'same-origin',
        headers: { Accept: 'application/json' },
      });
      const data = await res.json().catch(function () {
        return {};
      });
      cfg.messagesEl.innerHTML = '';
      const messages = data.messages || [];
      if (!messages.length && cfg.emptyHint) {
        const hint = document.createElement('div');
        hint.className = 'fitgo-ai-empty';
        hint.textContent = cfg.emptyHint;
        cfg.messagesEl.appendChild(hint);
        return;
      }
      messages.forEach(function (m) {
        if (m.role === 'user' || m.role === 'assistant') {
          appendMessage(cfg.messagesEl, m.role, m.content || '', {
            showAvatar: cfg.showAvatar,
          });
        }
      });
    } catch (e) {
      cfg.messagesEl.innerHTML =
        '<div class="fitgo-ai-empty">Could not load conversation history.</div>';
    }
  }

  async function postMessage(cfg, text) {
    const res = await fetch(cfg.chatUrl, {
      method: 'POST',
      credentials: 'same-origin',
      headers: {
        'Content-Type': 'application/json',
        Accept: 'application/json',
        'X-CSRF-TOKEN': csrfToken(),
        'X-Requested-With': 'XMLHttpRequest',
      },
      body: JSON.stringify({ message: text }),
    });
    return res.json().catch(function () {
      return {};
    });
  }

  /**
   * @param {object} cfg
   * @returns {{loadHistory: Function, send: Function}}
   */
  function init(cfg) {
    const defaultChips = cfg.initialChips || [
      'Hello',
      'What should I eat today?',
      'I hit a plateau',
    ];

    function clearEmptyHint() {
      const empty = cfg.messagesEl?.querySelector('.fitgo-ai-empty');
      if (empty) empty.remove();
    }

    async function send(text) {
      const msg = (text || '').trim();
      if (!msg) return;

      clearEmptyHint();
      appendMessage(cfg.messagesEl, 'user', msg, { showAvatar: cfg.showAvatar });
      setTyping(cfg.typingEl, true);

      try {
        const data = await postMessage(cfg, msg);
        setTyping(cfg.typingEl, false);
        appendMessage(cfg.messagesEl, 'assistant', data.reply || 'Something went wrong — try again.', {
          showAvatar: cfg.showAvatar,
        });
        if (cfg.chipsEl) {
          renderChips(cfg.chipsEl, data.chips?.length ? data.chips : defaultChips, function (chip) {
            if (cfg.inputEl) cfg.inputEl.value = chip;
            if (cfg.formEl) {
              cfg.formEl.dispatchEvent(new Event('submit', { cancelable: true, bubbles: true }));
            } else {
              send(chip);
            }
          });
        }
        if (typeof cfg.onReply === 'function') cfg.onReply(data);
      } catch (err) {
        setTyping(cfg.typingEl, false);
        appendMessage(cfg.messagesEl, 'assistant', 'Network issue. Check your connection and retry.', {
          showAvatar: cfg.showAvatar,
        });
      }
    }

    if (cfg.chipsEl) {
      renderChips(cfg.chipsEl, defaultChips, function (chip) {
        if (cfg.inputEl) cfg.inputEl.value = chip;
        if (cfg.formEl) {
          cfg.formEl.dispatchEvent(new Event('submit', { cancelable: true, bubbles: true }));
        } else {
          send(chip);
        }
      });
    }

    if (cfg.formEl) {
      cfg.formEl.addEventListener('submit', function (e) {
        e.preventDefault();
        const msg = (cfg.inputEl?.value || '').trim();
        if (cfg.inputEl) cfg.inputEl.value = '';
        send(msg);
      });
    }

    if (cfg.loadOnInit !== false && cfg.historyUrl) {
      loadHistory(cfg);
    }

    return {
      loadHistory: function () {
        return loadHistory(cfg);
      },
      send: send,
    };
  }

  global.FitGoAiChat = {
    init: init,
    appendMessage: appendMessage,
    loadHistory: loadHistory,
  };
})(window);
