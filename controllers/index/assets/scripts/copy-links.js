/**
 * @param {string} toCopy
 * @param {string} msgText
 */
function copyTextToClipboard(toCopy, msgText) {
    navigator
        .clipboard
        .writeText(toCopy)
        .then(function () {
            console.log(msgText)
        })
        .catch(err => console.error(err))
}

/**
 * @param {string} method
 * @param {string} uri
 */
function copyLinkToRoute(method, uri) {
    let link = window.location.origin + window.location.pathname;
    link += '?method=' + method + '&uri=' + uri;

    link = addObjectParams(link, getRouteParams(), 'route');
    link = addObjectParams(link, getRequestParams(), 'request');
    link = addObjectParams(link, getRequestHeaders(), 'headers');

    copyTextToClipboard(link, 'Link copied to clipboard!');
}
