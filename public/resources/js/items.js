/*const itemsAppList = [
    {category_id: 59, code: 1, name:'Mujer'},
    {category_id: 59, code: 2, name:'Hombre'},
];*/

const itemsMap = new Map();

// Construimos el índice
for (const item of itemsAppList) {
  if (!itemsMap.has(item.category_id)) {
    itemsMap.set(item.category_id, new Map());
  }
  itemsMap.get(item.category_id).set(item.code, item);
}

const ItemsApp = {
    //Valor de un campo específico
    fieldValue(categoryId, code, field = 'name') {
        code = code.toString();
        categoryId = categoryId.toString();
        return itemsMap.get(categoryId)?.get(code)?.[field] ?? `[${code}]`;
    },
    //Array para la categoría requerida
    arrayCategory(categoryId) {
        categoryId = categoryId.toString();
        return Array.from(itemsMap.get(categoryId)?.values() ?? []);
    },
};