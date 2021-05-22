let listElem;
let clearSearchBtn;
let filterInput;

document.addEventListener('DOMContentLoaded', function () {
    listElem = document.querySelector('#routes-list');
    clearSearchBtn = document.querySelector('#clear-search');
    filterInput = document.querySelector('#routes-filter');

    filterList();

    filterInput.onkeyup = function () {
        filterList();
    };

    clearSearchBtn.onclick = function () {
        filterInput.value = '';
        filterList();
    };
});

function filterList() {
    let rows = listElem.querySelectorAll('tr');
    let term = filterInput.value.toString().toLowerCase();

    for (let index in rows) {
        if (rows.hasOwnProperty(index)) {
            let row = rows[index];

            let uri = row.querySelector('.route-uri');
            let description = row.querySelector('.route-description');

            let uriContainsTerm = uri.textContent.toLowerCase().indexOf(term) !== -1;
            let descriptionContainsTerm = description && description.dataset.text && description.dataset.text.toLowerCase().indexOf(term) !== -1;

            let show = uriContainsTerm || descriptionContainsTerm;

            if (show) {
                row.style.display = 'table-row';
            } else {
                row.style.display = 'none';
            }
        }
    }

    clearSearchBtn.disabled = term.length <= 0;
}