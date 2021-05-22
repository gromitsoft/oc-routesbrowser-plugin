document.addEventListener('keyup', function (event) {
    if (event.code === 'KeyW' && event.altKey) {
        $('#details-tabs').ocTab('closeTab', '.nav-tabs > li.active', true);
    }
});

function getActiveDetailsTab() {
    return document.querySelector('#details-tabs > .tab-content > .tab-pane.active');
}