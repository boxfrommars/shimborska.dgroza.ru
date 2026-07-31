document.addEventListener('DOMContentLoaded', () => {
    const dialog = document.querySelector('#content');
    const currentPage = document.querySelector('#center-bottom-nav');

    if (!dialog) return;

    function addShortcut(item, text) {
        if (!item || item.matches('.first, .last')) return;

        const shortcut = document.createElement('span');
        shortcut.className = 'shortkey';
        shortcut.textContent = text;
        item.append(shortcut);
    }

    addShortcut(currentPage?.previousElementSibling, '(ctrl + ←)');
    addShortcut(currentPage?.nextElementSibling, '(ctrl + →)');

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

    document.addEventListener('keydown', (event) => {
        if (event.key === 'Escape' && dialog.open) {
            event.preventDefault();
            closeContents();
            return;
        }
        if (!event.ctrlKey || event.altKey || event.metaKey || event.shiftKey) return;

        if (event.key === 'ArrowLeft') goToAdjacentPage('previous');
        else if (event.key === 'ArrowRight') goToAdjacentPage('next');
        else if (event.key === 'ArrowDown') location.href = '/';
        else if (event.key === 'ArrowUp') dialog.open ? closeContents() : openContents();
        else return;

        event.preventDefault();
    });
});
