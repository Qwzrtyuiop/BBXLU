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

    const modal = document.querySelector('[data-event-modal]');
    if (!modal) {
        return;
    }

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
});
