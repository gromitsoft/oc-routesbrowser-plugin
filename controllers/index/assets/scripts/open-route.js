/**
 * @param {string} title
 * @param {string} method
 * @param {string} uri
 */
function openRoute(title, method, uri) {
    let content = `
    <div class='loading-indicator-container'>
        <div class='loading-indicator indicator-center'>
            <span></span>
        </div>
    </div>
`;

    $('#details-tabs').ocTab('addTab', title, content);

    loadRouteDetails(method, uri)
}

/**
 * @param {string} method
 * @param {string} uri
 */
function loadRouteDetails(method, uri) {
    $.request('onShowDetails', {
        data: {
            uri,
            method
        },
        update: {
            'partials/details': '#details-tabs > .tab-content > .tab-pane.active'
        }
    });
}
