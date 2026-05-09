/**
 * Admin JS for JB WP Beheer Plugin menu organizer and settings
 */

(function() {
	'use strict';

	document.addEventListener('DOMContentLoaded', function() {
		initMenuOrganizer();
	});

	function initMenuOrganizer() {
		const menuOrderReset = document.getElementById('dwmcd-menu-order-reset');
		const menuDataInput = document.getElementById('dwmcd-menu-data');
		const groupsArea = document.getElementById('dwmcd-groups-area');

		if (!groupsArea) {
			return; // Menu organizer not on this page
		}

		// ── Role selector modal handlers ──────────────────────────────────────
		document.addEventListener('click', function(e) {
			const rolesBtn = e.target.closest('.dwmcd-chip-roles-btn');
			if (!rolesBtn) return;

			e.preventDefault();
			const chip = rolesBtn.closest('.dwmcd-menu-chip');
			const modal = chip.querySelector('.dwmcd-chip-roles-modal');
			modal.style.display = 'block';
		});

		// Save roles button
		document.addEventListener('click', function(e) {
			const saveBtn = e.target.closest('.dwmcd-chip-roles-save');
			if (!saveBtn) return;

			e.preventDefault();
			const modal = saveBtn.closest('.dwmcd-chip-roles-modal');
			const chip = modal.closest('.dwmcd-menu-chip');
			const checkboxes = modal.querySelectorAll('.dwmcd-role-checkbox:checked');
			const selectedRoles = Array.from(checkboxes).map(cb => cb.value);

			// Update hidden input with selected roles as JSON
			const rolesInput = chip.querySelector('.dwmcd-chip-visible-for-roles');
			rolesInput.value = JSON.stringify(selectedRoles);

			// Update chip visibility indicator
			if (selectedRoles.length === 0) {
				chip.classList.add('is-hidden');
			} else {
				chip.classList.remove('is-hidden');
			}

			// Close modal
			modal.style.display = 'none';

			// Mark form as changed
			serializeMenuData();
		});

		// Cancel roles button
		document.addEventListener('click', function(e) {
			const cancelBtn = e.target.closest('.dwmcd-chip-roles-cancel');
			if (!cancelBtn) return;

			e.preventDefault();
			const modal = cancelBtn.closest('.dwmcd-chip-roles-modal');
			modal.style.display = 'none';
		});

		// Close modal when clicking outside
		document.addEventListener('click', function(e) {
			if (e.target.classList.contains('dwmcd-chip-roles-modal')) {
				e.target.style.display = 'none';
			}
		});

		// ── Drag and drop functionality ───────────────────────────────────────
		let draggedChip = null;

		document.addEventListener('dragstart', function(e) {
			const chip = e.target.closest('.dwmcd-menu-chip');
			if (!chip) return;

			draggedChip = chip;
			chip.style.opacity = '0.5';
			e.dataTransfer.effectAllowed = 'move';
		});

		document.addEventListener('dragend', function(e) {
			const chip = e.target.closest('.dwmcd-menu-chip');
			if (!chip) return;

			chip.style.opacity = '1';
			draggedChip = null;

			// Serialize after drag
			serializeMenuData();
		});

		document.addEventListener('dragover', function(e) {
			e.preventDefault();
			e.dataTransfer.dropEffect = 'move';

			const dropZone = e.target.closest('[data-group-drop]');
			if (!dropZone) return;

			dropZone.style.backgroundColor = 'rgba(0, 0, 0, 0.05)';
		});

		document.addEventListener('dragleave', function(e) {
			const dropZone = e.target.closest('[data-group-drop]');
			if (!dropZone) return;

			dropZone.style.backgroundColor = '';
		});

		document.addEventListener('drop', function(e) {
			e.preventDefault();

			const dropZone = e.target.closest('[data-group-drop]');
			if (!dropZone || !draggedChip) return;

			dropZone.style.backgroundColor = '';
			dropZone.appendChild(draggedChip);
		});

		// ── Move buttons (up/down) ───────────────────────────────────────────
		document.addEventListener('click', function(e) {
			const moveBtn = e.target.closest('.dwmcd-chip-move-btn');
			if (!moveBtn) return;

			e.preventDefault();

			const chip = moveBtn.closest('.dwmcd-menu-chip');
			const group = chip.closest('.dwmcd-group-items');
			const direction = moveBtn.dataset.chipMove;

			if (!group) return;

			if (direction === 'up' && chip.previousElementSibling) {
				group.insertBefore(chip, chip.previousElementSibling);
			} else if (direction === 'down' && chip.nextElementSibling) {
				group.insertBefore(chip.nextElementSibling, chip);
			}

			serializeMenuData();
		});

		// ── Custom label change ──────────────────────────────────────────────
		document.addEventListener('change', function(e) {
			if (e.target.classList.contains('dwmcd-chip-custom-label')) {
				serializeMenuData();
			}
		});

		// ── Reset menu ───────────────────────────────────────────────────────
		const resetCheckbox = document.querySelector('input[name="dwmcd_settings[menu_organizer_enabled]"]');
		if (resetCheckbox) {
			resetCheckbox.addEventListener('change', function() {
				if (!this.checked) {
					// When disabling, clear the menu data
					if (menuDataInput) {
						menuDataInput.value = '';
					}
				}
			});
		}

		// ── Serialize menu data to hidden input ───────────────────────────────
		function serializeMenuData() {
			if (!menuDataInput) return;

			const groups = [];
			const items = [];

			// Collect groups
			document.querySelectorAll('.dwmcd-menu-group[data-group-id]').forEach((group, idx) => {
				const groupId = group.dataset.groupId;

				// Skip "ungrouped" group (empty id)
				if (groupId === '') return;

				const nameInput = group.querySelector('.dwmcd-group-name');
				const groupName = nameInput ? nameInput.value : '';

				groups.push({
					id: groupId,
					name: groupName,
				});
			});

			// Collect items with their order and settings
			document.querySelectorAll('.dwmcd-menu-chip').forEach((chip) => {
				const slug = chip.dataset.slug;
				const customLabelInput = chip.querySelector('.dwmcd-chip-custom-label');
				const rolesInput = chip.querySelector('.dwmcd-chip-visible-for-roles');
				const group = chip.closest('.dwmcd-menu-group');
				const groupId = group ? group.dataset.groupId : '';

				const customLabel = customLabelInput ? customLabelInput.value : '';
				const visibleForRoles = rolesInput ? JSON.parse(rolesInput.value || '[]') : [];

				items.push({
					slug: slug,
					custom_label: customLabel,
					visible_for_roles: visibleForRoles,
					group: groupId || '',
				});
			});

			const menuData = {
				groups: groups,
				items: items,
			};

			menuDataInput.value = JSON.stringify(menuData);
		}

		// Serialize on form submit
		const form = document.querySelector('form');
		if (form) {
			form.addEventListener('submit', function() {
				serializeMenuData();
			});
		}

		// Serialize initial state
		serializeMenuData();
	}
})();
