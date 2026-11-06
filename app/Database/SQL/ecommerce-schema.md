# Contrato del esquema ecommerce MVP (v2)

Version actual: `2026-11-06-v2-mvp`. SQL incremental:
[2026-11-06_ecommerce_v2_mvp.sql](2026-11-06_ecommerce_v2_mvp.sql).
Validado en MariaDB 10.4.32 y PHP 8.0 de XAMPP.

El alcance inicial son productos virtuales, servicios y accesos, una tienda y una
cuenta comercial por proveedor y ambiente. Las devoluciones excepcionales se
gestionan manualmente en la pasarela. Las ocho tablas de negocio son:

| Tabla | Responsabilidad |
|---|---|
| `products` | Producto comercial y precio base. |
| `products_meta` | Atributos extensibles con clave, posicion y JSON. |
| `product_variants` | Presentaciones o planes con SKU propio. |
| `orders` | Comprador, totales y estado del pedido. |
| `orders_product` | Copia historica de cada linea comprada. |
| `payment_sessions` | Checkout y referencia comercial. |
| `payments` | Intentos reales de pago y registro minimo de devolucion manual. |
| `payment_events` | Notificaciones externas y su procesamiento. |

`ecommerce_schema_versions` conserva el historial de versiones. Las nueve tablas
usan InnoDB, utf8mb4 y claves primarias INT/BIGINT UNSIGNED autoincrementales.

## Cambios respecto de v1

Se retiran `inventory_reservations`, `inventory_movements`, `payment_accounts` y
`payment_refunds`. Se eliminan de las variantes `track_inventory`, `stock_on_hand`
y `stock_reserved`. No hay reservas, contadores ni movimientos de inventario.

`payment_account_id` se sustituye por `provider` y `environment` en sesiones,
pagos y eventos. Las FK compuestas conservan la consistencia de proveedor,
ambiente y moneda. Las referencias, IDs externos, claves de idempotencia y claves
de deduplicacion tienen unicidad por proveedor y ambiente. Un ID usado en sandbox
puede existir tambien en produccion o en otro proveedor sin colisiones.

El tipo de producto predeterminado pasa a `digital`. Se admiten `digital`,
`service` y `access`. El valor `physical` y los campos opcionales de dimensiones
de v1 se conservan por compatibilidad; no activan gestion fisica ni inventario.
Los campos de envio de pedidos siguen siendo opcionales; usar `not_required`
para las compras virtuales. No se incluyen suscripciones recurrentes, cupos
limitados ni asignacion automatica de licencias o permisos en esta etapa.

## Migracion, respaldo y pruebas

Desde la raiz del proyecto:

```powershell
D:\xampp\php\php.exe tests/manual/ecommerce-schema.php --test
D:\xampp\php\php.exe tests/manual/ecommerce-schema.php --apply
```

`--test` respalda las tablas ecommerce de la base configurada en `.env`, restaura
en una base temporal y prueba alli. Si el origen es v1, tambien verifica la
migracion con catalogo, pedido pagado, sesion y eventos de prueba. Comprueba
conservacion de conteos, IDs relacionados, importe cobrado y fecha historica.
Prueba protecciones ante inventario, devoluciones, varias cuentas y dependencias
adicionales. Si el origen ya es v2, verifica directamente la copia v2.
La base temporal se elimina al terminar; el origen no se modifica en `--test`.

`--apply` exige v1, ejecuta esas pruebas y obtiene un respaldo actualizado justo
antes de aplicar v2. No ejecuta otras migraciones de CodeIgniter ni modifica las
tablas ajenas al ecommerce. Los respaldos completos del modulo, incluidos sus
datos, quedan en `writable/database/backups/ecommerce_before_v2_*.sql` y no se
versionan ni contienen la clave de conexion.

El SQL incremental puede importarse seleccionando la base v1 en phpMyAdmin, con
respaldo previo. La copia de `writable/database/db_ecommerce_tables.sql` corresponde
ahora a esta migracion v2; la fuente versionada esta en `app/Database/SQL/`.

Antes de migrar se deben detener escrituras del modulo: el bloqueo del instalador
solo coordina instaladores. La migracion conserva catalogo, pedidos y pagos, y
traslada el proveedor/ambiente desde las cuentas originales. Aborta antes de
alterar tablas si encuentra inventario, cualquier historial de devolucion,
varias cuentas por proveedor/ambiente, dependencias adicionales o una instalacion
v2 previa. No se desactivan las FK. La configuracion no secreta de cuentas queda
en el respaldo; si se hubiera configurado alguna, trasladarla a la configuracion
del backend antes de integrar su adaptador.

DDL realiza commits implicitos y no admite rollback global. Ante un fallo
parcial, conservar respaldo y revisar el estado antes de repetir. Un intento
rechazado puede dejar el procedimiento `accn_simplify_ecommerce_20261106`;
eliminar solamente ese procedimiento despues de revisar el error. No borrar
tablas o marcas de version para eludir las protecciones. El ejecutor rechaza
`--apply` cuando v2 ya esta instalado; usar `--test` para volver a verificar.

Para una instalacion basada en las tablas heredadas, primero se aplica el
instalador historico v1 y despues v2. Se conservan `2026-11-06_ecommerce_v1.sql`,
`verify_ecommerce_v1.sql` y `tests/manual/ecommerce-schema-v1.php` como historial.
No se deben ejecutar estos archivos historicos sobre la base MVP en uso.

## Catalogo e importes

`products.idcode` identifica el producto; `product_variants.sku` identifica su
presentacion o plan vendible. Incluso un producto simple tiene una variante.
Los precios y costos NULL de la variante heredan los del producto. Las variantes
tambien permiten planes de acceso sin crear otra arquitectura de catalogo.
`products.status`: 0 borrador, 1 activo, 2 archivado. Archivar o usar `deleted_at`
para conservar las relaciones con ventas historicas.

