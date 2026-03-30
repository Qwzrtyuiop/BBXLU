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
    const debugReturnButton = document.querySelector('[data-debug-return]');

    if (introHalf && secondHalf && proceedButton) {
        const root = document.documentElement;
        const body = document.body;
        const introFit = introHalf.querySelector('[data-intro-fit]');
        const stickyHeader = document.querySelector('header.sticky');
        let minSecondHalfTop = null;

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

        proceedButton.addEventListener('click', () => {
            if (proceedButton.disabled) {
                return;
            }

            proceedButton.disabled = true;
            proceedButton.classList.add('opacity-60', 'cursor-not-allowed');
            body.classList.add('clash-play');

            window.setTimeout(() => {
                unlockScroll();
                window.removeEventListener('resize', scheduleIntroFit);
                window.removeEventListener('load', scheduleIntroFit);
                if (introFit) {
                    introFit.style.transform = '';
                    introFit.style.transformOrigin = '';
                }

                minSecondHalfTop = getSecondHalfTargetTop();
                window.scrollTo({ top: minSecondHalfTop, behavior: 'smooth' });

                window.setTimeout(() => {
                    keepSecondHalfLocked();
                    window.addEventListener('scroll', keepSecondHalfLocked, { passive: true });
                }, lockSecondHalfDelayMs);
            }, clashTransitionMs);
        });

        if (debugReturnButton) {
            debugReturnButton.addEventListener('click', () => {
                window.removeEventListener('scroll', keepSecondHalfLocked);
                minSecondHalfTop = null;
                body.classList.remove('clash-play');

                proceedButton.disabled = false;
                proceedButton.classList.remove('opacity-60', 'cursor-not-allowed');

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

    const workspaceMatchModal = document.querySelector('[data-workspace-match-modal]');
    if (workspaceMatchModal) {
        const workspaceMatchButtons = document.querySelectorAll('[data-workspace-match-open]');
        const workspaceMatchCloseButtons = workspaceMatchModal.querySelectorAll('[data-workspace-match-close]');
        const workspaceMatchTitle = workspaceMatchModal.querySelector('[data-workspace-match-modal-title]');
        const workspaceMatchSubtitle = workspaceMatchModal.querySelector('[data-workspace-match-modal-subtitle]');
        const workspaceMatchBody = workspaceMatchModal.querySelector('[data-workspace-match-modal-body]');

        const openWorkspaceMatchModal = (button) => {
            const templateId = button.dataset.matchTemplateId;
            const template = templateId ? document.getElementById(templateId) : null;
            if (!template || !workspaceMatchBody) {
                return;
            }

            if (workspaceMatchTitle) {
                workspaceMatchTitle.textContent = button.dataset.matchModalTitle || '';
            }

            if (workspaceMatchSubtitle) {
                workspaceMatchSubtitle.textContent = button.dataset.matchModalSubtitle || '';
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
            button.addEventListener('click', () => openWorkspaceMatchModal(button));
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
    }

    const modal = document.querySelector('[data-event-modal]');
    if (modal) {
        const modalTitle = modal.querySelector('[data-event-modal-title]');
        const modalType = modal.querySelector('[data-event-modal-type]');
        const modalDate = modal.querySelector('[data-event-modal-date]');
        const modalStatus = modal.querySelector('[data-event-modal-status]');
        const modalLocation = modal.querySelector('[data-event-modal-location]');
        const modalParticipants = modal.querySelector('[data-event-modal-participants]');
        const modalCreatedBy = modal.querySelector('[data-event-modal-created-by]');
        const modalDescription = modal.querySelector('[data-event-modal-description]');
        const closeButton = modal.querySelector('[data-event-modal-close]');

        const openModal = (card) => {
            modalTitle.textContent = card.dataset.eventTitle || '';
            modalType.textContent = card.dataset.eventType || '';
            modalDate.textContent = card.dataset.eventDate || '';
            modalStatus.textContent = card.dataset.eventStatus || '';
            modalLocation.textContent = card.dataset.eventLocation || '';
            modalParticipants.textContent = card.dataset.eventParticipants || '';
            modalCreatedBy.textContent = card.dataset.eventCreatedBy || '';
            modalDescription.textContent = card.dataset.eventDescription || '';

            modal.classList.remove('hidden');
            modal.classList.add('flex');
            document.body.classList.add('overflow-hidden');
        };

        const closeModal = () => {
            modal.classList.add('hidden');
            modal.classList.remove('flex');
            document.body.classList.remove('overflow-hidden');
        };

        document.querySelectorAll('[data-event-card]').forEach((card) => {
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
});
