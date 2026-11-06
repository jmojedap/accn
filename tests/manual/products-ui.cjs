// UI QA: Chrome headless, paginas sinteticas y API interceptada. No toca MySQL.
// Primero: php -d extension=sqlite3 tests/manual/products-views.php
const fs = require('node:fs');
const path = require('node:path');
const {spawn} = require('node:child_process');
const assert = require('node:assert/strict');

const directory = path.resolve(__dirname, '../../writable/cache/products-ui');
const fixture = JSON.parse(fs.readFileSync(path.join(directory, 'search.json'), 'utf8'));
const chrome = 'C:/Program Files/Google/Chrome/Application/chrome.exe';
const child = spawn(chrome, [
    '--headless=new', '--disable-gpu', '--no-first-run', '--no-default-browser-check',
    '--ignore-certificate-errors', '--remote-debugging-port=0',
    '--user-data-dir=' + path.join(directory, 'chrome-' + Date.now()), 'about:blank',
], {windowsHide: true, stdio: ['ignore', 'ignore', 'pipe']});

let socket;
let session;
let sequence = 0;
let checks = 0;
let failNext = false;
let records = structuredClone(fixture.results);
let lastPayload = {};
let lastRequest = '';
const pending = new Map();
const exceptions = [];
const delay = milliseconds => new Promise(resolve => setTimeout(resolve, milliseconds));

function send(method, params = {}, browser = false) {
    return new Promise((resolve, reject) => {
        const id = ++sequence;
        const timer = setTimeout(() => { pending.delete(id); reject(new Error('CDP timeout: ' + method)); }, 20000);
        pending.set(id, {resolve, reject, timer});
        socket.send(JSON.stringify({id, method, params, ...(!browser && session ? {sessionId: session} : {})}));
    });
}

async function evaluate(expression) {
    const response = await send('Runtime.evaluate', {expression, returnByValue: true, awaitPromise: true});
    if (response.exceptionDetails) throw new Error(JSON.stringify(response.exceptionDetails));
    return response.result.value;
}

async function until(expression) {
    const end = Date.now() + 20000;
    while (Date.now() < end) {
        if (await evaluate(expression)) return;
        await delay(100);
    }
    throw new Error('UI timeout: ' + expression);
}

async function check(expression, message) {
    assert.equal(await evaluate(expression), true, message);
    checks++;
}

async function screenshot(name) {
    const image = await send('Page.captureScreenshot', {format: 'png', captureBeyondViewport: true});
    fs.writeFileSync(path.join(directory, name + '.png'), Buffer.from(image.data, 'base64'));
}

function multipart(body = '') {
    const result = {};
    for (const match of body.matchAll(/name="([^"]+)"\r?\n\r?\n([\s\S]*?)(?=\r?\n--)/g)) result[match[1]] = match[2];
    return result;
}

async function respond(event) {
    const request = event.params.request;
    const url = new URL(request.url);
    let status = 200;
    let body = {};
    if (url.hostname !== 'products.test') {
        if (['cdn.jsdelivr.net', 'unpkg.com', 'cdnjs.cloudflare.com'].includes(url.hostname)) {
            return send('Fetch.continueRequest', {requestId: event.params.requestId});
        }
        exceptions.push('Solicitud inesperada bloqueada: ' + request.url);
        return send('Fetch.failRequest', {requestId: event.params.requestId, errorReason: 'Aborted'});
    }
    if (url.pathname.startsWith('/admin/ecommerce/products/')) {
        const name = url.pathname.includes('/info/') ? 'info' : url.pathname.includes('/edit/') ? 'edit' : url.pathname.endsWith('/add') ? 'add' : 'explore';
        return send('Fetch.fulfillRequest', {requestId: event.params.requestId, responseCode: 200,
            responseHeaders: [{name: 'Content-Type', value: 'text/html; charset=utf-8'}],
            body: fs.readFileSync(path.join(directory, name + '.html')).toString('base64')});
    }
    if (request.method === 'OPTIONS') {
        return send('Fetch.fulfillRequest', {requestId: event.params.requestId, responseCode: 204,
            responseHeaders: [{name:'Access-Control-Allow-Origin',value:'*'}, {name:'Access-Control-Allow-Methods',value:'GET,POST,DELETE,OPTIONS'}, {name:'Access-Control-Allow-Headers',value:'*'}]});
    }
    if (!url.pathname.startsWith('/api/products/')) {
        return send('Fetch.fulfillRequest', {requestId: event.params.requestId, responseCode: 404, body: ''});
    }
    lastRequest = url.pathname;
    if (failNext) {
        status = 500;
        failNext = false;
    } else if (url.pathname.endsWith('/search')) {
        const q = (url.searchParams.get('q') || '').toLowerCase();
        let selected = records.filter(row => !q || [row.name, row.idcode].some(value => String(value).toLowerCase().includes(q)));
        for (const [filter, field] of [['status__eq','status'], ['product_kind__eq','product_kind'], ['currency__eq','currency']]) {
            const value = url.searchParams.get(filter);
            if (value !== null && value !== '') selected = selected.filter(row => String(row[field]) === value);
        }
        const settings = {...fixture.settings, numPage: Number(url.searchParams.get('numPage') || 1), perPage: Number(url.searchParams.get('perPage') || 12)};
        settings.offset = (settings.numPage - 1) * settings.perPage;
        settings.maxPage = Math.max(1, Math.ceil(selected.length / settings.perPage));
        body = {...fixture, qtyResults: selected.length, settings, filters: {...fixture.filters, ...Object.fromEntries(url.searchParams)}, results: selected.slice(settings.offset, settings.offset + settings.perPage), getString: url.searchParams.toString()};
    } else if (url.pathname.endsWith('/delete_selected')) {
        const ids = JSON.parse(request.postData).ids;
        records = records.filter(row => !ids.includes(row.idcode));
        body = {summary: {total: ids.length, deleted: ids.length, not_deleted: 0}, deleteResults: ids.map(idcode => ({idcode, code: 'DELETED'}))};
    } else {
        lastPayload = multipart(request.postData);
        if (lastPayload.name === 'Duplicado') {
            status = 422;
            body = {savedId: 0, errors: {name: 'Revisa el nombre del producto.'}};
        } else {
            body = {saved: true, savedId: 99, idcode: lastPayload.idcode || 'P-UI-99', errors: {}};
        }
    }
    await send('Fetch.fulfillRequest', {
        requestId: event.params.requestId, responseCode: status,
        responseHeaders: [{name:'Content-Type',value:'application/json'}, {name:'Access-Control-Allow-Origin',value:'*'}],
        body: Buffer.from(JSON.stringify(body)).toString('base64'),
    });
}

