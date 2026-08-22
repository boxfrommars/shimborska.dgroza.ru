document.addEventListener('DOMContentLoaded', () => {
    const dialog = document.querySelector('#content');
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
