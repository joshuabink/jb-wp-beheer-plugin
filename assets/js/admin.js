/* JB WP Beheer Plugin — v4.3.0 */

/* global DWMCD, wp */
window.DWMCD = window.DWMCD || { typeHints: {}, typePlaceholders: {}, noTargetTypes: [], ajaxurl: '', nonce: '' };

(function () {
  'use strict';

  // ── Helpers ────────────────────────────────────────────────────────────────

  function isValidHex(v) { return /^#[0-9a-fA-F]{6}$/.test(v); }

  function hexToRgb(hex) {
    return {
      r: parseInt(hex.slice(1, 3), 16),
      g: parseInt(hex.slice(3, 5), 16),
      b: parseInt(hex.slice(5, 7), 16),
    };
  }

  function previewUrl(type, target) {
    if (!target && (type === 'new_post_type' || type === 'list_post_type')) target = 'post';
    switch (type) {
      case 'new_post_type':  return '/wp-admin/post-new.php?post_type=' + target;
      case 'list_post_type': return target === 'post' ? '/wp-admin/edit.php' : '/wp-admin/edit.php?post_type=' + target;
      case 'media_upload':   return '/wp-admin/media-new.php';
      case 'media_library':  return '/wp-admin/upload.php';
      case 'site_url':       return '/';
      case 'admin_url':      return target ? '/wp-admin/' + target.replace(/^\/+/, '') : 'Nog niet compleet';
      case 'external_url':   return target || 'Nog niet compleet';
      default:               return 'Nog niet compleet';
    }
  }

  // ── Tabs ───────────────────────────────────────────────────────────────────

  var TAB_KEY = 'dwmcd_active_tab';

  function switchTab(id) {
    document.querySelectorAll('.dwmcd-tab-btn').forEach(function (btn) {
      var isActive = btn.dataset.tab === id;
      btn.classList.toggle('dwmcd-tab-active', isActive);
      btn.setAttribute('aria-selected', isActive ? 'true' : 'false');
      btn.setAttribute('tabindex', isActive ? '0' : '-1');
    });
    document.querySelectorAll('[data-tab-panel]').forEach(function (panel) {
      var isActive = panel.dataset.tabPanel === id;
      panel.classList.toggle('hidden', !isActive);
      panel.setAttribute('tabindex', isActive ? '0' : '-1');
    });
    try { localStorage.setItem(TAB_KEY, id); } catch (e) { /* ignore */ }
  }

  var tabBtns = document.querySelectorAll('.dwmcd-tab-btn');
  tabBtns.forEach(function (btn, idx) {
    btn.addEventListener('click', function () { switchTab(btn.dataset.tab); });
    // Arrow key navigation for tabs (WAI-ARIA tabs pattern)
    btn.addEventListener('keydown', function (e) {
      var tabs = Array.prototype.slice.call(tabBtns);
      var dir  = 0;
      if (e.key === 'ArrowRight' || e.key === 'ArrowDown') dir = 1;
      if (e.key === 'ArrowLeft'  || e.key === 'ArrowUp')   dir = -1;
      if (dir === 0) return;
      e.preventDefault();
      var next = (idx + dir + tabs.length) % tabs.length;
      tabs[next].focus();
      switchTab(tabs[next].dataset.tab);
    });
  });

  var savedTab = 'dashboard';
  try { savedTab = localStorage.getItem(TAB_KEY) || 'dashboard'; } catch (e) { /* ignore */ }
  var validTab = document.querySelector('[data-tab-panel="' + savedTab + '"]');
  switchTab(validTab ? savedTab : 'dashboard');

  // ── Save notice auto-dismiss ────────────────────────────────────────────────
  var saveNotice = document.getElementById('dwmcd-save-notice');
  if (saveNotice) {
    setTimeout(function () { if (saveNotice.parentNode) saveNotice.parentNode.removeChild(saveNotice); }, 3500);
  }

  // ── Quick Actions ──────────────────────────────────────────────────────────

  var grid      = document.getElementById('dwmcd-actions-grid');
  var addButton = document.getElementById('dwmcd-add-action');
  var tpl       = document.getElementById('dwmcd-action-template');
  var emptyBox  = document.getElementById('dwmcd-actions-empty');

  function updateEmptyState() {
    if (!grid || !emptyBox) return;
    emptyBox.classList.toggle('hidden', grid.querySelectorAll('[data-action-card]').length > 0);
  }

  function renumberCards() {
    if (!grid) return;
    grid.querySelectorAll('[data-action-card]').forEach(function (card, idx) {
      card.querySelectorAll('input, select, textarea').forEach(function (field) {
        if (field.name) {
          field.name = field.name.replace(
            /dwmcd_settings\[quick_actions\]\[(.*?)\]/,
            'dwmcd_settings[quick_actions][' + idx + ']'
          );
        }
      });
    });
  }

  function updateCardPreviews(card) {
    var titleInput  = card.querySelector('[data-live-title]');
    var descInput   = card.querySelector('[data-live-description]');
    var iconSelect  = card.querySelector('[data-live-icon]');
    var typeSelect  = card.querySelector('[data-action-type]');
    var targetInput = card.querySelector('[data-action-target]');
    var titlePreview = card.querySelector('.dwmcd-action-title-preview');
    var descPreview  = card.querySelector('.dwmcd-action-desc-preview');
    var headerIcon   = card.querySelector('.dwmcd-action-icon-preview');
    var inlineIcon   = card.querySelector('.dwmcd-icon-inline-preview');
    var urlPreview   = card.querySelector('[data-url-preview]');
    var targetField  = card.querySelector('.dwmcd-target-field');
    var typeHint     = card.querySelector('[data-type-hint]');

    var title  = titleInput  ? titleInput.value.trim()  : '';
    var desc   = descInput   ? descInput.value.trim()   : '';
    var icon   = iconSelect  ? iconSelect.value         : 'admin-links';
    var type   = typeSelect  ? typeSelect.value         : 'admin_url';
    var target = targetInput ? targetInput.value.trim() : '';

    if (titlePreview) titlePreview.textContent = title || 'Nieuwe knop';
    if (descPreview)  descPreview.textContent  = desc  || '\u2014';

    var iconClass = 'dashicons dashicons-' + icon;
    if (headerIcon) headerIcon.className = iconClass + ' dwmcd-action-icon-preview';
    if (inlineIcon) inlineIcon.className = iconClass + ' dwmcd-icon-inline-preview';

    var noTarget = (DWMCD.noTargetTypes || []).indexOf(type) !== -1;
    if (targetField) targetField.style.display = noTarget ? 'none' : '';
    if (typeHint)    typeHint.textContent = (DWMCD.typeHints || {})[type] || '';
    if (urlPreview)  urlPreview.textContent = previewUrl(type, noTarget ? '' : target);
    // Update placeholder based on type
    if (targetInput) targetInput.placeholder = (DWMCD.typePlaceholders || {})[type] || '';
  }

  function bindCard(card) {
    var header = card.querySelector('[data-action-toggle]');
    if (header) {
      header.addEventListener('click', function (e) {
        if (e.target.closest('[data-move]') || e.target.closest('[data-remove-action]')) return;
        card.classList.toggle('dwmcd-action-open');
      });
    }
    card.querySelectorAll('[data-live-title],[data-live-description],[data-live-icon],[data-action-type],[data-action-target]')
      .forEach(function (el) {
        el.addEventListener('input',  function () { updateCardPreviews(card); });
        el.addEventListener('change', function () { updateCardPreviews(card); });
      });
    updateCardPreviews(card);
  }

  if (grid) {
    grid.querySelectorAll('[data-action-card]').forEach(bindCard);

    grid.addEventListener('click', function (e) {
      var removeBtn = e.target.closest('[data-remove-action]');
      if (removeBtn) {
        var card = removeBtn.closest('[data-action-card]');
        if (card) { card.remove(); renumberCards(); updateEmptyState(); }
        return;
      }
      var moveBtn = e.target.closest('[data-move]');
      if (moveBtn) {
        var card = moveBtn.closest('[data-action-card]');
        if (!card) return;
        var dir = moveBtn.dataset.move;
        if (dir === 'up'   && card.previousElementSibling) grid.insertBefore(card, card.previousElementSibling);
        if (dir === 'down' && card.nextElementSibling)     grid.insertBefore(card.nextElementSibling, card);
        renumberCards();
      }
    });
  }

  // Create a new action card, optionally pre-filled from a preset
  function addActionCard(preset) {
    if (!grid || !tpl) return;
    var count = grid.querySelectorAll('[data-action-card]').length;
    var html  = tpl.innerHTML.replace(/__INDEX__/g, count);
    var wrap  = document.createElement('div');
    wrap.innerHTML = html.trim();
    var card  = wrap.firstElementChild;
    if (!card) return;
    card.classList.add('dwmcd-action-open');

    // Pre-fill fields if preset provided
    if (preset) {
      var titleEl  = card.querySelector('[data-live-title]');
      var descEl   = card.querySelector('[data-live-description]');
      var iconEl   = card.querySelector('[data-live-icon]');
      var typeEl   = card.querySelector('[data-action-type]');
      var targetEl = card.querySelector('[data-action-target]');
      var capEl    = card.querySelector('select[name*="[capability]"]');
      if (titleEl)  titleEl.value  = preset.title || '';
      if (descEl)   descEl.value   = preset.description || '';
      if (iconEl)   iconEl.value   = preset.icon || 'admin-links';
      if (typeEl)   typeEl.value   = preset.action_type || 'admin_url';
      if (targetEl) targetEl.value = preset.target || '';
      if (capEl)    capEl.value    = preset.capability || 'read';
    }

    grid.appendChild(card);
    bindCard(card);
    renumberCards();
    updateEmptyState();
    card.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
  }

  if (addButton && grid && tpl) {
    addButton.addEventListener('click', function () { addActionCard(null); });
  }

  // Preset select — add pre-filled action cards
  var presetSelect = document.getElementById('dwmcd-preset-select');
  if (presetSelect && grid && tpl) {
    presetSelect.addEventListener('change', function () {
      var key = presetSelect.value;
      if (!key) return;
      var presets = (DWMCD && DWMCD.presets) ? DWMCD.presets : {};
      var preset  = presets[key];
      if (!preset) return;
      addActionCard(preset);
      presetSelect.value = ''; // reset dropdown
    });
  }

  // ── Logo uploader ──────────────────────────────────────────────────────────

  function bindMediaUploader(selectBtn, removeBtn, idInput, preview, isIcon) {
    if (!selectBtn) return;

    selectBtn.addEventListener('click', function () {
      if (typeof wp === 'undefined' || !wp.media) {
        alert('De WordPress mediabibliotheek kon niet worden geladen. Herlaad de pagina en probeer opnieuw.');
        return;
      }
      var frame = wp.media({
        title:    isIcon ? 'Icoon selecteren' : 'Logo selecteren',
        button:   { text: isIcon ? 'Dit icoon gebruiken' : 'Dit logo gebruiken' },
        multiple: false,
        library:  { type: 'image' },
      });
      frame.on('select', function () {
        var attachment = frame.state().get('selection').first().toJSON();
        if (idInput) idInput.value = attachment.id;
        if (preview) {
          var imgUrl = (attachment.sizes && attachment.sizes.medium)
            ? attachment.sizes.medium.url
            : attachment.url;
          preview.innerHTML = '<img src="' + imgUrl + '" alt="Preview">';
          preview.classList.remove('dwmcd-logo-empty');
        }
        if (removeBtn) removeBtn.classList.remove('hidden');
      });
      frame.open();
    });

    if (removeBtn && idInput && preview) {
      removeBtn.addEventListener('click', function () {
        idInput.value = '0';
        var icon = isIcon ? 'dashicons-wordpress' : 'dashicons-format-image';
        var text = isIcon ? 'Geen icoon geselecteerd' : 'Geen logo geselecteerd';
        preview.innerHTML = '<span class="dashicons ' + icon + '"></span><span>' + text + '</span>';
        preview.classList.add('dwmcd-logo-empty');
        removeBtn.classList.add('hidden');
      });
    }
  }

  bindMediaUploader(
    document.getElementById('dwmcd-logo-select'),
    document.getElementById('dwmcd-logo-remove'),
    document.getElementById('dwmcd-logo-id'),
    document.getElementById('dwmcd-logo-preview'),
    false
  );

  bindMediaUploader(
    document.getElementById('dwmcd-icon-select'),
    document.getElementById('dwmcd-icon-remove'),
    document.getElementById('dwmcd-icon-id'),
    document.getElementById('dwmcd-icon-preview'),
    true
  );

  // ── Login background uploader ─────────────────────────────────────────────

  (function () {
    var selectBtn = document.getElementById('dwmcd-login-bg-select');
    var removeBtn = document.getElementById('dwmcd-login-bg-remove');
    var idInput   = document.getElementById('dwmcd-login-bg-id');
    var preview   = document.getElementById('dwmcd-login-bg-preview');
    if (!selectBtn) return;

    selectBtn.addEventListener('click', function () {
      if (typeof wp === 'undefined' || !wp.media) return;
      var frame = wp.media({
        title:    'Achtergrondafbeelding kiezen',
        button:   { text: 'Gebruiken als achtergrond' },
        multiple: false,
        library:  { type: 'image' },
      });
      frame.on('select', function () {
        var att = frame.state().get('selection').first().toJSON();
        idInput.value = att.id;
        var url = (att.sizes && att.sizes.large) ? att.sizes.large.url : att.url;
        preview.innerHTML = '<img src="' + url + '" alt="Login achtergrond" style="width:100%;height:100%;object-fit:cover">';
        preview.classList.remove('dwmcd-logo-empty');
        removeBtn.classList.remove('hidden');
      });
      frame.open();
    });

    if (removeBtn) {
      removeBtn.addEventListener('click', function () {
        idInput.value = '0';
        preview.innerHTML = '<span class="dashicons dashicons-format-image"></span><span>Geen achtergrond geselecteerd</span>';
        preview.classList.add('dwmcd-logo-empty');
        removeBtn.classList.add('hidden');
      });
    }
  })();

  // ── Color pickers ──────────────────────────────────────────────────────────

  var accentColorInput  = document.getElementById('dwmcd-accent-color');
  var accentHexInput    = document.getElementById('dwmcd-accent-hex');
  var sidebarColorInput = document.getElementById('dwmcd-sidebar-bg');
  var sidebarHexInput   = document.getElementById('dwmcd-sidebar-hex');

  function applyAccent(hex) {
    var root = document.documentElement;
    root.style.setProperty('--dwmcd-accent', hex);
    var rgb = hexToRgb(hex);
    root.style.setProperty('--dwmcd-accent-soft', 'rgba(' + rgb.r + ',' + rgb.g + ',' + rgb.b + ',0.09)');
  }

  function applySidebarBg(hex) {
    var root  = document.documentElement;
    root.style.setProperty('--dwmcd-sidebar-bg', hex);
    var rgb   = hexToRgb(hex);
    var lum   = (rgb.r * 299 + rgb.g * 587 + rgb.b * 114) / 1000;
    var text  = lum > 140 ? '#35435d' : '#e8eef8';
    var muted = lum > 140 ? '#8a9ab5' : '#a0aec0';
    root.style.setProperty('--dwmcd-sidebar-text', text);
    root.style.setProperty('--dwmcd-sidebar-muted', muted);
    var styleId = 'dwmcd-live-sidebar';
    var s = document.getElementById(styleId);
    if (!s) { s = document.createElement('style'); s.id = styleId; document.head.appendChild(s); }
    s.textContent =
      '#adminmenuwrap,#adminmenuback,#adminmenu,#adminmenu .wp-submenu{background:' + hex + ' !important;}' +
      '#wpadminbar{background:' + hex + ' !important;}' +
      '#adminmenu a,#adminmenu .wp-menu-name,#adminmenu div.wp-menu-image:before,' +
      '#wpadminbar .ab-item,#wpadminbar a.ab-item,#wpadminbar .ab-icon:before,#wpadminbar .ab-label' +
      '{color:' + text + ' !important;}' +
      '#adminmenu .wp-submenu a{color:' + muted + ' !important;}';
  }

  function bindColorPair(colorEl, hexEl, applyFn) {
    if (!colorEl || !hexEl) return;
    colorEl.addEventListener('input', function () {
      hexEl.value = colorEl.value;
      applyFn(colorEl.value);
    });
    hexEl.addEventListener('input', function () {
      var val = hexEl.value;
      if (!val.startsWith('#')) val = '#' + val;
      if (isValidHex(val)) { colorEl.value = val; applyFn(val); }
    });
  }

  bindColorPair(accentColorInput, accentHexInput, applyAccent);
  bindColorPair(sidebarColorInput, sidebarHexInput, applySidebarBg);

  var resetBtn = document.getElementById('dwmcd-reset-colors');
  if (resetBtn) {
    resetBtn.addEventListener('click', function () {
      var da = '#2952ff', ds = '#ffffff';
      if (accentColorInput)  accentColorInput.value  = da;
      if (accentHexInput)    accentHexInput.value    = da;
      if (sidebarColorInput) sidebarColorInput.value = ds;
      if (sidebarHexInput)   sidebarHexInput.value   = ds;
      applyAccent(da);
      applySidebarBg(ds);
    });
  }

  // ── Menu-organizer: folder/chip drag-and-drop ──────────────────────────────

  var groupsArea    = document.getElementById('dwmcd-groups-area');
  var menuDataField = document.getElementById('dwmcd-menu-data');
  var menuReset     = document.getElementById('dwmcd-menu-reset');
  var menuFlag      = document.getElementById('dwmcd-menu-order-reset');
  var addGroupBtn   = document.getElementById('dwmcd-add-group');
  var settingsForm  = document.getElementById('dwmcd-settings-form');

  var draggedChip  = null;
  var draggedGroup = null;
  var groupCounter = Date.now();

  function escHtml(str) {
    return String(str)
      .replace(/&/g, '&amp;')
      .replace(/"/g, '&quot;')
      .replace(/</g, '&lt;')
      .replace(/>/g, '&gt;');
  }

  // Serialize current UI state to JSON
  function serializeMenuState() {
    var groups = [];
    var items  = [];

    if (!groupsArea) return { groups: groups, items: items };

    groupsArea.querySelectorAll('.dwmcd-menu-group').forEach(function (groupEl) {
      var gid = groupEl.dataset.groupId || '';

      if (gid) {
        var nameInput = groupEl.querySelector('.dwmcd-group-name');
        groups.push({
          id:   gid,
          name: nameInput ? nameInput.value.trim() : '',
        });
      }

      groupEl.querySelectorAll('.dwmcd-menu-chip').forEach(function (chip) {
        var visBtn     = chip.querySelector('.dwmcd-chip-visibility');
        var labelInput = chip.querySelector('.dwmcd-chip-custom-label');
        items.push({
          slug:         chip.dataset.slug || '',
          visible:      visBtn ? (visBtn.dataset.visible === '1' ? 1 : 0) : 1,
          custom_label: labelInput ? labelInput.value.trim() : '',
          group:        gid,
        });
      });
    });

    return { groups: groups, items: items };
  }

  // Keep hidden menu_data field in sync with the UI *at all times*, not just
  // on submit. This is defense in depth — if for any reason the submit
  // listener doesn't fire (browser autofill, form.submit() called directly,
  // another script hijacking submit), the value is already populated from
  // the last user interaction.
  function syncMenuData() {
    if (!menuDataField) return;
    try {
      menuDataField.value = JSON.stringify(serializeMenuState());
    } catch (e) { /* ignore */ }
  }

  // Populate immediately so a save without any interaction still ships the
  // current (default) order — so at the very least "menu_order" in the DB
  // gets populated and the filter can observe it.
  if (groupsArea && menuDataField) {
    syncMenuData();
  }

  // Also sync on every submit as a final safety net.
  if (settingsForm && menuDataField) {
    settingsForm.addEventListener('submit', syncMenuData);
  }

  // Chip visibility toggle
  function bindChipVisibility(chip) {
    var btn = chip.querySelector('.dwmcd-chip-visibility');
    if (!btn) return;
    btn.addEventListener('click', function (e) {
      e.stopPropagation();
      var visible = btn.dataset.visible === '1' ? 0 : 1;
      btn.dataset.visible = visible ? '1' : '0';
      var label = visible ? 'Zichtbaar \u2014 klik om te verbergen' : 'Verborgen \u2014 klik om te tonen';
      btn.title = label;
      btn.setAttribute('aria-label', label);
      var icon = btn.querySelector('.dashicons');
      if (icon) {
        icon.className = 'dashicons ' + (visible ? 'dashicons-visibility' : 'dashicons-hidden');
      }
      chip.classList.toggle('is-hidden', !visible);
    });
  }

  // Create a new group element
  function createGroup(id, name) {
    var el = document.createElement('div');
    el.className = 'dwmcd-menu-group';
    el.dataset.groupId = id;
    el.innerHTML =
      '<div class="dwmcd-group-header" draggable="true">' +
        '<span class="dwmcd-group-drag dashicons dashicons-move" title="Groep verslepen"></span>' +
        '<span class="dashicons dashicons-category" style="color:var(--dwmcd-accent);font-size:16px;width:16px;height:16px"></span>' +
        '<input type="text" class="dwmcd-group-name" value="' + escHtml(name) + '" placeholder="Groepsnaam...">' +
        '<button type="button" class="dwmcd-btn-icon dwmcd-group-delete" title="Groep verwijderen" aria-label="Groep verwijderen"><span class="dashicons dashicons-trash"></span></button>' +
      '</div>' +
      '<div class="dwmcd-group-items" data-group-drop>' +
        '<div class="dwmcd-drop-placeholder"></div>' +
      '</div>';
    return el;
  }

  // Insert chip before placeholder (or append)
  function insertChipInZone(zone, chip) {
    var placeholder = zone.querySelector('.dwmcd-drop-placeholder');
    if (placeholder) {
      zone.insertBefore(chip, placeholder);
    } else {
      zone.appendChild(chip);
    }
  }

  // Get position-based drop target inside a chip zone
  function getChipDropTarget(zone, clientY) {
    var chips = zone.querySelectorAll('.dwmcd-menu-chip');
    for (var i = 0; i < chips.length; i++) {
      if (chips[i] === draggedChip) continue;
      var rect = chips[i].getBoundingClientRect();
      if (clientY < rect.top + rect.height / 2) {
        return chips[i];
      }
    }
    return null;
  }

  // Get position-based drop target for group reordering
  function getGroupDropTarget(clientY) {
    var groups = groupsArea.querySelectorAll('.dwmcd-menu-group');
    for (var i = 0; i < groups.length; i++) {
      if (groups[i] === draggedGroup) continue;
      var rect = groups[i].getBoundingClientRect();
      if (clientY < rect.top + rect.height / 2) {
        return groups[i];
      }
    }
    return null;
  }

  // Drag-and-drop on groups area
  if (groupsArea) {

    groupsArea.addEventListener('dragstart', function (e) {
      // Group header drag (reorder groups)
      var header = e.target.closest('.dwmcd-group-header');
      if (header) {
        // Don't drag when clicking on input/button inside header
        if (e.target.tagName === 'INPUT' || e.target.tagName === 'BUTTON') {
          e.preventDefault();
          return;
        }
        draggedGroup = header.closest('.dwmcd-menu-group');
        if (draggedGroup) {
          draggedGroup.classList.add('group-dragging');
          e.dataTransfer.effectAllowed = 'move';
          e.dataTransfer.setData('text/plain', draggedGroup.dataset.groupId || '');
        }
        return;
      }

      // Chip drag (move chip between groups)
      var chip = e.target.closest('.dwmcd-menu-chip');
      if (!chip) { e.preventDefault(); return; }
      if (e.target.tagName === 'INPUT' || e.target.tagName === 'BUTTON') { e.preventDefault(); return; }
      draggedChip = chip;
      chip.classList.add('is-dragging');
      e.dataTransfer.effectAllowed = 'move';
      e.dataTransfer.setData('text/plain', chip.dataset.slug || '');
    });

    groupsArea.addEventListener('dragend', function () {
      if (draggedGroup) {
        draggedGroup.classList.remove('group-dragging');
        draggedGroup = null;
      }
      if (draggedChip) {
        draggedChip.classList.remove('is-dragging');
        draggedChip = null;
      }
      groupsArea.querySelectorAll('.drag-over, .group-drag-over').forEach(function (el) {
        el.classList.remove('drag-over', 'group-drag-over');
      });
    });

    groupsArea.addEventListener('dragover', function (e) {
      // Group reorder drag-over
      if (draggedGroup) {
        e.preventDefault();
        e.dataTransfer.dropEffect = 'move';
        var target = e.target.closest('.dwmcd-menu-group');
        groupsArea.querySelectorAll('.dwmcd-menu-group').forEach(function (g) {
          g.classList.toggle('group-drag-over', g === target && g !== draggedGroup);
        });
        return;
      }

      // Chip drag-over
      var zone = e.target.closest('[data-group-drop]');
      if (!zone || !draggedChip) return;
      e.preventDefault();
      e.dataTransfer.dropEffect = 'move';
      groupsArea.querySelectorAll('[data-group-drop]').forEach(function (z) {
        z.classList.toggle('drag-over', z === zone);
      });
    });

    groupsArea.addEventListener('dragleave', function (e) {
      if (draggedGroup) {
        var target = e.target.closest('.dwmcd-menu-group');
        if (target && !target.contains(e.relatedTarget)) {
          target.classList.remove('group-drag-over');
        }
        return;
      }
      var zone = e.target.closest('[data-group-drop]');
      if (zone && !zone.contains(e.relatedTarget)) {
        zone.classList.remove('drag-over');
      }
    });

    groupsArea.addEventListener('drop', function (e) {
      e.preventDefault();

      // Group reorder drop
      if (draggedGroup) {
        groupsArea.querySelectorAll('.dwmcd-menu-group').forEach(function (g) {
          g.classList.remove('group-drag-over');
        });
        var dropTarget = getGroupDropTarget(e.clientY);
        if (dropTarget) {
          groupsArea.insertBefore(draggedGroup, dropTarget);
        } else {
          // Drop at end — insert before ungrouped bucket if it exists
          var ungrouped = groupsArea.querySelector('.dwmcd-group-ungrouped');
          if (ungrouped && draggedGroup !== ungrouped) {
            groupsArea.insertBefore(draggedGroup, ungrouped);
          } else {
            groupsArea.appendChild(draggedGroup);
          }
        }
        draggedGroup.classList.remove('group-dragging');
        draggedGroup = null;
        return;
      }

      // Chip drop
      var zone = e.target.closest('[data-group-drop]');
      if (!zone || !draggedChip) return;
      zone.classList.remove('drag-over');

      var dropTarget = getChipDropTarget(zone, e.clientY);
      if (dropTarget) {
        zone.insertBefore(draggedChip, dropTarget);
      } else {
        insertChipInZone(zone, draggedChip);
      }

      draggedChip.classList.remove('is-dragging');
      draggedChip = null;
    });

    // Bind existing chips
    groupsArea.querySelectorAll('.dwmcd-menu-chip').forEach(bindChipVisibility);

    // Chip keyboard move (up/down buttons — accessibility alternative to drag)
    groupsArea.addEventListener('click', function (e) {
      var moveBtn = e.target.closest('[data-chip-move]');
      if (moveBtn) {
        var chip = moveBtn.closest('.dwmcd-menu-chip');
        if (!chip) return;
        var dir  = moveBtn.dataset.chipMove;
        var zone = chip.parentNode;
        if (dir === 'up') {
          var prev = chip.previousElementSibling;
          if (prev && prev.classList.contains('dwmcd-menu-chip')) {
            zone.insertBefore(chip, prev);
          }
        } else if (dir === 'down') {
          var next = chip.nextElementSibling;
          if (next && next.classList.contains('dwmcd-menu-chip')) {
            zone.insertBefore(next, chip);
          }
        }
        moveBtn.focus();
        return;
      }

      var deleteBtn = e.target.closest('.dwmcd-group-delete');
      if (!deleteBtn) return;

      var groupEl = deleteBtn.closest('.dwmcd-menu-group');
      if (!groupEl) return;

      // Move all chips in this group to the ungrouped bucket
      var ungrouped = groupsArea.querySelector('.dwmcd-group-ungrouped [data-group-drop]');
      if (ungrouped) {
        groupEl.querySelectorAll('.dwmcd-menu-chip').forEach(function (chip) {
          insertChipInZone(ungrouped, chip);
        });
      }
      groupEl.remove();
    });
  }

  // Add group button
  if (addGroupBtn && groupsArea) {
    addGroupBtn.addEventListener('click', function () {
      var id     = 'g' + (++groupCounter);
      var newGrp = createGroup(id, '');
      // Insert before the ungrouped bucket
      var ungrouped = groupsArea.querySelector('.dwmcd-group-ungrouped');
      if (ungrouped) {
        groupsArea.insertBefore(newGrp, ungrouped);
      } else {
        groupsArea.appendChild(newGrp);
      }
      var nameInput = newGrp.querySelector('.dwmcd-group-name');
      if (nameInput) nameInput.focus();
    });
  }

  // Menu reset
  if (menuReset && menuFlag) {
    menuReset.addEventListener('click', function () {
      if (!confirm('Menu-configuratie resetten naar de WordPress standaard?')) return;
      menuFlag.value = '1';
      var form = menuReset.closest('form');
      if (form) form.submit();
    });
  }

  // Auto-sync the hidden menu_data field whenever the UI changes. A single
  // MutationObserver catches: drag drops, group add/delete, chip reorder via
  // arrow buttons, and any future DOM rearrangement. `input` catches label
  // typing and group name edits. `click` catches visibility toggles. This
  // guarantees menu_data is always fresh when the form submits, regardless
  // of which code path the user took.
  if (groupsArea && menuDataField) {
    var syncTimer = null;
    function scheduleSync() {
      if (syncTimer) return;
      syncTimer = setTimeout(function () {
        syncTimer = null;
        syncMenuData();
      }, 50);
    }
    var mo = new MutationObserver(scheduleSync);
    mo.observe(groupsArea, { childList: true, subtree: true });
    groupsArea.addEventListener('input',  scheduleSync);
    groupsArea.addEventListener('change', scheduleSync);
    groupsArea.addEventListener('click',  scheduleSync);
  }

  // ── GA4: laden bij leeg transient ─────────────────────────────────────────

  var ga4Widget = document.getElementById('dwmcd-ga4-widget');
  if (ga4Widget && ga4Widget.classList.contains('dwmcd-ga4-loading') && DWMCD.ajaxurl) {
    fetch(DWMCD.ajaxurl + '?action=dwmcd_ga4_load&nonce=' + encodeURIComponent(DWMCD.nonce))
      .then(function (r) { return r.json(); })
      .then(function (data) {
        if (data.success && data.data && data.data.html) {
          var tmp = document.createElement('div');
          tmp.innerHTML = data.data.html;
          ga4Widget.parentNode.replaceChild(tmp.firstElementChild || tmp, ga4Widget);
        }
      })
      .catch(function () { /* stil falen */ });
  }

  // ── GA4: verbinding testen & cache wissen ──────────────────────────────────

  var ga4TestBtn    = document.getElementById('dwmcd-ga4-test');
  var ga4RefreshBtn = document.getElementById('dwmcd-ga4-refresh');
  var ga4Status     = document.getElementById('dwmcd-ga4-status');

  function ga4Ajax(action, label) {
    if (!DWMCD.ajaxurl) return;
    if (ga4Status) { ga4Status.textContent = label + '...'; ga4Status.className = 'dwmcd-ga4-status'; }
    fetch(DWMCD.ajaxurl, {
      method: 'POST',
      headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
      body: 'action=' + action + '&nonce=' + encodeURIComponent(DWMCD.nonce),
    })
      .then(function (r) { return r.json(); })
      .then(function (data) {
        if (!ga4Status) return;
        ga4Status.textContent = data.data && data.data.message ? data.data.message : (data.success ? 'Geslaagd.' : 'Mislukt.');
        ga4Status.className = 'dwmcd-ga4-status ' + (data.success ? 'success' : 'error');
      })
      .catch(function () {
        if (ga4Status) { ga4Status.textContent = 'Verzoek mislukt.'; ga4Status.className = 'dwmcd-ga4-status error'; }
      });
  }

  if (ga4TestBtn)    ga4TestBtn.addEventListener('click', function () { ga4Ajax('dwmcd_ga4_test', 'Testen'); });
  if (ga4RefreshBtn) ga4RefreshBtn.addEventListener('click', function () { ga4Ajax('dwmcd_ga4_refresh', 'Cache wissen'); });

  // ── Reset instellingen ──────────────────────────────────────────────────
  var resetBtn = document.getElementById('dwmcd-reset-settings');
  if (resetBtn) {
    resetBtn.addEventListener('click', function () {
      if (!confirm('Weet je zeker dat je ALLE plugin-instellingen wilt terugzetten naar de standaardwaarden?\n\nDit kan niet ongedaan worden gemaakt.')) {
        return;
      }
      resetBtn.disabled = true;
      resetBtn.textContent = 'Bezig met resetten…';
      var fd = new FormData();
      fd.append('action', 'dwmcd_reset_settings');
      fd.append('nonce', DWMCD.nonce);
      fetch(DWMCD.ajaxurl, { method: 'POST', body: fd })
        .then(function (r) { return r.json(); })
        .then(function (data) {
          if (data.success) {
            window.location.reload();
          } else {
            alert(data.data && data.data.message ? data.data.message : 'Reset mislukt.');
            resetBtn.disabled = false;
            resetBtn.textContent = 'Reset alle instellingen';
          }
        })
        .catch(function () {
          alert('Verzoek mislukt. Probeer het opnieuw.');
          resetBtn.disabled = false;
          resetBtn.textContent = 'Reset alle instellingen';
        });
    });
  }

})();
