import './bootstrap';

document.addEventListener('DOMContentLoaded', () => {
    const carousel = document.querySelector('[data-events-carousel]');
    const prevButton = document.querySelector('[data-carousel-prev]');
    const nextButton = document.querySelector('[data-carousel-next]');

    if (carousel && prevButton && nextButton) {
        const getScrollAmount = () => {
            const firstCard = carousel.querySelector('[data-event-card]');
            if (!firstCard) {
                return Math.max(220, Math.floor(carousel.clientWidth * 0.75));
            }

            const styles = window.getComputedStyle(carousel);
            const gap = parseFloat(styles.columnGap || styles.gap || '0') || 0;
            return Math.floor(firstCard.getBoundingClientRect().width + gap);
        };

        const updateEdgeFadeState = () => {
            const maxScrollLeft = Math.max(0, carousel.scrollWidth - carousel.clientWidth);
            const atStart = carousel.scrollLeft <= 1;
            const atEnd = carousel.scrollLeft >= maxScrollLeft - 1;

            carousel.classList.toggle('is-scrolled', !atStart);
            carousel.classList.toggle('is-at-end', atEnd);
        };

        prevButton.addEventListener('click', () => {
            carousel.scrollBy({ left: -getScrollAmount(), behavior: 'smooth' });
        });

        nextButton.addEventListener('click', () => {
            carousel.scrollBy({ left: getScrollAmount(), behavior: 'smooth' });
        });

        carousel.addEventListener('scroll', updateEdgeFadeState, { passive: true });
        window.addEventListener('resize', updateEdgeFadeState);
        updateEdgeFadeState();
    }

    const introHalf = document.querySelector('[data-intro-half]');
    const secondHalf = document.querySelector('[data-second-half]');
    const proceedButton = document.querySelector('[data-proceed-btn]');
    const skipIntroButton = document.querySelector('[data-skip-intro-btn]');
    const debugReturnButton = document.querySelector('[data-debug-return]');

    if (introHalf && secondHalf && proceedButton) {
        const body = document.body;
        const introFit = introHalf.querySelector('[data-intro-fit]');
        const stickyHeader = document.querySelector('header.sticky');
        let introTransitionTimeout = null;

        const fitIntroToViewport = () => {
            if (!introFit) {
                return;
            }

            introFit.style.transform = '';
            introFit.style.transformOrigin = '';

            const availableHeight = introHalf.getBoundingClientRect().height;
            const contentHeight = introFit.scrollHeight;
            if (!availableHeight || !contentHeight) {
                return;
            }

            const scale = Math.min(1, (availableHeight - 12) / contentHeight);
            if (scale < 1) {
                introFit.style.transformOrigin = 'top center';
                introFit.style.transform = `scale(${scale})`;
            }
        };

        const scheduleIntroFit = () => window.requestAnimationFrame(fitIntroToViewport);

        const setIntroButtonsDisabled = (disabled) => {
            proceedButton.disabled = disabled;
            proceedButton.classList.toggle('opacity-60', disabled);
            proceedButton.classList.toggle('cursor-not-allowed', disabled);

            if (skipIntroButton) {
                skipIntroButton.disabled = disabled;
                skipIntroButton.classList.toggle('opacity-60', disabled);
                skipIntroButton.classList.toggle('cursor-not-allowed', disabled);
            }
        };

        const getSecondHalfTargetTop = () => {
            const secondHalfTop = secondHalf.getBoundingClientRect().top + window.scrollY;
            const headerHeight = stickyHeader ? stickyHeader.getBoundingClientRect().height : 0;
            const revealOffset = 12;
            return Math.max(0, secondHalfTop - headerHeight - revealOffset);
        };

        const scrollToSecondHalf = () => {
            window.scrollTo({ top: getSecondHalfTargetTop(), behavior: 'smooth' });
        };

        const resetIntroTransition = () => {
            if (introTransitionTimeout !== null) {
                window.clearTimeout(introTransitionTimeout);
                introTransitionTimeout = null;
            }

            body.classList.remove('clash-play');
            body.classList.remove('clash-finished');
            setIntroButtonsDisabled(false);
        };

        fitIntroToViewport();
        window.requestAnimationFrame(fitIntroToViewport);
        window.addEventListener('resize', scheduleIntroFit);
        window.addEventListener('load', scheduleIntroFit);
        if (document.fonts && document.fonts.ready) {
            document.fonts.ready.then(scheduleIntroFit);
        }

        const clashTravelDurationMs = 1660;
        const clashImpactDurationMs = 250;
        const clashResetBufferMs = 40;
        const clashDurationMs = clashTravelDurationMs + clashImpactDurationMs + clashResetBufferMs;

        proceedButton.addEventListener('click', () => {
            if (introTransitionTimeout !== null) {
                return;
            }

            setIntroButtonsDisabled(true);
            body.classList.remove('clash-play');
            body.classList.remove('clash-finished');
            void body.offsetWidth;
            body.classList.add('clash-play');
            introTransitionTimeout = window.setTimeout(() => {
                introTransitionTimeout = null;
                body.classList.remove('clash-play');
                body.classList.add('clash-finished');
                setIntroButtonsDisabled(false);
                scrollToSecondHalf();
            }, clashDurationMs);
        });

        skipIntroButton?.addEventListener('click', () => {
            resetIntroTransition();
            scrollToSecondHalf();
        });

        if (debugReturnButton) {
            debugReturnButton.addEventListener('click', () => {
                resetIntroTransition();
                window.scrollTo({ top: 0, behavior: 'smooth' });
                window.requestAnimationFrame(fitIntroToViewport);
            });
        }
    }

    const updateBattlePickerSelection = (picker, selectedChoice) => {
        let selectedButton = null;

        picker.querySelectorAll('[data-battle-choice]').forEach((button) => {
            const isSelected = button.dataset.choice === selectedChoice;
            if (isSelected) {
                selectedButton = button;
            }
            button.classList.toggle('border-amber-400/70', isSelected);
            button.classList.toggle('bg-amber-400/12', isSelected);
            button.classList.toggle('text-amber-100', isSelected);
            button.classList.toggle('shadow-[0_10px_24px_rgba(251,191,36,0.12)]', isSelected);

            button.classList.toggle('border-slate-700/80', !isSelected);
            button.classList.toggle('bg-slate-950/75', !isSelected);
            button.classList.toggle('text-slate-300', !isSelected);
        });

        const summary = picker.querySelector('[data-battle-summary]');
        if (!summary) {
            return;
        }

        summary.textContent = selectedButton?.dataset.choiceSummary || summary.dataset.defaultSummary || '';
        summary.classList.toggle('text-amber-200', Boolean(selectedButton));
        summary.classList.toggle('font-semibold', Boolean(selectedButton));
        summary.classList.toggle('text-slate-400', !selectedButton);
    };

    document.addEventListener('click', (event) => {
        const choiceButton = event.target.closest('[data-battle-choice]');
        if (choiceButton) {
            const picker = choiceButton.closest('[data-battle-picker]');
            if (!picker) {
                return;
            }

            const winnerInput = picker.querySelector('[data-battle-result-winner]');
            const typeInput = picker.querySelector('[data-battle-result-type]');
            const [winner = '', type = ''] = (choiceButton.dataset.choice || '').split(':');

            if (winnerInput) {
                winnerInput.value = winner;
            }

            if (typeInput) {
                typeInput.value = type;
            }

            updateBattlePickerSelection(picker, `${winner}:${type}`);
            return;
        }

        const clearButton = event.target.closest('[data-battle-clear]');
        if (!clearButton) {
            return;
        }

        const picker = clearButton.closest('[data-battle-picker]');
        if (!picker) {
            return;
        }

        const winnerInput = picker.querySelector('[data-battle-result-winner]');
        const typeInput = picker.querySelector('[data-battle-result-type]');

        if (winnerInput) {
            winnerInput.value = '';
        }

        if (typeInput) {
            typeInput.value = '';
        }

        updateBattlePickerSelection(picker, '');
    });

    const updateStadiumSideButtons = (group, selectedSide) => {
        group.querySelectorAll('[data-stadium-side-choice]').forEach((button) => {
            const isSelected = button.dataset.sideChoice === selectedSide;
            button.classList.toggle('border-cyan-400/70', isSelected);
            button.classList.toggle('bg-cyan-400/10', isSelected);
            button.classList.toggle('text-cyan-100', isSelected);

            button.classList.toggle('border-slate-700/80', !isSelected);
            button.classList.toggle('border-slate-700', !isSelected);
            button.classList.toggle('bg-slate-950/70', !isSelected);
            button.classList.toggle('text-slate-300', !isSelected);
        });
    };

    document.addEventListener('click', (event) => {
        const sideButton = event.target.closest('[data-stadium-side-choice]');
        if (!sideButton) {
            return;
        }

        const group = sideButton.closest('[data-stadium-side-group]');
        const control = sideButton.closest('[data-stadium-side-control]');
        if (!group || !control) {
            return;
        }

        const sideInput = group.querySelector('[data-stadium-side-input]');
        const selectedSide = sideButton.dataset.sideChoice || '';
        const opposingSide = selectedSide === 'X' ? 'B' : (selectedSide === 'B' ? 'X' : '');

        if (sideInput) {
            sideInput.value = selectedSide;
        }
        updateStadiumSideButtons(group, selectedSide);

        control.querySelectorAll('[data-stadium-side-group]').forEach((candidate) => {
            if (candidate === group) {
                return;
            }

            const candidateInput = candidate.querySelector('[data-stadium-side-input]');
            if (candidateInput) {
                candidateInput.value = opposingSide;
            }

            updateStadiumSideButtons(candidate, opposingSide);
        });
    });

    const dashboardShell = document.querySelector('[data-dashboard-shell]');
    if (dashboardShell) {
        document.documentElement.style.overflowY = 'hidden';
        document.body.style.overflowY = 'hidden';
        setupDashboardSoftNavigation();
        window.requestAnimationFrame(() => hideDashboardLoader());
    }

    const registerForm = document.querySelector('[data-auth-register]');
    if (registerForm) {
        const modeInputs = registerForm.querySelectorAll('input[name="mode"]');
        const modePanels = registerForm.querySelectorAll('[data-register-mode-panel]');

        const updateRegisterMode = () => {
            const activeMode = Array.from(modeInputs).find((input) => input.checked)?.value || 'register';

            modePanels.forEach((panel) => {
                panel.classList.toggle('hidden', panel.dataset.registerModePanel !== activeMode);
            });
        };

        modeInputs.forEach((input) => {
            input.addEventListener('change', updateRegisterMode);
        });

        updateRegisterMode();
    }

    const registerModal = document.querySelector('[data-register-modal]');
    if (registerModal) {
        const openButtons = document.querySelectorAll('[data-register-modal-open]');
        const closeButtons = registerModal.querySelectorAll('[data-register-modal-close]');
        const existingSelect = registerModal.querySelector('[data-register-existing]');
        const addExistingButton = registerModal.querySelector('[data-register-existing-add]');
        const newNicknameInput = registerModal.querySelector('[data-register-new]');
        const addNewButton = registerModal.querySelector('[data-register-new-add]');
        const selectedContainer = registerModal.querySelector('[data-register-selected]');
        const hiddenInputsContainer = registerModal.querySelector('[data-register-hidden-inputs]');
        const countLabel = registerModal.querySelector('[data-register-count]');
        const feedbackLabel = registerModal.querySelector('[data-register-feedback]');
        const submitButton = registerModal.querySelector('[data-register-submit]');
        const registerForm = registerModal.querySelector('[data-register-form]');
        const selectedNicknames = new Map();
        const defaultFeedback = feedbackLabel ? feedbackLabel.textContent : '';

        const normalizeNickname = (value) => value.trim().replace(/\s+/g, ' ');
        const nicknameKey = (value) => normalizeNickname(value).toLocaleLowerCase();

        const setFeedback = (message, tone = 'neutral') => {
            if (!feedbackLabel) {
                return;
            }

            feedbackLabel.textContent = message;
            feedbackLabel.classList.remove('text-slate-500', 'text-emerald-300', 'text-rose-300');

            if (tone === 'success') {
                feedbackLabel.classList.add('text-emerald-300');
                return;
            }

            if (tone === 'error') {
                feedbackLabel.classList.add('text-rose-300');
                return;
            }

            feedbackLabel.classList.add('text-slate-500');
        };

        const renderSelectedNicknames = () => {
            if (!selectedContainer || !hiddenInputsContainer) {
                return;
            }

            selectedContainer.innerHTML = '';
            hiddenInputsContainer.innerHTML = '';

            if (countLabel) {
                countLabel.textContent = `${selectedNicknames.size} selected`;
            }

            if (submitButton) {
                submitButton.disabled = selectedNicknames.size === 0;
            }

            if (selectedNicknames.size === 0) {
                const emptyState = document.createElement('p');
                emptyState.className = 'text-sm text-slate-500';
                emptyState.textContent = 'No players selected yet.';
                selectedContainer.appendChild(emptyState);
                return;
            }

            selectedNicknames.forEach((nickname, key) => {
                const row = document.createElement('div');
                row.className = 'flex items-center justify-between gap-2 border border-slate-800/80 bg-slate-950/65 px-2.5 py-1.5';

                const name = document.createElement('span');
                name.className = 'min-w-0 flex-1 truncate text-sm text-slate-100';
                name.textContent = nickname;

                const removeButton = document.createElement('button');
                removeButton.type = 'button';
                removeButton.className = 'type-label border border-rose-500/60 px-2 py-1 text-[9px] text-rose-200 transition hover:bg-rose-500/10';
                removeButton.textContent = 'Remove';
                removeButton.addEventListener('click', () => {
                    selectedNicknames.delete(key);
                    renderSelectedNicknames();
                    setFeedback(`${nickname} removed from the selection.`, 'neutral');
                });

                const hiddenInput = document.createElement('input');
                hiddenInput.type = 'hidden';
                hiddenInput.name = 'selected_nicknames[]';
                hiddenInput.value = nickname;

                row.appendChild(name);
                row.appendChild(removeButton);
                selectedContainer.appendChild(row);
                hiddenInputsContainer.appendChild(hiddenInput);
            });
        };

        const addNickname = (rawNickname, successMessage) => {
            const nickname = normalizeNickname(rawNickname);

            if (!nickname) {
                setFeedback('Choose or enter a nickname first.', 'error');
                return;
            }

            const key = nicknameKey(nickname);
            if (selectedNicknames.has(key)) {
                setFeedback(`${nickname} is already selected.`, 'error');
                return;
            }

            selectedNicknames.set(key, nickname);
            renderSelectedNicknames();
            setFeedback(successMessage || `${nickname} added to the selection.`, 'success');
        };

        const openRegisterModal = () => {
            registerModal.classList.remove('hidden');
            registerModal.classList.add('flex');
            document.body.classList.add('overflow-hidden');
        };

        const closeRegisterModal = () => {
            registerModal.classList.add('hidden');
            registerModal.classList.remove('flex');
            document.body.classList.remove('overflow-hidden');
            setFeedback(defaultFeedback, 'neutral');
        };

        if (hiddenInputsContainer) {
            hiddenInputsContainer.querySelectorAll('input[name="selected_nicknames[]"]').forEach((input) => {
                const nickname = normalizeNickname(input.value);
                if (!nickname) {
                    return;
                }

                selectedNicknames.set(nicknameKey(nickname), nickname);
            });
        }

        renderSelectedNicknames();

        openButtons.forEach((button) => {
            button.addEventListener('click', openRegisterModal);
        });

        closeButtons.forEach((button) => {
            button.addEventListener('click', closeRegisterModal);
        });

        addExistingButton?.addEventListener('click', () => {
            const selectedOptions = existingSelect ? Array.from(existingSelect.selectedOptions) : [];

            if (selectedOptions.length === 0) {
                setFeedback('Select at least one registered user to add.', 'error');
                return;
            }

            selectedOptions.forEach((option) => {
                if (option.value) {
                    addNickname(option.value, `${option.value} added from registered users.`);
                }
            });
        });

        existingSelect?.addEventListener('dblclick', () => {
            const firstOption = Array.from(existingSelect.selectedOptions).find((option) => option.value);
            if (firstOption) {
                addNickname(firstOption.value, `${firstOption.value} added from registered users.`);
            }
        });

        newNicknameInput?.addEventListener('keydown', (event) => {
            if (event.key !== 'Enter') {
                return;
            }

            event.preventDefault();
            addNickname(newNicknameInput.value, `${normalizeNickname(newNicknameInput.value)} added as a new user.`);
            newNicknameInput.value = '';
        });

        addNewButton?.addEventListener('click', () => {
            if (!newNicknameInput) {
                return;
            }

            const nickname = normalizeNickname(newNicknameInput.value);
            addNickname(nickname, `${nickname} added as a new user.`);
            newNicknameInput.value = '';
        });

        registerForm?.addEventListener('submit', (event) => {
            if (selectedNicknames.size > 0) {
                return;
            }

            event.preventDefault();
            setFeedback('Add at least one player before confirming.', 'error');
        });

        registerModal.addEventListener('click', (event) => {
            if (event.target === registerModal) {
                closeRegisterModal();
            }
        });

        if (registerModal.dataset.registerOpenOnLoad === 'true') {
            openRegisterModal();
        }

        document.addEventListener('keydown', (event) => {
            if (event.key === 'Escape' && !registerModal.classList.contains('hidden')) {
                closeRegisterModal();
            }
        });
    }

    const deckModal = document.querySelector('[data-deck-modal]');
    if (deckModal) {
        const openButtons = document.querySelectorAll('[data-deck-modal-open]');
        const closeButtons = deckModal.querySelectorAll('[data-deck-modal-close]');
        const deckScrollBody = deckModal.querySelector('[data-deck-scroll-body]');
        const deckBulkForm = deckModal.querySelector('[data-deck-bulk-form]');
        const deckBulkInputs = deckModal.querySelector('[data-deck-bulk-inputs]');
        const deckBulkSubmitButton = deckModal.querySelector('[data-deck-bulk-submit]');

        const focusDeckRow = () => {
            const playerId = deckModal.dataset.deckFocusPlayerId;
            if (!playerId || !deckScrollBody) {
                return;
            }

            const targetRow = deckModal.querySelector(`[data-deck-player-row="${playerId}"]`);
            if (!targetRow) {
                return;
            }

            targetRow.scrollIntoView({ block: 'center', behavior: 'auto' });
        };

        const appendBulkInput = (name, value) => {
            if (!deckBulkInputs) {
                return;
            }

            const input = document.createElement('input');
            input.type = 'hidden';
            input.name = name;
            input.value = value;
            deckBulkInputs.appendChild(input);
        };

        const openDeckModal = () => {
            deckModal.classList.remove('hidden');
            deckModal.classList.add('flex');
            document.body.classList.add('overflow-hidden');
            window.requestAnimationFrame(focusDeckRow);
        };

        const closeDeckModal = () => {
            deckModal.classList.add('hidden');
            deckModal.classList.remove('flex');
            document.body.classList.remove('overflow-hidden');
        };

        openButtons.forEach((button) => {
            button.addEventListener('click', openDeckModal);
        });

        closeButtons.forEach((button) => {
            button.addEventListener('click', closeDeckModal);
        });

        deckBulkSubmitButton?.addEventListener('click', () => {
            if (!deckBulkForm || !deckBulkInputs) {
                return;
            }

            deckBulkForm.setAttribute('action', deckModal.dataset.deckBulkAction || '');
            deckBulkInputs.innerHTML = '';

            deckModal.querySelectorAll('[data-deck-player-row]').forEach((row) => {
                const playerId = row.dataset.deckPlayerRow;
                if (!playerId) {
                    return;
                }

                const bey1 = row.querySelector('input[name="deck_bey1"]');
                const bey2 = row.querySelector('input[name="deck_bey2"]');
                const bey3 = row.querySelector('input[name="deck_bey3"]');

                appendBulkInput(`decks[${playerId}][deck_bey1]`, bey1?.value || '');
                appendBulkInput(`decks[${playerId}][deck_bey2]`, bey2?.value || '');
                appendBulkInput(`decks[${playerId}][deck_bey3]`, bey3?.value || '');
            });

            deckBulkForm.submit();
        });

        if (deckModal.dataset.deckOpenOnLoad === 'true') {
            openDeckModal();
        }

        document.addEventListener('keydown', (event) => {
            if (event.key === 'Escape' && !deckModal.classList.contains('hidden')) {
                closeDeckModal();
            }
        });
    }

    const participantsModal = document.querySelector('[data-participants-modal]');
    if (participantsModal) {
        const openButtons = document.querySelectorAll('[data-participants-modal-open]');
        const closeButtons = participantsModal.querySelectorAll('[data-participants-modal-close]');

        const openParticipantsModal = () => {
            participantsModal.classList.remove('hidden');
            participantsModal.classList.add('flex');
            document.body.classList.add('overflow-hidden');
        };

        const closeParticipantsModal = () => {
            participantsModal.classList.add('hidden');
            participantsModal.classList.remove('flex');
            document.body.classList.remove('overflow-hidden');
        };

        openButtons.forEach((button) => {
            button.addEventListener('click', openParticipantsModal);
        });

        closeButtons.forEach((button) => {
            button.addEventListener('click', closeParticipantsModal);
        });

        participantsModal.addEventListener('click', (event) => {
            if (event.target === participantsModal) {
                closeParticipantsModal();
            }
        });

        document.addEventListener('keydown', (event) => {
            if (event.key === 'Escape' && !participantsModal.classList.contains('hidden')) {
                closeParticipantsModal();
            }
        });
    }

    const lockedParticipantModals = document.querySelectorAll('[data-locked-participant-modal]');
    if (lockedParticipantModals.length > 0) {
        const openModal = (modal) => {
            modal.classList.remove('hidden');
            modal.classList.add('flex');
            document.body.classList.add('overflow-hidden');
        };

        const closeModal = (modal) => {
            modal.classList.add('hidden');
            modal.classList.remove('flex');

            if (!Array.from(lockedParticipantModals).some((item) => !item.classList.contains('hidden'))) {
                document.body.classList.remove('overflow-hidden');
            }
        };

        document.querySelectorAll('[data-locked-participant-open]').forEach((button) => {
            button.addEventListener('click', () => {
                const targetId = button.dataset.lockedParticipantOpen;
                const modal = Array.from(lockedParticipantModals).find((item) => item.dataset.lockedParticipantModal === targetId);
                if (modal) {
                    openModal(modal);
                }
            });
        });

        lockedParticipantModals.forEach((modal) => {
            modal.querySelectorAll('[data-locked-participant-close]').forEach((button) => {
                button.addEventListener('click', () => closeModal(modal));
            });

            modal.addEventListener('click', (event) => {
                if (event.target === modal) {
                    closeModal(modal);
                }
            });

            if (modal.dataset.lockedParticipantOpenOnLoad === 'true') {
                openModal(modal);
            }
        });

        document.addEventListener('keydown', (event) => {
            if (event.key !== 'Escape') {
                return;
            }

            lockedParticipantModals.forEach((modal) => {
                if (!modal.classList.contains('hidden')) {
                    closeModal(modal);
                }
            });
        });
    }

    const workspaceMatchModal = document.querySelector('[data-workspace-match-modal]');
    if (workspaceMatchModal) {
        const workspaceMatchButtons = document.querySelectorAll('[data-workspace-match-open]');
        const workspaceMatchCloseButtons = workspaceMatchModal.querySelectorAll('[data-workspace-match-close]');
        const workspaceMatchTitle = workspaceMatchModal.querySelector('[data-workspace-match-modal-title]');
        const workspaceMatchSubtitle = workspaceMatchModal.querySelector('[data-workspace-match-modal-subtitle]');
        const workspaceMatchBody = workspaceMatchModal.querySelector('[data-workspace-match-modal-body]');

        const openWorkspaceMatchModal = ({ templateId, title = '', subtitle = '' }) => {
            const template = templateId ? document.getElementById(templateId) : null;
            if (!template || !workspaceMatchBody) {
                return;
            }

            if (workspaceMatchTitle) {
                workspaceMatchTitle.textContent = title || template.dataset.matchModalTitle || '';
            }

            if (workspaceMatchSubtitle) {
                workspaceMatchSubtitle.textContent = subtitle || template.dataset.matchModalSubtitle || '';
            }

            workspaceMatchBody.innerHTML = template.innerHTML;
            workspaceMatchModal.classList.remove('hidden');
            workspaceMatchModal.classList.add('flex');
            document.body.classList.add('overflow-hidden');
        };

        const closeWorkspaceMatchModal = () => {
            workspaceMatchModal.classList.add('hidden');
            workspaceMatchModal.classList.remove('flex');
            if (workspaceMatchBody) {
                workspaceMatchBody.innerHTML = '';
            }
            document.body.classList.remove('overflow-hidden');
        };

        workspaceMatchButtons.forEach((button) => {
            button.addEventListener('click', () => openWorkspaceMatchModal({
                templateId: button.dataset.matchTemplateId,
                title: button.dataset.matchModalTitle || '',
                subtitle: button.dataset.matchModalSubtitle || '',
            }));
        });

        workspaceMatchCloseButtons.forEach((button) => {
            button.addEventListener('click', closeWorkspaceMatchModal);
        });

        workspaceMatchModal.addEventListener('click', (event) => {
            if (event.target === workspaceMatchModal) {
                closeWorkspaceMatchModal();
            }
        });

        document.addEventListener('keydown', (event) => {
            if (event.key === 'Escape' && !workspaceMatchModal.classList.contains('hidden')) {
                closeWorkspaceMatchModal();
            }
        });

        if (workspaceMatchModal.dataset.openTemplateId) {
            openWorkspaceMatchModal({
                templateId: workspaceMatchModal.dataset.openTemplateId,
                title: workspaceMatchModal.dataset.openTitle || '',
                subtitle: workspaceMatchModal.dataset.openSubtitle || '',
            });
        }
    }

    const modal = document.querySelector('[data-event-modal]');
    if (modal) {
        const modalBody = modal.querySelector('[data-event-modal-body]');
        const closeButton = modal.querySelector('[data-event-modal-close]');

        const openModal = (trigger) => {
            const templateId = trigger.dataset.eventPreviewTemplateId;
            const template = templateId ? document.getElementById(templateId) : null;
            if (!template || !modalBody) {
                return;
            }

            modalBody.innerHTML = template.innerHTML;

            modal.classList.remove('hidden');
            modal.classList.add('flex');
            document.body.classList.add('overflow-hidden');
        };

        const closeModal = () => {
            modal.classList.add('hidden');
            modal.classList.remove('flex');
            if (modalBody) {
                modalBody.innerHTML = '';
            }
            document.body.classList.remove('overflow-hidden');
        };

        document.querySelectorAll('[data-event-preview-open]').forEach((card) => {
            card.addEventListener('click', () => openModal(card));
        });

        if (closeButton) {
            closeButton.addEventListener('click', closeModal);
        }

        modal.addEventListener('click', (event) => {
            if (event.target === modal) {
                closeModal();
            }
        });

        document.addEventListener('keydown', (event) => {
            if (event.key === 'Escape' && !modal.classList.contains('hidden')) {
                closeModal();
            }
        });
    }

    const leaderboardProfileModal = document.querySelector('[data-leaderboard-profile-modal]');
    if (leaderboardProfileModal) {
        const openButtons = document.querySelectorAll('[data-leaderboard-profile-open]');
        const closeButton = leaderboardProfileModal.querySelector('[data-leaderboard-profile-close]');
        const modalBody = leaderboardProfileModal.querySelector('[data-leaderboard-profile-body]');

        const openLeaderboardProfileModal = (trigger) => {
            const templateId = trigger.dataset.leaderboardProfileTemplateId;
            const template = templateId ? document.getElementById(templateId) : null;
            if (!template || !modalBody) {
                return;
            }

            modalBody.innerHTML = template.innerHTML;
            leaderboardProfileModal.classList.remove('hidden');
            leaderboardProfileModal.classList.add('flex');
            document.body.classList.add('overflow-hidden');
        };

        const closeLeaderboardProfileModal = () => {
            leaderboardProfileModal.classList.add('hidden');
            leaderboardProfileModal.classList.remove('flex');
            if (modalBody) {
                modalBody.innerHTML = '';
            }
            document.body.classList.remove('overflow-hidden');
        };

        openButtons.forEach((button) => {
            button.addEventListener('click', () => openLeaderboardProfileModal(button));
        });

        closeButton?.addEventListener('click', closeLeaderboardProfileModal);

        leaderboardProfileModal.addEventListener('click', (event) => {
            if (event.target === leaderboardProfileModal) {
                closeLeaderboardProfileModal();
            }
        });

        document.addEventListener('keydown', (event) => {
            if (event.key === 'Escape' && !leaderboardProfileModal.classList.contains('hidden')) {
                closeLeaderboardProfileModal();
            }
        });
    }

    const liveDetailModal = document.querySelector('[data-live-detail-modal]');
    if (liveDetailModal) {
        const openButtons = document.querySelectorAll('[data-live-detail-open]');
        const closeButtons = liveDetailModal.querySelectorAll('[data-live-detail-close]');
        const liveDetailTitle = liveDetailModal.querySelector('[data-live-detail-title]');
        const liveDetailBody = liveDetailModal.querySelector('[data-live-detail-body]');

        const openLiveDetailModal = (button) => {
            const templateId = button.dataset.liveDetailTemplateId;
            const template = templateId ? document.getElementById(templateId) : null;
            if (!template || !liveDetailBody) {
                return;
            }

            if (liveDetailTitle) {
                liveDetailTitle.textContent = button.dataset.liveDetailTitle || 'Details';
            }

            liveDetailBody.innerHTML = template.innerHTML;
            liveDetailModal.classList.remove('hidden');
            liveDetailModal.classList.add('flex');
            document.body.classList.add('overflow-hidden');
        };

        const closeLiveDetailModal = () => {
            liveDetailModal.classList.add('hidden');
            liveDetailModal.classList.remove('flex');
            if (liveDetailBody) {
                liveDetailBody.innerHTML = '';
            }
            document.body.classList.remove('overflow-hidden');
        };

        openButtons.forEach((button) => {
            button.addEventListener('click', () => openLiveDetailModal(button));
        });

        closeButtons.forEach((button) => {
            button.addEventListener('click', closeLiveDetailModal);
        });

        liveDetailModal.addEventListener('click', (event) => {
            if (event.target === liveDetailModal) {
                closeLiveDetailModal();
            }
        });

        document.addEventListener('keydown', (event) => {
            if (event.key === 'Escape' && !liveDetailModal.classList.contains('hidden')) {
                closeLiveDetailModal();
            }
        });
    }

    const profileEventsModal = document.querySelector('[data-profile-events-modal]');
    if (profileEventsModal) {
        const openButtons = document.querySelectorAll('[data-profile-events-open]');
        const closeButtons = profileEventsModal.querySelectorAll('[data-profile-events-close]');

        const openProfileEventsModal = () => {
            profileEventsModal.classList.remove('hidden');
            profileEventsModal.classList.add('flex');
            document.body.classList.add('overflow-hidden');
        };

        const closeProfileEventsModal = () => {
            profileEventsModal.classList.add('hidden');
            profileEventsModal.classList.remove('flex');
            document.body.classList.remove('overflow-hidden');
        };

        openButtons.forEach((button) => {
            button.addEventListener('click', openProfileEventsModal);
        });

        closeButtons.forEach((button) => {
            button.addEventListener('click', closeProfileEventsModal);
        });

        profileEventsModal.addEventListener('click', (event) => {
            if (event.target === profileEventsModal) {
                closeProfileEventsModal();
            }
        });

        document.addEventListener('keydown', (event) => {
            if (event.key === 'Escape' && !profileEventsModal.classList.contains('hidden')) {
                closeProfileEventsModal();
            }
        });
    }

    const profileMatchModal = document.querySelector('[data-profile-match-modal]');
    if (profileMatchModal) {
        const openButtons = document.querySelectorAll('[data-profile-match-open]');
        const closeButtons = profileMatchModal.querySelectorAll('[data-profile-match-close]');
        const modalBody = profileMatchModal.querySelector('[data-profile-match-body]');

        const openProfileMatchModal = (button) => {
            const templateId = button.dataset.profileMatchTemplateId;
            const template = templateId ? document.getElementById(templateId) : null;
            if (!template || !modalBody) {
                return;
            }

            modalBody.innerHTML = template.innerHTML;
            profileMatchModal.classList.remove('hidden');
            profileMatchModal.classList.add('flex');
            document.body.classList.add('overflow-hidden');
        };

        const closeProfileMatchModal = () => {
            profileMatchModal.classList.add('hidden');
            profileMatchModal.classList.remove('flex');
            if (modalBody) {
                modalBody.innerHTML = '';
            }
            document.body.classList.remove('overflow-hidden');
        };

        openButtons.forEach((button) => {
            button.addEventListener('click', () => openProfileMatchModal(button));
        });

        closeButtons.forEach((button) => {
            button.addEventListener('click', closeProfileMatchModal);
        });

        profileMatchModal.addEventListener('click', (event) => {
            if (event.target === profileMatchModal) {
                closeProfileMatchModal();
            }
        });

        document.addEventListener('keydown', (event) => {
            if (event.key === 'Escape' && !profileMatchModal.classList.contains('hidden')) {
                closeProfileMatchModal();
            }
        });
    }

    const profileMatchFilter = document.querySelector('[data-profile-match-filter]');
    const profileMatchList = document.querySelector('[data-profile-match-list]');
    if (profileMatchFilter && profileMatchList) {
        const matchCards = Array.from(profileMatchList.querySelectorAll('[data-profile-match-event-id]'));
        const emptyState = profileMatchList.querySelector('[data-profile-match-filter-empty]');

        const updateProfileMatchFilter = () => {
            const selectedEventId = profileMatchFilter.value;
            let visibleCount = 0;

            matchCards.forEach((card) => {
                const isVisible = !selectedEventId || card.dataset.profileMatchEventId === selectedEventId;
                card.classList.toggle('hidden', !isVisible);
                if (isVisible) {
                    visibleCount += 1;
                }
            });

            if (emptyState) {
                emptyState.classList.toggle('hidden', visibleCount !== 0);
            }
        };

        profileMatchFilter.addEventListener('change', updateProfileMatchFilter);
        updateProfileMatchFilter();
    }
});