`products_meta` guarda valores JSON validos con una clave explicita. Posicion 0
para atributos unicos; varias posiciones para listas. El backend valida la
semantica y tipo de cada clave. Importes, estados e identidades de pago tienen
columnas propias.

Todos los importes usan DECIMAL(18,2); cantidades, DECIMAL(12,3). Precios base y
envio excluyen impuestos. Se mantiene `currency` ISO en mayusculas. El backend
debe calcular con aritmetica decimal, validar la escala del proveedor y conciliar
los agregados; no usar float.

```text
orders.total_amount = subtotal - discount_total + tax_total + shipping_total
orders_product.subtotal = ROUND(quantity * unit_price, 2)
orders_product.line_total = subtotal - discount_total + tax_total
```

`tax_total` del pedido incluye impuestos de productos y, si se usara, envio.
Las lineas conservan nombre, SKU, atributos, costo, precio y tributacion aplicados.
Las FK RESTRICT conservan ventas y transacciones. `user_id` es opcional para
invitados; los identificadores de usuario no tienen FK porque `users` sigue
siendo una tabla heredada MyISAM. Las direcciones opcionales son copias JSON.

## Pasarelas, reintentos y eventos

`provider` es extensible: `mercadopago`, `wompi`, `payu` u otro adaptador.
`environment` es `sandbox` o `production`; ambos son parte de la identidad
historica y no se infieren despues a partir de la configuracion actual.
Las credenciales y seleccion de la cuenta comercial se resuelven en configuracion
segura del backend. No guardar claves o tokens en metadata o eventos.
Se admite una sola cuenta por proveedor/ambiente; si se incorpora una segunda o
se reemplaza la cuenta comercial con ventas existentes, ampliar primero el
modelo para conservar esa identidad.

`payment_sessions` guarda referencia comercial, identificadores externos de
checkout/orden, clave de idempotencia, importe y moneda. Un pedido puede tener
varias sesiones y proveedores. `payments` guarda cada intento y su estado
original. Varios intentos pueden compartir una referencia comercial; el ID de
transaccion es el que distingue los intentos. `provider_payment_id` puede ser
NULL mientras se crea la operacion; la clave de solicitud puede ser NULL cuando
el intento se origina dentro del checkout externo.

Generar y persistir las claves de idempotencia antes de llamar al proveedor.
Reutilizar la clave solo para repetir la misma operacion. Un timeout tiene
resultado desconocido: consultar y reconciliar antes de crear otro cobro.
`paid_total` registra capturas brutas y admite sobrepago. No degradar un pedido
pagado por el rechazo tardio de otro intento; registrar y resolver segundos
cobros aprobados. Los estados agregados se mantienen en una transaccion del
backend, no mediante triggers.

`payment_events` admite eventos aun sin pago identificado. Correlacionar por
proveedor, ambiente y referencia o ID externo. La clave de deduplicacion usa un
ID de evento confiable o un hash estable del adaptador; no solamente el ID de
transaccion. Depurar payload/cabeceras antes de persistir y validar firmas con
los valores originales segun cada proveedor. PayU puede enviar formularios en
lugar de JSON. Verificar tambien importe, moneda y referencia; confirmar desde
el backend y no desde el retorno del navegador. El CHECK exige firma validada y
fecha para marcar un evento como procesado, pero la criptografia la realiza el
adaptador. Los efectos de entrega/acceso deben ser idempotentes.

## Devolucion manual excepcional

No hay solicitudes automaticas ni tabla de reembolsos. Se contempla un registro
confirmado de devolucion por pago, total o parcial, con estos campos en `payments`:

- `refunded_amount`: importe efectivamente devuelto, no superior al capturado.
- `refund_reference`: referencia de la operacion externa/manual confirmada.
- `refunded_at`: fecha real de devolucion en UTC.
- `refund_reason`: motivo o nota del operador.

El CHECK exige referencia no vacia y fecha si el importe devuelto es positivo.
No registrar una solicitud pendiente como dinero ya devuelto. El operador
confirma la operacion en la pasarela y el backend registra pago, estado del
pedido y `orders.refunded_total` en una misma transaccion, bloqueando las filas.
Para el registro inicial puede condicionarse la escritura a `refunded_amount=0`
y actualizar el pedido solamente si se modifico el pago, evitando duplicados.
Los totales y estados no se actualizan solos por cambiar una columna.

El registro minimo no es un historial de varias devoluciones parciales. Si ese
caso aparece, incorporar una tabla de operaciones antes de sobreescribir la
referencia previa. Las reglas y disponibilidad de devolucion siguen dependiendo
del proveedor y medio de pago. No se ha implementado una interfaz de devolucion.

## Verificacion y alcance

[verify_ecommerce_mvp.sql](verify_ecommerce_mvp.sql) revisa en solo lectura las
nueve tablas, motores, columnas, relaciones, conteos y algunos agregados. El
ejecutor prueba integridad, proveedor/ambiente/moneda, idempotencia, reintentos,
eventos repetidos, firma requerida, devolucion manual y rollback.

Las conexiones del futuro backend deben usar UTC (`SET time_zone = '+00:00'`) y
modo SQL estricto para evitar truncamientos. Convertir a America/Bogota al
mostrar fechas. El instalador configura solo su sesion, no el servidor global.

Esta entrega modifica el esquema y sus verificaciones. Checkout, adaptadores de
pasarela, endpoints/webhooks en `Controllers/Api` y asignacion de accesos siguen
pendientes del desarrollo del modulo comercial. El esquema no realiza cobros.
