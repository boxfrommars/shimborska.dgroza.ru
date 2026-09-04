document.addEventListener('DOMContentLoaded', () => {
    const dialog = document.querySelector('#content');
    const illustrationDialog = document.querySelector('#illustration-dialog');
    const currentPage = document.querySelector('#center-bottom-nav');
    const platform = navigator.userAgentData?.platform || navigator.platform || '';
    const isMac = platform.toLowerCase().startsWith('mac');
    const shortcutDirections = {
        previous: { symbol: '←', name: 'влево' },
        next: { symbol: '→', name: 'вправо' },
        cover: { symbol: '↓', name: 'вниз' },
        contents: { symbol: '↑', name: 'вверх' },
    };

    if (!dialog) return;

    function renderShortcut(shortcut, action) {
        const direction = shortcutDirections[action];
        if (!direction) return;

        const visualLabel = document.createElement('span');
        visualLabel.setAttribute('aria-hidden', 'true');
        visualLabel.textContent = isMac
            ? `(⌃⇧${direction.symbol})`
            : `(ctrl + ${direction.symbol})`;

        const accessibleLabel = document.createElement('span');
        accessibleLabel.className = 'visually-hidden';
        accessibleLabel.textContent = isMac
            ? `Горячая клавиша: Control, Shift и стрелка ${direction.name}`
            : `Горячая клавиша: Control и стрелка ${direction.name}`;

        shortcut.replaceChildren(visualLabel, accessibleLabel);
    }

    function addShortcut(item, action) {
        if (!item || item.matches('.first, .last')) return;

        const shortcut = document.createElement('span');
        shortcut.className = 'shortkey';
        shortcut.dataset.shortcut = action;
        renderShortcut(shortcut, action);
        item.append(shortcut);
    }

    document.querySelectorAll('.shortkey[data-shortcut]').forEach((shortcut) => {
        renderShortcut(shortcut, shortcut.dataset.shortcut);
    });

    addShortcut(currentPage?.previousElementSibling, 'previous');
    addShortcut(currentPage?.nextElementSibling, 'next');

    function positionNotes() {
        const main = document.querySelector('#main');
        if (!main) return;

        const mainTop = main.getBoundingClientRect().top;
        document.querySelectorAll('.tonote').forEach((marker) => {
            const note = document.querySelector(`#note${marker.id.replace('tonote', '')}`);
            if (note) note.style.top = `${marker.getBoundingClientRect().top - mainTop - 30}px`;
        });
    }

    positionNotes();
    if (document.fonts?.ready) document.fonts.ready.then(positionNotes);
    window.addEventListener('load', positionNotes);
    window.addEventListener('resize', positionNotes);
    document.querySelectorAll('.page img').forEach((image) => {
        if (!image.complete) image.addEventListener('load', positionNotes);
    });

    function revealCurrentContentItem() {
        const dialogBody = dialog.querySelector('.dialog-body');
        const currentItem = dialog.querySelector('[aria-current="page"]');
        if (!dialogBody || !currentItem) return;

        const bodyRect = dialogBody.getBoundingClientRect();
        const itemRect = currentItem.getBoundingClientRect();
        const itemIsVisible = itemRect.top >= bodyRect.top && itemRect.bottom <= bodyRect.bottom;
        if (itemIsVisible) return;

        dialogBody.scrollTop += itemRect.top
            - bodyRect.top
            - (dialogBody.clientHeight - itemRect.height) / 2;
    }

    function openContents() {
        if (!dialog.open) dialog.showModal();
        revealCurrentContentItem();
        document.body.classList.add('content-open');
    }

    function closeContents() {
        if (dialog.open) dialog.close();
    }

    document.querySelectorAll('.show-content-link').forEach((link) => {
        link.addEventListener('click', (event) => {
            event.preventDefault();
            openContents();
        });
    });
    dialog.querySelector('.content-close').addEventListener('click', closeContents);
    dialog.addEventListener('click', (event) => {
        const bounds = dialog.getBoundingClientRect();
        const clickedOutside = event.clientX < bounds.left
            || event.clientX > bounds.right
            || event.clientY < bounds.top
            || event.clientY > bounds.bottom;

        if (clickedOutside) closeContents();
    });
    dialog.addEventListener('close', () => document.body.classList.remove('content-open'));

    if (illustrationDialog && typeof illustrationDialog.showModal === 'function') {
        const title = illustrationDialog.querySelector('#illustration-title');
        const stage = illustrationDialog.querySelector('.illustration-stage');
        const holder = illustrationDialog.querySelector('.illustration-image');
        const caption = illustrationDialog.querySelector('.illustration-caption');
        const status = illustrationDialog.querySelector('.illustration-status');
        const error = illustrationDialog.querySelector('.illustration-error');
        const closeButton = illustrationDialog.querySelector('.illustration-close');
        const titlebar = illustrationDialog.querySelector('.illustration-titlebar');
        let requestId = 0;
        let opener = null;
        let loadedImage = null;
        let resizeFrame = null;
        let scrollPosition = { left: 0, top: 0 };

        function closeIllustration() {
            if (illustrationDialog.open) illustrationDialog.close();
        }

        function numericStyle(style, property) {
            return Number.parseFloat(style.getPropertyValue(property)) || 0;
        }

        function fitIllustration(image) {
            const dialogStyle = getComputedStyle(illustrationDialog);
            const stageStyle = getComputedStyle(stage);
            const viewportGap = numericStyle(dialogStyle, '--illustration-viewport-gap');
            const maxDialogWidth = Math.min(
                numericStyle(dialogStyle, '--illustration-max-width'),
                window.innerWidth - viewportGap,
            );
            const maxDialogHeight = window.innerHeight - viewportGap;
            const dialogBorderWidth = numericStyle(dialogStyle, 'border-left-width')
                + numericStyle(dialogStyle, 'border-right-width');
            const dialogBorderHeight = numericStyle(dialogStyle, 'border-top-width')
                + numericStyle(dialogStyle, 'border-bottom-width');
            const stagePaddingWidth = numericStyle(stageStyle, 'padding-left')
                + numericStyle(stageStyle, 'padding-right');
            const stagePaddingHeight = numericStyle(stageStyle, 'padding-top')
                + numericStyle(stageStyle, 'padding-bottom');
            const availableImageWidth = Math.max(1, maxDialogWidth - dialogBorderWidth - stagePaddingWidth);

            illustrationDialog.style.setProperty('--illustration-dialog-width', `${maxDialogWidth}px`);

            // Re-measure the wrapping caption after each width adjustment.
            for (let pass = 0; pass < 3; pass += 1) {
                const fixedHeight = titlebar.getBoundingClientRect().height
                    + caption.getBoundingClientRect().height
                    + dialogBorderHeight
                    + stagePaddingHeight;
                const availableImageHeight = Math.max(1, maxDialogHeight - fixedHeight - 1);
                const scale = Math.min(
                    1,
                    availableImageWidth / image.naturalWidth,
                    availableImageHeight / image.naturalHeight,
                );
                const imageWidth = image.naturalWidth * scale;
                const imageHeight = image.naturalHeight * scale;

                illustrationDialog.style.setProperty(
                    '--illustration-dialog-width',
                    `${imageWidth + stagePaddingWidth + dialogBorderWidth}px`,
                );
                illustrationDialog.style.setProperty(
                    '--illustration-stage-height',
                    `${imageHeight + stagePaddingHeight}px`,
                );
                illustrationDialog.style.setProperty(
                    '--illustration-dialog-height',
                    `${imageHeight + fixedHeight}px`,
                );
            }
        }

        document.querySelectorAll('.illustrations a[data-illustration]').forEach((link) => {
            const thumbnail = link.querySelector('img');
            if (!thumbnail) return;

            link.setAttribute('aria-haspopup', 'dialog');
            link.setAttribute('aria-controls', illustrationDialog.id);
            link.addEventListener('click', (event) => {
                if (event.defaultPrevented || event.button !== 0 || event.ctrlKey || event.metaKey
                    || event.shiftKey || event.altKey || link.hasAttribute('download')
                    || (link.target && link.target !== '_self')) return;

                event.preventDefault();
                const currentRequest = ++requestId;
                opener = link;
                scrollPosition = { left: window.scrollX, top: window.scrollY };
                title.textContent = link.dataset.illustrationTitle?.trim() || 'Иллюстрация';
                loadedImage = null;
                window.cancelAnimationFrame(resizeFrame);
                resizeFrame = null;
                illustrationDialog.style.removeProperty('--illustration-dialog-width');
                illustrationDialog.style.removeProperty('--illustration-dialog-height');
                illustrationDialog.style.removeProperty('--illustration-stage-height');
                holder.replaceChildren();
                caption.replaceChildren();
                link.closest('.left-box')?.querySelectorAll(':scope > p').forEach((paragraph) => {
                    const copy = document.createElement('p');
                    copy.textContent = paragraph.textContent;
                    caption.append(copy);
                });
                caption.hidden = caption.childElementCount === 0;
                status.hidden = false;
                error.hidden = true;
                error.querySelector('a').href = link.href;
                stage.setAttribute('aria-busy', 'true');
                illustrationDialog.showModal();
                document.body.classList.add('illustration-open');
                closeButton.focus({ preventScroll: true });

                // Each opening owns its image; late events cannot replace a newer request.
                const image = new Image();
                image.alt = thumbnail.alt;
                image.onload = () => {
                    if (currentRequest !== requestId || !illustrationDialog.open) return;
                    holder.replaceChildren(image);
                    loadedImage = image;
                    fitIllustration(image);
                    status.hidden = true;
                    stage.setAttribute('aria-busy', 'false');
                };
                image.onerror = () => {
                    if (currentRequest !== requestId || !illustrationDialog.open) return;
                    status.hidden = true;
                    error.hidden = false;
                    stage.setAttribute('aria-busy', 'false');
                };
                image.src = link.href;
            });
        });

        closeButton.addEventListener('click', closeIllustration);
        illustrationDialog.addEventListener('cancel', (event) => {
            event.preventDefault();
            closeIllustration();
        });
        illustrationDialog.addEventListener('click', (event) => {
            const bounds = illustrationDialog.getBoundingClientRect();
            if (event.target === illustrationDialog && (event.clientX < bounds.left
                || event.clientX > bounds.right || event.clientY < bounds.top
                || event.clientY > bounds.bottom)) closeIllustration();
        });
        illustrationDialog.addEventListener('close', () => {
            // A queued close event may arrive after the next opening.
            if (illustrationDialog.open) return;
            ++requestId;
            holder.replaceChildren();
            loadedImage = null;
            window.cancelAnimationFrame(resizeFrame);
            resizeFrame = null;
            illustrationDialog.style.removeProperty('--illustration-dialog-width');
            illustrationDialog.style.removeProperty('--illustration-dialog-height');
            illustrationDialog.style.removeProperty('--illustration-stage-height');
            stage.setAttribute('aria-busy', 'false');
            document.body.classList.remove('illustration-open');
            opener?.focus({ preventScroll: true });
            if (opener) window.scrollTo(scrollPosition);
            opener = null;
        });
        window.addEventListener('resize', () => {
            if (!illustrationDialog.open || !loadedImage) return;
            window.cancelAnimationFrame(resizeFrame);
            resizeFrame = window.requestAnimationFrame(() => {
                if (illustrationDialog.open && loadedImage) fitIllustration(loadedImage);
            });
        });
    }

    function goToAdjacentPage(direction) {
        const item = direction === 'previous'
            ? currentPage?.previousElementSibling
            : currentPage?.nextElementSibling;
        const link = item && !item.matches('.first, .last') ? item.querySelector('a') : null;
        location.href = link?.href || '/';
    }

    function hasShortcutModifiers(event) {
        if (event.altKey || event.metaKey) return false;

        return isMac
            ? event.ctrlKey && event.shiftKey
            : event.ctrlKey && !event.shiftKey;
    }

    function isEditableTarget(target) {
        return target instanceof Element
            && target.closest('input, textarea, select, [contenteditable]:not([contenteditable="false"])') !== null;
    }

    document.addEventListener('keydown', (event) => {
        if (illustrationDialog?.open) {
            if (hasShortcutModifiers(event) && event.key.startsWith('Arrow')) event.preventDefault();
            return;
        }
        if (event.key === 'Escape' && dialog.open) {
            event.preventDefault();
            closeContents();
            return;
        }
        if (isEditableTarget(event.target) || !hasShortcutModifiers(event)) return;

        if (event.key === 'ArrowLeft') goToAdjacentPage('previous');
        else if (event.key === 'ArrowRight') goToAdjacentPage('next');
        else if (event.key === 'ArrowDown') location.href = '/';
        else if (event.key === 'ArrowUp') dialog.open ? closeContents() : openContents();
        else return;

        event.preventDefault();
    });
});