function setupDashboardSoftNavigation() {
    if (window.__dashboardSoftNavigationBound) {
        return;
    }

    window.__dashboardSoftNavigationBound = true;
    window.__dashboardRetryRequest = null;

    const state = {
        activeController: null,
        requestInFlight: false,
        refreshTimer: null,
        retryRequest: null,
    };

    const getDashboardShell = () => document.querySelector('[data-dashboard-shell]');
    const getDashboardMain = () => document.querySelector('[data-dashboard-main]');
    const getDashboardRoute = () => getDashboardShell()?.dataset.dashboardRoute || '';
    const isDashboardActive = () => Boolean(getDashboardShell());

    const isDashboardUrl = (candidate) => {
        const dashboardRoute = getDashboardRoute();
        if (!dashboardRoute) {
            return false;
        }

        const dashboardUrl = new URL(dashboardRoute, window.location.href);
        const targetUrl = new URL(candidate, window.location.href);

        return targetUrl.origin === dashboardUrl.origin
            && targetUrl.pathname === dashboardUrl.pathname;
    };

    const captureDashboardScroll = () => ({
        shellTop: getDashboardShell()?.scrollTop ?? 0,
        mainTop: getDashboardMain()?.scrollTop ?? 0,
    });

    const restoreDashboardScroll = (positions) => {
        if (!positions) {
            return;
        }

        window.requestAnimationFrame(() => {
            const shell = getDashboardShell();
            const main = getDashboardMain();

            if (shell) {
                shell.scrollTop = positions.shellTop ?? 0;
            }

            if (main) {
                main.scrollTop = positions.mainTop ?? 0;
            }
        });
    };

    const resetDashboardScroll = () => {
        window.requestAnimationFrame(() => {
            const shell = getDashboardShell();
            const main = getDashboardMain();

            if (shell) {
                shell.scrollTop = 0;
            }

            if (main) {
                main.scrollTop = 0;
            }
        });
    };

    const setDashboardBusyState = (pending) => {
        const shell = getDashboardShell();
        if (!shell) {
            return;
        }

        shell.style.transition = 'opacity 180ms ease, transform 180ms ease, filter 180ms ease';

        if (pending) {
            shell.style.opacity = '0.72';
            shell.style.transform = 'translateY(6px)';
            shell.style.filter = 'saturate(0.88)';
            shell.style.pointerEvents = 'none';
            return;
        }

        shell.style.opacity = '1';
        shell.style.transform = 'translateY(0)';
        shell.style.filter = '';
        shell.style.pointerEvents = '';
    };

    const animateDashboardShellIn = () => {
        const shell = getDashboardShell();
        if (!shell) {
            return;
        }

        shell.style.opacity = '0';
        shell.style.transform = 'translateY(10px)';
        shell.style.transition = 'opacity 220ms ease, transform 220ms ease';

        window.requestAnimationFrame(() => {
            shell.style.opacity = '1';
            shell.style.transform = 'translateY(0)';
        });
    };

    const updateHistoryForDashboard = (url, mode) => {
        if (mode === 'skip') {
            return;
        }

        if (mode === 'push' && url !== window.location.href) {
            window.history.pushState({ dashboard: true }, '', url);
            return;
        }

        window.history.replaceState({ dashboard: true }, '', url);
    };

    const applyDashboardDocument = (nextDocument, finalUrl, options = {}) => {
        const nextDashboardShell = nextDocument.querySelector('[data-dashboard-shell]');
        document.title = nextDocument.title || document.title;
        document.body.className = nextDocument.body.className;
        document.body.innerHTML = nextDocument.body.innerHTML;

        updateHistoryForDashboard(finalUrl, options.historyMode ?? 'replace');
        bindDashboardBodyInteractions();
        if (options.quiet) {
            hideDashboardLoader();
        } else {
            window.requestAnimationFrame(() => hideDashboardLoader());
        }

        if (options.preserveScroll) {
            restoreDashboardScroll(options.scrollPositions);
        } else {
            resetDashboardScroll();
        }

        if (!options.quiet) {
            animateDashboardShellIn();
        }

        if (options.warnOnErrors && nextDashboardShell?.dataset.dashboardErrorState === 'true') {
            window.requestAnimationFrame(() => {
                showDashboardWarning({
                    title: 'Action needs attention',
                    message: 'The dashboard finished the request with errors. Review the highlighted details before trying again.',
                    allowRetry: false,
                });
            });
        }
    };

    const requestDashboardDocument = async (url, options = {}) => {
        if (!isDashboardActive()) {
            if (!options.quiet) {
                window.location.href = url;
            }
            return false;
        }

        if (state.activeController) {
            state.activeController.abort();
        }

        const controller = new AbortController();
        state.activeController = controller;
        state.requestInFlight = true;

        const preserveScroll = Boolean(options.preserveScroll);
        const scrollPositions = preserveScroll ? captureDashboardScroll() : null;

        if (!options.quiet) {
            setDashboardBusyState(true);
            showDashboardLoader('Updating changes...');
            hideDashboardWarning();
        }

        try {
            const response = await fetch(url, {
                method: options.method || 'GET',
                body: options.body,
                credentials: 'same-origin',
                headers: {
                    Accept: 'text/html,application/xhtml+xml',
                    'X-Requested-With': 'XMLHttpRequest',
                    ...(options.headers || {}),
                },
                signal: controller.signal,
            });

            const html = await response.text();
            const nextDocument = new DOMParser().parseFromString(html, 'text/html');
            const nextDashboardShell = nextDocument.querySelector('[data-dashboard-shell]');
            const finalUrl = response.url || url;

            if (!response.ok) {
                if (!options.quiet) {
                    showDashboardWarning({
                        title: 'Dashboard request failed',
                        message: `The server returned ${response.status}. You can retry this request or reload the page.`,
                    });
                }

                return false;
            }

            if (!nextDashboardShell) {
                if (!options.quiet) {
                    showDashboardWarning({
                        title: 'Dashboard response was incomplete',
                        message: 'The dashboard could not finish loading this view. Try the request again or reload the page.',
                    });
                }
                return false;
            }

            const render = () => applyDashboardDocument(nextDocument, finalUrl, {
                historyMode: options.historyMode ?? 'replace',
                preserveScroll,
                scrollPositions,
                quiet: Boolean(options.quiet),
                warnOnErrors: Boolean(options.warnOnErrors),
            });

            if (!options.quiet && typeof document.startViewTransition === 'function') {
                await document.startViewTransition(() => Promise.resolve(render())).finished;
            } else {
                render();
            }

            return true;
        } catch (error) {
            if (error.name !== 'AbortError' && !options.quiet) {
                showDashboardWarning({
                    title: 'Connection lost during dashboard update',
                    message: 'The request did not complete. Check the connection or retry the update.',
                });
            }

            return false;
        } finally {
            if (state.activeController === controller) {
                state.activeController = null;
            }

            state.requestInFlight = false;

            if (!options.quiet) {
                setDashboardBusyState(false);
            }
        }
    };

    const shouldSkipAutoRefresh = () => {
        if (!isDashboardActive() || document.hidden || state.requestInFlight) {
            return true;
        }

        if (isDashboardWarningVisible()) {
            return true;
        }

        if (document.body.classList.contains('overflow-hidden')) {
            return true;
        }

        const shell = getDashboardShell();
        if (!shell || shell.dataset.dashboardPanel === 'events') {
            return true;
        }

        const activeElement = document.activeElement;
        if (
            activeElement instanceof HTMLElement
            && (
                activeElement.isContentEditable
                || ['INPUT', 'TEXTAREA', 'SELECT'].includes(activeElement.tagName)
            )
        ) {
            return true;
        }

        return Boolean(document.querySelector('form[data-dashboard-dirty="true"]'));
    };

    document.addEventListener('click', (event) => {
        if (event.defaultPrevented || !isDashboardActive()) {
            return;
        }

        const link = event.target.closest('a[href]');
        if (!link || link.hasAttribute('download') || link.dataset.dashboardSoftIgnore !== undefined) {
            return;
        }

        if (link.target && link.target !== '_self') {
            return;
        }

        if (event.metaKey || event.ctrlKey || event.shiftKey || event.altKey || event.button !== 0) {
            return;
        }

        const href = link.getAttribute('href');
        if (!href || href.startsWith('#') || !isDashboardUrl(link.href)) {
            return;
        }

        event.preventDefault();
        state.retryRequest = () => requestDashboardDocument(link.href, {
            historyMode: 'push',
            preserveScroll: false,
        });
        window.__dashboardRetryRequest = state.retryRequest;
        requestDashboardDocument(link.href, {
            historyMode: 'push',
            preserveScroll: false,
        });
    });

    document.addEventListener('submit', (event) => {
        if (event.defaultPrevented || !isDashboardActive()) {
            return;
        }

        const form = event.target;
        if (!(form instanceof HTMLFormElement) || form.dataset.dashboardSoftIgnore !== undefined) {
            return;
        }

        const method = (form.getAttribute('method') || 'GET').toUpperCase();
        if (method === 'GET') {
            return;
        }

        const action = form.action || window.location.href;
        const actionUrl = new URL(action, window.location.href);
        if (actionUrl.origin !== window.location.origin || actionUrl.pathname === '/logout') {
            return;
        }

        event.preventDefault();
        form.dataset.dashboardDirty = 'false';

        const submitter = event.submitter;
        const buildFormData = () => {
            const formData = new FormData(form);

            if (submitter instanceof HTMLElement && submitter.getAttribute('name')) {
                formData.append(
                    submitter.getAttribute('name'),
                    submitter.getAttribute('value') || ''
                );
            }

            return formData;
        };

        state.retryRequest = () => requestDashboardDocument(actionUrl.toString(), {
            method,
            body: buildFormData(),
            historyMode: 'push',
            preserveScroll: true,
            warnOnErrors: true,
        });
        window.__dashboardRetryRequest = state.retryRequest;

        requestDashboardDocument(actionUrl.toString(), {
            method,
            body: buildFormData(),
            historyMode: 'push',
            preserveScroll: true,
            warnOnErrors: true,
        });
    });

    document.addEventListener('input', (event) => {
        if (!isDashboardActive()) {
            return;
        }

        const form = event.target instanceof HTMLElement ? event.target.closest('form') : null;
        if (form && form.dataset.dashboardSoftIgnore === undefined) {
            form.dataset.dashboardDirty = 'true';
        }
    });

    document.addEventListener('change', (event) => {
        if (!isDashboardActive()) {
            return;
        }

        const form = event.target instanceof HTMLElement ? event.target.closest('form') : null;
        if (form && form.dataset.dashboardSoftIgnore === undefined) {
            form.dataset.dashboardDirty = 'true';
        }
    });

    document.addEventListener('keydown', (event) => {
        if (event.key !== 'Escape' || !isDashboardActive()) {
            return;
        }

        if (isDashboardWarningVisible()) {
            hideDashboardWarning();
            return;
        }

        const visibleModals = getVisibleDashboardModals();
        const lastModal = visibleModals[visibleModals.length - 1];
        if (lastModal) {
            closeDashboardModal(lastModal);
        }
    });

    window.addEventListener('popstate', () => {
        if (!isDashboardActive() || !isDashboardUrl(window.location.href)) {
            return;
        }

        requestDashboardDocument(window.location.href, {
            historyMode: 'skip',
            preserveScroll: false,
        });
    });

    window.addEventListener('focus', () => {
        if (shouldSkipAutoRefresh()) {
            return;
        }

        requestDashboardDocument(window.location.href, {
            historyMode: 'replace',
            preserveScroll: true,
            quiet: true,
        });
    });

    state.refreshTimer = window.setInterval(() => {
        if (shouldSkipAutoRefresh()) {
            return;
        }

        requestDashboardDocument(window.location.href, {
            historyMode: 'replace',
            preserveScroll: true,
            quiet: true,
        });
    }, 20000);
}

