let editors = {};

document.addEventListener('DOMContentLoaded', function () {
    $('#details-tabs').on('beforeClose.oc.tab', function (event) {
        let id = $(event.relatedTarget).find('.response-viewer').attr('id');

        if (editors[id]) {
            delete (editors[id]);
        }
    });
});

function getRouteParams() {
    let routeParams = {};

    getActiveDetailsTab()
        .querySelectorAll('.route-params-table tr')
        .forEach(function (tr) {

            let nameInput = tr.querySelector('input.param-name');

            if (!nameInput) {
                return;
            }

            if (nameInput.value.length > 0) {
                routeParams[nameInput.value] = tr.querySelector('input.param-value').value;
            }
        });

    return routeParams;
}

function getRequestParams() {
    let requestParams = {};

    getActiveDetailsTab()
        .querySelectorAll('.params-table tr')
        .forEach(function (tr) {
            let nameInput = tr.querySelector('input.param-name');

            if (!nameInput) {
                return;
            }

            if (nameInput.value.length > 0) {
                requestParams[nameInput.value] = tr.querySelector('input.param-value').value;
            }
        });

    return requestParams;
}

function getRequestHeaders() {
    let headers = {};

    getActiveDetailsTab()
        .querySelectorAll('.headers-table tr')
        .forEach(function (tr) {
            let nameInput = tr.querySelector('input.param-name');

            if (!nameInput) {
                return;
            }

            if (nameInput.value.length > 0) {
                headers[nameInput.value] = tr.querySelector('input.param-value').value;
            }
        });

    return headers;
}

function sendRequest(method, uri, responseViewerId) {
    showResponseLoader();

    let routeParams = getRouteParams();
    let requestParams = getRequestParams();

    uri = prepareUri(uri, routeParams);

    let axiosParams = {
        method,
    };

    let headers = getRequestHeaders();

    if (Object.keys(headers).length > 0) {
        axiosParams.headers = headers;
    }

    if (method === 'get' && Object.keys(requestParams).length > 0) {
        uri += '?' + buildQueryString(requestParams);
    } else {
        axiosParams.data = requestParams;
    }

    axiosParams.url = uri;

    axios(axiosParams)
        .then(res => renderResponse(res, responseViewerId))
        .catch(err => renderResponse(err.response, responseViewerId));
}

/**
 * @param {string} uri
 * @param {Object} routeParams
 */
function prepareUri(uri, routeParams) {
    for (let paramName in routeParams) {
        if (routeParams.hasOwnProperty(paramName)) {
            //uri = uri.split(paramName).join(routeParams[paramName]);
            uri = uri.replace(`{${paramName}}`, routeParams[paramName]);
        }
    }

    return uri;
        //.split('{').join('')
        //.split('?}').join('')
        //.split('}').join('');
}


function buildQueryString(obj) {
    let str = [];

    for (let p in obj) {
        if (obj.hasOwnProperty(p)) {
            str.push(encodeURIComponent(p) + '=' + encodeURIComponent(obj[p]));
        }
    }

    return str.join('&');
}

function setStatus(res) {
    let status = res.status;

    if (httpCodes[status]) {
        status += ': ' + httpCodes[status];
    }

    getActiveDetailsTab()
        .querySelector('.response-code')
        .innerHTML = status;
}

function getEditorMode(res) {
    let contentType = res.headers['content-type'].split(';')[0];

    switch (contentType) {
        case 'application/json':
        case 'text/json':
        case 'json':
            return 'javascript';
        case 'text/html':
        case 'html':
            return 'html';
        case 'application/xml':
        case 'text/xml':
        case 'xml':
            return 'xml';
    }

    return null;
}

function findResponseLoader() {
    return getActiveDetailsTab().querySelector('.response-viewer .loading-indicator-container');
}

function showResponseLoader() {
    let loader = findResponseLoader();

    if (loader) {
        loader.style.display = 'block';
    }
}

function removeResponseLoader() {
    let loader = findResponseLoader();

    if (loader) {
        loader.remove();
    }
}

function renderResponse(res, responseViewerId) {
    try {
        removeResponseLoader();

        setStatus(res);

        let mode = getEditorMode(res);

        let editor = ace.edit(responseViewerId);
        if (mode) {
            editor.session.setMode('ace/mode/' + mode);
        }
        editor.setReadOnly(true);
        editor.setValue(JSON.stringify(res.data, null, 4), -1);

        editors[responseViewerId] = editor;
    } catch (err) {
        console.error(err);
    }
}

function resizeEditor(responseViewerId) {
    if (editors[responseViewerId]) {
        editors[responseViewerId].resize();
    }
}

/**
 * @param {string} link
 * @param {Object} obj
 * @param {string} wrap
 * @return {string}
 */
function addObjectParams(link, obj, wrap) {
    if (!objectIsEmpty(obj)) {
        let arrayed = {};

        for (let key in obj) {
            if (!obj.hasOwnProperty(key)) {
                continue;
            }

            let value = obj[key];

            if (value.length === 0) {
                continue;
            }

            arrayed[wrap + '[' + key + ']'] = value;
        }

        if (!objectIsEmpty(arrayed)) {
            link += '&' + buildQueryString(arrayed);
        }
    }

    return link;
}

/**
 * @param {Object} obj
 */
function objectIsEmpty(obj) {
    return Object.keys(obj).length === 0
}
