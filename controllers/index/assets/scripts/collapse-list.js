function collapseList() {
    let routesListContainer = document.querySelector('#route-list-container');
    let routesListPlaceholder = document.querySelector('#routes-list-placeholder');

    routesListContainer.style.display = 'none';
    routesListPlaceholder.style.display = 'block';

    updateParamsScroll();
}

function showList() {
    let routesListContainer = document.querySelector('#route-list-container');
    let routesListPlaceholder = document.querySelector('#routes-list-placeholder');

    routesListContainer.style.display = 'block';
    routesListPlaceholder.style.display = 'none';

    updateParamsScroll();
}

function updateParamsScroll() {
    // $('[data-control="scrollbar"]').each((_, elem) => $(elem).trigger('oc.scrollbar.gotoStart'));
    $(window).trigger('oc.updateUi');
}