function bindDashboardBodyInteractions() {
    if (!document.querySelector('[data-dashboard-shell]')) {
        syncDashboardScrollLock();
        return;
    }

    syncDashboardScrollLock();
    bindDashboardWarningOverlay();
    bindDashboardRegisterModal();
    bindDashboardDeckModal();
    bindDashboardParticipantsModal();
    bindDashboardLockedParticipantModals();
    bindDashboardWorkspaceMatchModal();
    bindDashboardEventModal();
    bindDashboardLeaderboardProfileModal();
    updateDashboardModalScrollLock();

    const dashboardShell = document.querySelector('[data-dashboard-shell]');
    if (dashboardShell?.dataset.dashboardErrorState === 'true') {
        window.requestAnimationFrame(() => {
            showDashboardWarning({
                title: 'Action needs attention',
                message: 'The dashboard loaded with errors. Review the highlighted details before continuing.',
                allowRetry: false,
            });
        });
    }
}

function syncDashboardScrollLock() {
    const dashboardShell = document.querySelector('[data-dashboard-shell]');
    document.documentElement.style.overflowY = dashboardShell ? 'hidden' : '';
    document.body.style.overflowY = dashboardShell ? 'hidden' : '';
}

function showDashboardLoader(message = 'Syncing admin view...') {
    const loader = document.querySelector('[data-dashboard-loader]');
    if (!loader) {
        return;
    }

    const label = loader.querySelector('[data-dashboard-loader-label]');
    if (label) {
        label.textContent = message;
    }

    loader.classList.remove('opacity-0', 'pointer-events-none');
    loader.classList.add('opacity-100');
    loader.setAttribute('aria-hidden', 'false');
}

