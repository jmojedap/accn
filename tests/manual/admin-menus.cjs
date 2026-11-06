// node tests/manual/admin-menus.cjs
// Evalua las definiciones y el arranque real del sidebar sin red ni base de datos.
const fs = require('node:fs');
const path = require('node:path');
const vm = require('node:vm');
const assert = require('node:assert/strict');
const root = path.resolve(__dirname, '../..');
const sidebar = fs.readFileSync(path.join(root, 'app/Views/templates/admin_pml_5/main/sidebar.php'), 'utf8');
const script = sidebar.match(/<script>([\s\S]*?)<\/script>/)[1];
let checks = 0;

function load(role, section) {
    const context = {APP_RID: role, appSection: section, URL_MOD: 'http://localhost/accn/admin/', window: {location: ''}};
    context.createApp = options => ({mount() {
        const app = options.data();
        for (const [name, method] of Object.entries(options.methods)) app[name] = method.bind(app);
        return app;
    }});
    vm.createContext(context);
    vm.runInContext(fs.readFileSync(path.join(root, 'public/resources/config/admin_pml_5/menus/nav_1_elements_' + role + '.js'), 'utf8'), context);
    assert.ok(Array.isArray(context.nav1Elements), 'Formato nav1Elements uniforme: rol ' + role);
    checks++;
    vm.runInContext(script, context);
    return context;
}

for (const role of [1, 2, 3]) {
    for (const section of ['ecommerce/products', 'ecommerce/products/explore', 'ecommerce/products/add', 'ecommerce/products/info/17', 'ecommerce/products/edit/17']) {
        const context = load(role, section);
        const menus = context.nav1Elements;
        const index = menus.findIndex(menu => menu.text === 'ECommerce');
        assert.ok(index >= 0, 'ECommerce presente: rol ' + role);
        const ecommerce = menus[index];
        assert.equal(ecommerce.active, true, 'Categoria activa: ' + section);
        const product = ecommerce.subelements.find(menu => menu.text === 'Productos');
        assert.equal(product.active, true, 'Productos activo: ' + section);
        assert.equal(product.anchor, true, 'Productos utiliza una ruta completa');
        context.nav1App.nav_1_click_sub(index, ecommerce.subelements.indexOf(product));
        assert.equal(context.window.location, 'http://localhost/accn/admin/ecommerce/products/explore', 'Destino del submenu');
        checks += 5;
    }

    const posts = load(role, 'posts/edit/9');
    assert.equal(posts.nav1Elements.find(menu => menu.text === 'Posts').active, true, 'Activacion anterior con ID: rol ' + role);
    assert.equal(posts.nav1Elements.find(menu => menu.text === 'ECommerce').active, false);
    const other = load(role, 'ecommerce/products-copy');
    assert.equal(other.nav1Elements.find(menu => menu.text === 'ECommerce').subelements[0].active, false, 'Respetar limite del segmento');
    checks += 3;
}

const developer = load(1, 'posts/explore');
const admin = load(2, 'posts/explore');
assert.deepEqual(Array.from(admin.nav1Elements, menu => menu.text), Array.from(developer.nav1Elements, menu => menu.text), 'Conservar las opciones previas del administrador');
const editor = load(3, 'posts/explore');
assert.deepEqual(Array.from(editor.nav1Elements, menu => menu.text), ['Posts', 'Archivos', 'ECommerce', 'Ajustes'], 'Conservar navegacion limitada del editor');
assert.deepEqual(Array.from(editor.nav1Elements.find(menu => menu.text === 'Ajustes').subelements, menu => menu.text), ['Ítems']);
const account = load(21, '');
assert.equal(account.nav1Elements.some(menu => menu.text === 'ECommerce'), false, 'El rol 21 no recibe ecommerce');
account.nav1App.nav_1_click(0);
assert.equal(new URL(account.window.location).pathname, '/accn/m/accounts/profile');
assert.ok(sidebar.includes(':href="subelement.anchor ? urlMod + subelement.cf') && sidebar.includes('v-on:click.prevent'), 'Enlaces reales y sin salto al #');
checks += 6;
console.log('OK: ' + checks + ' comprobaciones de menus, roles, activacion anidada y enlace de Productos.');
