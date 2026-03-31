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
        const root = document.documentElement;
        const body = document.body;
        const introFit = introHalf.querySelector('[data-intro-fit]');
        const stickyHeader = document.querySelector('header.sticky');
        let minSecondHalfTop = null;
        let secondHalfEntered = false;

        const lockInitialScroll = () => {
            root.style.overflowY = 'hidden';
            body.style.overflowY = 'hidden';
            window.scrollTo({ top: 0, behavior: 'auto' });
        };

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

        const unlockScroll = () => {
            root.style.overflowY = '';
            body.style.overflowY = '';
        };

        const teardownIntroFit = () => {
            window.removeEventListener('resize', scheduleIntroFit);
            window.removeEventListener('load', scheduleIntroFit);
            if (introFit) {
                introFit.style.transform = '';
                introFit.style.transformOrigin = '';
            }
        };

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

        const keepSecondHalfLocked = () => {
            if (minSecondHalfTop === null) {
                return;
            }

            if (window.scrollY < minSecondHalfTop) {
                window.scrollTo({ top: minSecondHalfTop, behavior: 'auto' });
            }
        };

        const getSecondHalfTargetTop = () => {
            const secondHalfTop = secondHalf.getBoundingClientRect().top + window.scrollY;
            const headerHeight = stickyHeader ? stickyHeader.getBoundingClientRect().height : 0;
            const revealOffset = 12;
            return Math.max(0, secondHalfTop - headerHeight - revealOffset);
        };

        lockInitialScroll();
        fitIntroToViewport();
        window.requestAnimationFrame(fitIntroToViewport);
        window.addEventListener('resize', scheduleIntroFit);
        window.addEventListener('load', scheduleIntroFit);
        if (document.fonts && document.fonts.ready) {
            document.fonts.ready.then(scheduleIntroFit);
        }

        const clashDurationMs = 1660;
        const clashTransitionMs = clashDurationMs;
        const lockSecondHalfDelayMs = 500;
        const immediateLockDelayMs = 120;

        const revealSecondHalf = (lockDelayMs) => {
            unlockScroll();
            teardownIntroFit();

            minSecondHalfTop = getSecondHalfTargetTop();
            window.scrollTo({ top: minSecondHalfTop, behavior: 'smooth' });

            window.setTimeout(() => {
                keepSecondHalfLocked();
                window.addEventListener('scroll', keepSecondHalfLocked, { passive: true });
            }, lockDelayMs);
        };

        const enterSecondHalf = ({ withClash }) => {
            if (secondHalfEntered) {
                return;
            }

            secondHalfEntered = true;
            setIntroButtonsDisabled(true);

            if (withClash) {
                body.classList.add('clash-play');
                window.setTimeout(() => {
                    revealSecondHalf(lockSecondHalfDelayMs);
                }, clashTransitionMs);
                return;
            }

            body.classList.remove('clash-play');
            revealSecondHalf(immediateLockDelayMs);
        };

        proceedButton.addEventListener('click', () => {
            enterSecondHalf({ withClash: true });
        });

        skipIntroButton?.addEventListener('click', () => {
            enterSecondHalf({ withClash: false });
        });

        if (debugReturnButton) {
            debugReturnButton.addEventListener('click', () => {
                window.removeEventListener('scroll', keepSecondHalfLocked);
                minSecondHalfTop = null;
                secondHalfEntered = false;
                body.classList.remove('clash-play');
                setIntroButtonsDisabled(false);

                lockInitialScroll();
                fitIntroToViewport();
                window.requestAnimationFrame(fitIntroToViewport);
                window.addEventListener('resize', scheduleIntroFit);
                window.addEventListener('load', scheduleIntroFit);
                if (document.fonts && document.fonts.ready) {
                    document.fonts.ready.then(scheduleIntroFit);
                }
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

    const floatingRails = document.querySelectorAll('[data-float-rail]');
    const eventsAnchor = document.querySelector('[data-events-anchor]');
    const desktopMedia = window.matchMedia('(min-width: 1280px)');

    if (floatingRails.length > 0 && eventsAnchor) {
        const stickyTop = 96; // Matches roughly top-24 with sticky header.

        const updateRailPositions = () => {
            if (!desktopMedia.matches) {
                floatingRails.forEach((rail) => {
                    rail.style.top = '';
                });
                return;
            }

            const anchorTop = eventsAnchor.getBoundingClientRect().top + window.scrollY;
            const top = Math.max(stickyTop, anchorTop - window.scrollY);
            floatingRails.forEach((rail) => {
                rail.style.top = `${top}px`;
            });
        };

        updateRailPositions();
        window.addEventListener('scroll', updateRailPositions, { passive: true });
        window.addEventListener('resize', updateRailPositions);
        desktopMedia.addEventListener('change', updateRailPositions);
    }

    const dashboardShell = document.querySelector('[data-dashboard-shell]');
    if (dashboardShell) {
        document.documentElement.style.overflowY = 'hidden';
        document.body.style.overflowY = 'hidden';
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