function hideDashboardLoader() {
    const loader = document.querySelector('[data-dashboard-loader]');
    if (!loader) {
        return;
    }

    loader.classList.remove('opacity-100');
    loader.classList.add('opacity-0', 'pointer-events-none');
    loader.setAttribute('aria-hidden', 'true');
}

function isDashboardWarningVisible() {
    const warning = document.querySelector('[data-dashboard-warning]');
    return Boolean(warning && warning.getAttribute('aria-hidden') === 'false');
}

function showDashboardWarning(options = {}) {
    const warning = document.querySelector('[data-dashboard-warning]');
    if (!warning) {
        return;
    }

    const title = warning.querySelector('[data-dashboard-warning-title]');
    const message = warning.querySelector('[data-dashboard-warning-message]');
    const retryButton = warning.querySelector('[data-dashboard-warning-retry]');

    if (title) {
        title.textContent = options.title || 'Dashboard update needs attention';
    }

    if (message) {
        message.textContent = options.message || 'The dashboard could not finish this request. You can retry the action or reload the page.';
    }

    const canRetry = options.allowRetry ?? (typeof window.__dashboardRetryRequest === 'function');
    if (retryButton) {
        retryButton.classList.toggle('hidden', !canRetry);
    }

    hideDashboardLoader();
    warning.classList.remove('opacity-0', 'pointer-events-none');
    warning.classList.add('opacity-100');
    warning.setAttribute('aria-hidden', 'false');
    updateDashboardModalScrollLock();
}