async function open(name, root) {
    const location = ['edit', 'info'].includes(name) ? name + '/3' : name;
    await send('Page.navigate', {url: 'https://products.test/admin/ecommerce/products/' + location});
    await until('Boolean(document.getElementById(' + JSON.stringify(root) + ')?.__vue_app__)');
    await evaluate('window.testApp = document.getElementById(' + JSON.stringify(root) + ').__vue_app__._instance.proxy; true');
}

async function input(selector, value) {
    await evaluate('(() => { const input = document.querySelector(' + JSON.stringify(selector) + '); input.value = ' + JSON.stringify(value) + '; input.dispatchEvent(new Event("input", {bubbles:true})); return true; })()');
}

(async () => {
    try {
        const address = await new Promise((resolve, reject) => {
            let log = '';
            const timer = setTimeout(() => reject(new Error('Chrome no inicio: ' + log)), 15000);
            child.stderr.on('data', chunk => {
                log += chunk;
                const match = log.match(/DevTools listening on (ws:\/\/[^\s]+)/);
                if (match) { clearTimeout(timer); resolve(match[1]); }
            });
            child.once('error', reject);
        });
        socket = new WebSocket(address);
        await new Promise(resolve => socket.addEventListener('open', resolve, {once: true}));
        socket.addEventListener('message', message => {
            const event = JSON.parse(message.data);
            if (event.id) {
                const callback = pending.get(event.id);
                if (!callback) return;
                clearTimeout(callback.timer);
                pending.delete(event.id);
                if (event.error) callback.reject(new Error(JSON.stringify(event.error)));
                else callback.resolve(event.result);
            } else if (event.method === 'Fetch.requestPaused') {
                respond(event).catch(error => exceptions.push(error.message));
            } else if (event.method === 'Runtime.exceptionThrown') {
                exceptions.push(JSON.stringify(event.params.exceptionDetails));
            }
        });
        const target = await send('Target.createTarget', {url: 'about:blank'}, true);
        session = (await send('Target.attachToTarget', {targetId: target.targetId, flatten: true}, true)).sessionId;
        await send('Page.enable');
        await send('Runtime.enable');
        await send('Fetch.enable', {patterns: [{urlPattern: '*', requestStage: 'Request'}]});
        await send('Emulation.setDeviceMetricsOverride', {width: 1440, height: 1100, deviceScaleFactor: 1, mobile: false});
        await open('explore', 'productsExplore');
        await check('document.querySelectorAll("tbody tr").length === 3', 'Listado inicial');
        await check('document.querySelector("tbody tr a").href.includes("/info/")', 'El nombre del producto abre el detalle');
        await screenshot('explore-desktop');
        await input('#productsQuery', 'Acceso');
        await evaluate('window.testApp.search(true)');
        await check('window.testApp.qtyResults === 1 && window.testApp.selected.length === 0', 'Busqueda interactiva');
        await evaluate('window.testApp.selectAll(true); window.testApp.confirmDelete(); true');
        await until('document.getElementById("productsDeleteModal").classList.contains("show")');
        await evaluate('window.testApp.deleteSelected()');
        await check('window.testApp.results.length === 0 && !window.testApp.deleting && window.testApp.notice.includes("1")', 'Eliminacion y refresco');
        await evaluate('window.testApp.resetFilters(); true');
        await until('!window.testApp.loading');
        await check('window.testApp.qtyResults === 2', 'Limpiar filtros');
        failNext = true;
        await evaluate('window.testApp.search()');
        await check('Boolean(window.testApp.error) && !window.testApp.loading', 'Error de red recuperable');

        await open('add', 'productFormApp');
        await screenshot('add-desktop');
        await input('#productName', 'Nuevo acceso');
        await input('#productPrice', '19900.50');
        await evaluate('window.testApp.handleSubmit()');
        assert.equal(lastPayload.name, 'Nuevo acceso');
        assert.equal(lastPayload.base_price, '19900.50');
        checks += 2;
        await until('document.getElementById("productCreatedModal").classList.contains("show")');
        await check('window.testApp.createdInfoUrl.endsWith("/info/99") && !window.testApp.loading', 'Creacion y enlace al detalle');
        await evaluate('window.testApp.createAnother(); true');
        await check('window.testApp.fields.name === "" && window.testApp.rowId === 0', 'Crear otro limpia formulario');
        await input('#productName', 'Duplicado');
        await evaluate('window.testApp.handleSubmit()');
        await check('Boolean(window.testApp.errors.name) && !window.testApp.loading && document.querySelector("#productName").classList.contains("is-invalid")', 'Errores de validacion');

        await open('info', 'productInfoApp');
        await check('document.querySelector("h1").textContent === "Acceso inicial" && window.testApp.statusName === "Activo"', 'Datos del detalle y estado');
        await check('document.querySelector("nav[aria-label=Producto] a[aria-current=page]").textContent.trim() === "Información"', 'Menu particular activo en detalle');
        await check('document.querySelector("nav[aria-label=Producto] a:last-child").href.endsWith("/edit/3")', 'Enlace de edicion del mismo producto');
        await screenshot('info-desktop');

        const menuLink = await evaluate('(() => { const rect = document.querySelector("nav[aria-label=Producto] a:last-child").getBoundingClientRect(); return {x: rect.x + rect.width/2, y: rect.y + rect.height/2}; })()');
        await send('Input.dispatchMouseEvent', {type: 'mousePressed', ...menuLink, button: 'left', clickCount: 1});
        await send('Input.dispatchMouseEvent', {type: 'mouseReleased', ...menuLink, button: 'left', clickCount: 1});
        await until('Boolean(document.getElementById("productFormApp")?.__vue_app__)');
        await evaluate('window.testApp = document.getElementById("productFormApp").__vue_app__._instance.proxy; true');
        await check('location.pathname.endsWith("/edit/3")', 'Navegar desde el menu del detalle a editar');
        await check('document.querySelector("nav[aria-label=Producto] a[aria-current=page]").textContent.trim() === "Editar"', 'Menu particular activo en edicion');
        await check('document.querySelector("#productName").value === "Acceso inicial"', 'Edicion precargada');
        await screenshot('edit-desktop');
        await input('#productCode', 'CODIGO-NUEVO');
        await evaluate('window.testApp.handleSubmit()');
        assert.equal(lastRequest, '/api/products/update/VIRTUAL-003');
        checks++;
        await check('window.testApp.currentIdCode === "CODIGO-NUEVO" && Boolean(window.testApp.savedNotice)', 'Actualizacion conserva referencia de API');
        await evaluate('window.testApp.handleSubmit()');
        assert.equal(lastRequest, '/api/products/update/CODIGO-NUEVO');
        checks++;
        await send('Emulation.setDeviceMetricsOverride', {width: 390, height: 844, deviceScaleFactor: 1, mobile: true});
        await screenshot('edit-mobile');
        await check('document.documentElement.scrollWidth <= innerWidth', 'Formulario sin desbordamiento movil');
        await open('info', 'productInfoApp');
        await screenshot('info-mobile');
        await check('document.documentElement.scrollWidth <= innerWidth', 'Detalle sin desbordamiento movil');
        await evaluate('window.testApp.product.description = "<img src=x onerror=alert(1)>"; true');
        await check('document.querySelector("#productInfoApp p.text-break").textContent.includes("<img") && document.querySelector("#productInfoApp p.text-break img") === null', 'La descripcion se muestra como texto seguro');
        await open('explore', 'productsExplore');
        await screenshot('explore-mobile');
        await check('document.documentElement.scrollWidth <= innerWidth', 'Listado movil con tabla desplazable');
        assert.deepEqual(exceptions, [], 'No debe haber excepciones de JavaScript');
        console.log('OK: ' + checks + ' comprobaciones de UI Chrome, sin excepciones JS. Capturas en ' + directory);
    } finally {
        if (socket && socket.readyState === WebSocket.OPEN) {
            await send('Browser.close', {}, true).catch(() => {});
            socket.close();
        }
        child.kill();
    }
})().catch(error => { console.error(error); process.exitCode = 1; });
