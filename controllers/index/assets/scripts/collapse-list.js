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
    $(window).trigger('oc.updateUi');
}