function hideDashboardWarning() {
    const warning = document.querySelector('[data-dashboard-warning]');
    if (!warning) {
        return;
    }

    warning.classList.remove('opacity-100');
    warning.classList.add('opacity-0', 'pointer-events-none');
    warning.setAttribute('aria-hidden', 'true');
    updateDashboardModalScrollLock();
}

function bindDashboardWarningOverlay() {
    const warning = document.querySelector('[data-dashboard-warning]');
    if (!warning || warning.dataset.dashboardBound === 'true') {
        return;
    }

    warning.dataset.dashboardBound = 'true';

    const dismissButton = warning.querySelector('[data-dashboard-warning-dismiss]');
    const retryButton = warning.querySelector('[data-dashboard-warning-retry]');
    const reloadButton = warning.querySelector('[data-dashboard-warning-reload]');

    dismissButton?.addEventListener('click', () => hideDashboardWarning());

    retryButton?.addEventListener('click', () => {
        hideDashboardWarning();
        if (window.__dashboardRetryRequest) {
            window.__dashboardRetryRequest();
        }
    });

    reloadButton?.addEventListener('click', () => {
        window.location.reload();
    });
}

function getVisibleDashboardModals() {
    return Array.from(document.querySelectorAll([
        '[data-register-modal]',
        '[data-deck-modal]',
        '[data-participants-modal]',
        '[data-locked-participant-modal]',
        '[data-workspace-match-modal]',
        '[data-event-modal]',
        '[data-leaderboard-profile-modal]',
    ].join(','))).filter((modal) => !modal.classList.contains('hidden'));
}

function updateDashboardModalScrollLock() {
    if (getVisibleDashboardModals().length > 0 || isDashboardWarningVisible()) {
        document.body.classList.add('overflow-hidden');
        return;
    }

    document.body.classList.remove('overflow-hidden');
}

function openDashboardModal(modal) {
    if (!modal) {
        return;
    }

    modal.classList.remove('hidden');
    modal.classList.add('flex');
    document.body.classList.add('overflow-hidden');
}

function closeDashboardModal(modal) {
    if (!modal) {
        return;
    }

    modal.classList.add('hidden');
    modal.classList.remove('flex');

    if (modal.matches('[data-register-modal]')) {
        const feedbackLabel = modal.querySelector('[data-register-feedback]');
        if (feedbackLabel) {
            feedbackLabel.textContent = modal.dataset.defaultFeedback || '';
            feedbackLabel.classList.remove('text-emerald-300', 'text-rose-300');
            feedbackLabel.classList.add('text-slate-500');
        }
    }

    if (modal.matches('[data-workspace-match-modal]')) {
        const body = modal.querySelector('[data-workspace-match-modal-body]');
        if (body) {
            body.innerHTML = '';
        }
    }

    if (modal.matches('[data-event-modal]')) {
        const body = modal.querySelector('[data-event-modal-body]');
        if (body) {
            body.innerHTML = '';
        }
    }

    if (modal.matches('[data-leaderboard-profile-modal]')) {
        const body = modal.querySelector('[data-leaderboard-profile-body]');
        if (body) {
            body.innerHTML = '';
        }
    }

    updateDashboardModalScrollLock();
}

function bindDashboardRegisterModal() {
    const registerModal = document.querySelector('[data-register-modal]');
    if (!registerModal || registerModal.dataset.dashboardBound === 'true') {
        return;
    }

    registerModal.dataset.dashboardBound = 'true';

    const openButtons = document.querySelectorAll('[data-register-modal-open]');
    const closeButtons = registerModal.querySelectorAll('[data-register-modal-close]');
    const existingSelect = registerModal.querySelector('[data-register-existing]');
    const addExistingButton = registerModal.querySelector('[data-register-existing-add]');
    const newNicknameInput = registerModal.querySelector('[data-register-new]');
    const addNewButton = registerModal.querySelector('[data-register-new-add]');
    const selectedContainer = registerModal.querySelector('[data-register-selected]');
    const hiddenInputsContainer = registerModal.querySelector('[data-register-hidden-inputs]');
    const countLabel = registerModal.querySelector('[data-register-count]');
    const feedbackLabel = registerModal.querySelector('[data-register-feedback]');
    const submitButton = registerModal.querySelector('[data-register-submit]');
    const registerFormElement = registerModal.querySelector('[data-register-form]');
    const selectedNicknames = new Map();
    const defaultFeedback = feedbackLabel ? feedbackLabel.textContent : '';
    registerModal.dataset.defaultFeedback = defaultFeedback;

    const normalizeNickname = (value) => value.trim().replace(/\s+/g, ' ');
    const nicknameKey = (value) => normalizeNickname(value).toLocaleLowerCase();

    const setFeedback = (message, tone = 'neutral') => {
        if (!feedbackLabel) {
            return;
        }

        feedbackLabel.textContent = message;
        feedbackLabel.classList.remove('text-slate-500', 'text-emerald-300', 'text-rose-300');

        if (tone === 'success') {
            feedbackLabel.classList.add('text-emerald-300');
            return;
        }

        if (tone === 'error') {
            feedbackLabel.classList.add('text-rose-300');
            return;
        }

        feedbackLabel.classList.add('text-slate-500');
    };

    const renderSelectedNicknames = () => {
        if (!selectedContainer || !hiddenInputsContainer) {
            return;
        }

        selectedContainer.innerHTML = '';
        hiddenInputsContainer.innerHTML = '';

        if (countLabel) {
            countLabel.textContent = `${selectedNicknames.size} selected`;
        }

        if (submitButton) {
            submitButton.disabled = selectedNicknames.size === 0;
        }

        if (selectedNicknames.size === 0) {
            const emptyState = document.createElement('p');
            emptyState.className = 'text-sm text-slate-500';
            emptyState.textContent = 'No players selected yet.';
            selectedContainer.appendChild(emptyState);
            return;
        }

        selectedNicknames.forEach((nickname, key) => {
            const row = document.createElement('div');
            row.className = 'flex items-center justify-between gap-2 border border-slate-800/80 bg-slate-950/65 px-2.5 py-1.5';

            const name = document.createElement('span');
            name.className = 'min-w-0 flex-1 truncate text-sm text-slate-100';
            name.textContent = nickname;

            const removeButton = document.createElement('button');
            removeButton.type = 'button';
            removeButton.className = 'type-label border border-rose-500/60 px-2 py-1 text-[9px] text-rose-200 transition hover:bg-rose-500/10';
            removeButton.textContent = 'Remove';
            removeButton.addEventListener('click', () => {
                selectedNicknames.delete(key);
                renderSelectedNicknames();
                setFeedback(`${nickname} removed from the selection.`, 'neutral');
            });

            const hiddenInput = document.createElement('input');
            hiddenInput.type = 'hidden';
            hiddenInput.name = 'selected_nicknames[]';
            hiddenInput.value = nickname;

            row.appendChild(name);
            row.appendChild(removeButton);
            selectedContainer.appendChild(row);
            hiddenInputsContainer.appendChild(hiddenInput);
        });
    };

    const addNickname = (rawNickname, successMessage) => {
        const nickname = normalizeNickname(rawNickname);

        if (!nickname) {
            setFeedback('Choose or enter a nickname first.', 'error');
            return;
        }

        const key = nicknameKey(nickname);
        if (selectedNicknames.has(key)) {
            setFeedback(`${nickname} is already selected.`, 'error');
            return;
        }

        selectedNicknames.set(key, nickname);
        renderSelectedNicknames();
        setFeedback(successMessage || `${nickname} added to the selection.`, 'success');
    };

    const openRegisterModal = () => openDashboardModal(registerModal);
    const closeRegisterModal = () => closeDashboardModal(registerModal);

    if (hiddenInputsContainer) {
        hiddenInputsContainer.querySelectorAll('input[name="selected_nicknames[]"]').forEach((input) => {
            const nickname = normalizeNickname(input.value);
            if (!nickname) {
                return;
            }

            selectedNicknames.set(nicknameKey(nickname), nickname);
        });
    }

    renderSelectedNicknames();

    openButtons.forEach((button) => {
        button.addEventListener('click', openRegisterModal);
    });

    closeButtons.forEach((button) => {
        button.addEventListener('click', closeRegisterModal);
    });

    addExistingButton?.addEventListener('click', () => {
        const selectedOptions = existingSelect ? Array.from(existingSelect.selectedOptions) : [];

        if (selectedOptions.length === 0) {
            setFeedback('Select at least one registered user to add.', 'error');
            return;
        }

        selectedOptions.forEach((option) => {
            if (option.value) {
                addNickname(option.value, `${option.value} added from registered users.`);
            }
        });
    });

    existingSelect?.addEventListener('dblclick', () => {
        const firstOption = Array.from(existingSelect.selectedOptions).find((option) => option.value);
        if (firstOption) {
            addNickname(firstOption.value, `${firstOption.value} added from registered users.`);
        }
    });

    newNicknameInput?.addEventListener('keydown', (event) => {
        if (event.key !== 'Enter') {
            return;
        }

        event.preventDefault();
        addNickname(newNicknameInput.value, `${normalizeNickname(newNicknameInput.value)} added as a new user.`);
        newNicknameInput.value = '';
    });

    addNewButton?.addEventListener('click', () => {
        if (!newNicknameInput) {
            return;
        }

        const nickname = normalizeNickname(newNicknameInput.value);
        addNickname(nickname, `${nickname} added as a new user.`);
        newNicknameInput.value = '';
    });

    registerFormElement?.addEventListener('submit', (event) => {
        if (selectedNicknames.size > 0) {
            return;
        }

        event.preventDefault();
        setFeedback('Add at least one player before confirming.', 'error');
    });

    registerModal.addEventListener('click', (event) => {
        if (event.target === registerModal) {
            closeRegisterModal();
        }
    });

    if (registerModal.dataset.registerOpenOnLoad === 'true') {
        openRegisterModal();
    }
}

function bindDashboardDeckModal() {
    const deckModal = document.querySelector('[data-deck-modal]');
    if (!deckModal || deckModal.dataset.dashboardBound === 'true') {
        return;
    }

    deckModal.dataset.dashboardBound = 'true';

    const openButtons = document.querySelectorAll('[data-deck-modal-open]');
    const closeButtons = deckModal.querySelectorAll('[data-deck-modal-close]');
    const deckScrollBody = deckModal.querySelector('[data-deck-scroll-body]');
    const deckBulkForm = deckModal.querySelector('[data-deck-bulk-form]');
    const deckBulkInputs = deckModal.querySelector('[data-deck-bulk-inputs]');
    const deckBulkSubmitButton = deckModal.querySelector('[data-deck-bulk-submit]');

    const focusDeckRow = () => {
        const playerId = deckModal.dataset.deckFocusPlayerId;
        if (!playerId || !deckScrollBody) {
            return;
        }

        const targetRow = deckModal.querySelector(`[data-deck-player-row="${playerId}"]`);
        if (!targetRow) {
            return;
        }

        targetRow.scrollIntoView({ block: 'center', behavior: 'auto' });
    };

    const appendBulkInput = (name, value) => {
        if (!deckBulkInputs) {
            return;
        }

        const input = document.createElement('input');
        input.type = 'hidden';
        input.name = name;
        input.value = value;
        deckBulkInputs.appendChild(input);
    };

    const openDeckModal = () => {
        openDashboardModal(deckModal);
        window.requestAnimationFrame(focusDeckRow);
    };

    const closeDeckModal = () => closeDashboardModal(deckModal);

    openButtons.forEach((button) => {
        button.addEventListener('click', openDeckModal);
    });

    closeButtons.forEach((button) => {
        button.addEventListener('click', closeDeckModal);
    });

    deckBulkSubmitButton?.addEventListener('click', () => {
        if (!deckBulkForm || !deckBulkInputs) {
            return;
        }

        deckBulkForm.setAttribute('action', deckModal.dataset.deckBulkAction || '');
        deckBulkInputs.innerHTML = '';

        deckModal.querySelectorAll('[data-deck-player-row]').forEach((row) => {
            const playerId = row.dataset.deckPlayerRow;
            if (!playerId) {
                return;
            }

            const bey1 = row.querySelector('input[name="deck_bey1"]');
            const bey2 = row.querySelector('input[name="deck_bey2"]');
            const bey3 = row.querySelector('input[name="deck_bey3"]');

            appendBulkInput(`decks[${playerId}][deck_bey1]`, bey1?.value || '');
            appendBulkInput(`decks[${playerId}][deck_bey2]`, bey2?.value || '');
            appendBulkInput(`decks[${playerId}][deck_bey3]`, bey3?.value || '');
        });

        deckBulkForm.submit();
    });

    deckModal.addEventListener('click', (event) => {
        if (event.target === deckModal) {
            closeDeckModal();
        }
    });

    if (deckModal.dataset.deckOpenOnLoad === 'true') {
        openDeckModal();
    }
}

function bindDashboardParticipantsModal() {
    const participantsModal = document.querySelector('[data-participants-modal]');
    if (!participantsModal || participantsModal.dataset.dashboardBound === 'true') {
        return;
    }

    participantsModal.dataset.dashboardBound = 'true';

    const openButtons = document.querySelectorAll('[data-participants-modal-open]');
    const closeButtons = participantsModal.querySelectorAll('[data-participants-modal-close]');

    const openParticipantsModal = () => openDashboardModal(participantsModal);
    const closeParticipantsModal = () => closeDashboardModal(participantsModal);

    openButtons.forEach((button) => {
        button.addEventListener('click', openParticipantsModal);
    });

    closeButtons.forEach((button) => {
        button.addEventListener('click', closeParticipantsModal);
    });

    participantsModal.addEventListener('click', (event) => {
        if (event.target === participantsModal) {
            closeParticipantsModal();
        }
    });
}

function bindDashboardLockedParticipantModals() {
    const lockedParticipantModals = document.querySelectorAll('[data-locked-participant-modal]');
    if (lockedParticipantModals.length === 0) {
        return;
    }

    document.querySelectorAll('[data-locked-participant-open]').forEach((button) => {
        if (button.dataset.dashboardBound === 'true') {
            return;
        }

        button.dataset.dashboardBound = 'true';
        button.addEventListener('click', () => {
            const targetId = button.dataset.lockedParticipantOpen;
            const modal = Array.from(lockedParticipantModals).find((item) => item.dataset.lockedParticipantModal === targetId);
            if (modal) {
                openDashboardModal(modal);
            }
        });
    });

    lockedParticipantModals.forEach((modal) => {
        if (modal.dataset.dashboardBound === 'true') {
            return;
        }

        modal.dataset.dashboardBound = 'true';

        modal.querySelectorAll('[data-locked-participant-close]').forEach((button) => {
            button.addEventListener('click', () => closeDashboardModal(modal));
        });

        modal.addEventListener('click', (event) => {
            if (event.target === modal) {
                closeDashboardModal(modal);
            }
        });

        if (modal.dataset.lockedParticipantOpenOnLoad === 'true') {
            openDashboardModal(modal);
        }
    });
}

function bindDashboardWorkspaceMatchModal() {
    const workspaceMatchModal = document.querySelector('[data-workspace-match-modal]');
    if (!workspaceMatchModal || workspaceMatchModal.dataset.dashboardBound === 'true') {
        return;
    }

    workspaceMatchModal.dataset.dashboardBound = 'true';

    const workspaceMatchButtons = document.querySelectorAll('[data-workspace-match-open]');
    const workspaceMatchCloseButtons = workspaceMatchModal.querySelectorAll('[data-workspace-match-close]');
    const workspaceMatchTitle = workspaceMatchModal.querySelector('[data-workspace-match-modal-title]');
    const workspaceMatchSubtitle = workspaceMatchModal.querySelector('[data-workspace-match-modal-subtitle]');
    const workspaceMatchBody = workspaceMatchModal.querySelector('[data-workspace-match-modal-body]');

    const openWorkspaceMatchModal = ({ templateId, title = '', subtitle = '' }) => {
        const template = templateId ? document.getElementById(templateId) : null;
        if (!template || !workspaceMatchBody) {
            return;
        }

        if (workspaceMatchTitle) {
            workspaceMatchTitle.textContent = title || template.dataset.matchModalTitle || '';
        }

        if (workspaceMatchSubtitle) {
            workspaceMatchSubtitle.textContent = subtitle || template.dataset.matchModalSubtitle || '';
        }

        workspaceMatchBody.innerHTML = template.innerHTML;
        openDashboardModal(workspaceMatchModal);
    };

    workspaceMatchButtons.forEach((button) => {
        button.addEventListener('click', () => openWorkspaceMatchModal({
            templateId: button.dataset.matchTemplateId,
            title: button.dataset.matchModalTitle || '',
            subtitle: button.dataset.matchModalSubtitle || '',
        }));
    });

    workspaceMatchCloseButtons.forEach((button) => {
        button.addEventListener('click', () => closeDashboardModal(workspaceMatchModal));
    });

    workspaceMatchModal.addEventListener('click', (event) => {
        if (event.target === workspaceMatchModal) {
            closeDashboardModal(workspaceMatchModal);
        }
    });

    if (workspaceMatchModal.dataset.openTemplateId) {
        openWorkspaceMatchModal({
            templateId: workspaceMatchModal.dataset.openTemplateId,
            title: workspaceMatchModal.dataset.openTitle || '',
            subtitle: workspaceMatchModal.dataset.openSubtitle || '',
        });
    }
}

function bindDashboardEventModal() {
    const modal = document.querySelector('[data-event-modal]');
    if (!modal || modal.dataset.dashboardBound === 'true') {
        return;
    }

    modal.dataset.dashboardBound = 'true';

    const modalBody = modal.querySelector('[data-event-modal-body]');
    const closeButton = modal.querySelector('[data-event-modal-close]');

    const openModal = (trigger) => {
        const templateId = trigger.dataset.eventPreviewTemplateId;
        const template = templateId ? document.getElementById(templateId) : null;
        if (!template || !modalBody) {
            return;
        }

        modalBody.innerHTML = template.innerHTML;
        openDashboardModal(modal);
    };

    document.querySelectorAll('[data-event-preview-open]').forEach((card) => {
        card.addEventListener('click', () => openModal(card));
    });

    closeButton?.addEventListener('click', () => closeDashboardModal(modal));

    modal.addEventListener('click', (event) => {
        if (event.target === modal) {
            closeDashboardModal(modal);
        }
    });
}

function bindDashboardLeaderboardProfileModal() {
    const leaderboardProfileModal = document.querySelector('[data-leaderboard-profile-modal]');
    if (!leaderboardProfileModal || leaderboardProfileModal.dataset.dashboardBound === 'true') {
        return;
    }

    leaderboardProfileModal.dataset.dashboardBound = 'true';

    const openButtons = document.querySelectorAll('[data-leaderboard-profile-open]');
    const closeButton = leaderboardProfileModal.querySelector('[data-leaderboard-profile-close]');
    const modalBody = leaderboardProfileModal.querySelector('[data-leaderboard-profile-body]');

    const openLeaderboardProfileModal = (trigger) => {
        const templateId = trigger.dataset.leaderboardProfileTemplateId;
        const template = templateId ? document.getElementById(templateId) : null;
        if (!template || !modalBody) {
            return;
        }

        modalBody.innerHTML = template.innerHTML;
        openDashboardModal(leaderboardProfileModal);
    };

    openButtons.forEach((button) => {
        button.addEventListener('click', () => openLeaderboardProfileModal(button));
    });

    closeButton?.addEventListener('click', () => closeDashboardModal(leaderboardProfileModal));

    leaderboardProfileModal.addEventListener('click', (event) => {
        if (event.target === leaderboardProfileModal) {
            closeDashboardModal(leaderboardProfileModal);
        }
    });
}
