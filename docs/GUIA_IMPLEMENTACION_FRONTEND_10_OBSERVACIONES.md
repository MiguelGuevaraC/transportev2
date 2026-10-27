# Guía de implementación frontend

## Transporte Hernández — 10 observaciones

Todas las rutas requieren autenticación Sanctum. Usar `Authorization: Bearer <token>` y `Accept: application/json`. Reemplazar `{API}` por la URL base del ambiente. Las fechas se envían como `YYYY-MM-DD`.

---

## 1. Recepción de carga: rango de fechas y Excel

El rango corresponde a `receptions.receptionDate` y es inclusivo.

El frontend debe utilizar el endpoint optimizado de carga de datos:

```http
GET {API}/api/dataCollection
```

Para el Excel se mantiene:

```http
GET {API}/api/reporteRecepcionesExcel
```

Enviar a `dataCollection` los filtros de recepciones y sus paginaciones independientes:

```text
branch_office_id
receptions_page
receptions_per_page
places_page
places_per_page
workers_page
workers_per_page
vehicles_page
vehicles_per_page
codeReception
dateStart
dateEnd
origenOrDestino
nombreClientePaga
nombreRemitente
nombreDestinatario
statusReception
isCargos
numberVenta
numberGuia
documentGuia
glosa
sinProgramaciones
```

Ejemplo recomendado:

```text
/api/dataCollection?branch_office_id=1&receptions_page=1&receptions_per_page=10&places_per_page=100&workers_per_page=100&vehicles_per_page=100&dateStart=2026-01-01&dateEnd=2026-10-21&glosa=&codeReception=&numberGuia=&documentGuia=&numberVenta=&origenOrDestino=&nombreClientePaga=&nombreRemitente=&nombreDestinatario=&statusReception=&isCargos=&sinProgramaciones=false
```

Para `reporteRecepcionesExcel`, enviar los mismos filtros de recepción (`branch_office_id`, `dateStart`, `dateEnd`, `codeReception`, `numberGuia`, `numberVenta`, `glosa`, `origenOrDestino`, `statusReception`, nombres e `isCargos`). La respuesta es un `.xlsx`.

El Excel incluye `SITUACIÓN FACTURA`:

- `Pagado`: factura pagada o saldo cero.
- `Vencido`: saldo pendiente y cuota vencida.
- `Pendiente`: saldo pendiente sin cuota vencida.
- `Sin Factura`: no existe factura relacionada.

No limitar el calendario a 15 días. Deshabilitar el botón durante la descarga. `dataCollection` conserva paginación independiente para evitar cargar todas las recepciones en una sola llamada; el frontend debe usar `receptions.current_page`, `receptions.last_page`, `receptions.total` y `receptions.data`.

### Excel consolidado de las tres fuentes

Para descargar toda la información relacionada en un único archivo, usar este endpoint nuevo:

```http
GET {API}/api/reporteGuiasConsolidadoExcel
```

La respuesta es un solo `.xlsx` con tres hojas:

1. `Recepción de guías`.
2. `Guías de transporte`.
3. `Cuentas por cobrar`.

El endpoint acepta los filtros de los tres módulos. Para una descarga por rango de fechas se recomienda enviar:

```text
branch_office_id=1
dateStart=2026-01-01
dateEnd=2026-10-21
startDate=2026-01-01
endDate=2026-10-21
dateType=guide
```

También acepta `numberGuia`, `documentGuia`, `numberVenta`, `codeReception`, `glosa`, `origenOrDestino`, `nombreClientePaga`, `nombreRemitente`, `nombreDestinatario`, `statusReception`, `statusGuia`, `statusFacturado`, `typeGuia`, `vehicles`, `drivers`, `status`, `person_id` y `sequentialNumber`.

`branch_office_id` es opcional. Para consultar todas las sucursales, el frontend debe mostrar la opción `TODAS` y omitir el parámetro o enviarlo vacío. No debe bloquear la descarga obligando a seleccionar una sucursal.

Ejemplo:

```text
/api/reporteGuiasConsolidadoExcel?branch_office_id=1&dateStart=2026-01-01&dateEnd=2026-10-21&startDate=2026-01-01&endDate=2026-10-21&dateType=guide&numberGuia=&documentGuia=&numberVenta=&glosa=
```

El frontend debe reemplazar las tres descargas anteriores por una sola llamada a este endpoint y tratar la respuesta como `blob`.

La hoja `Recepción de guías` también incluye la columna `Pagos`, con el número de operación, tipo, banco, monto y fecha de cada pago; si no existen pagos muestra `No tiene Pagos Realizados`.

## 2. Guías: fecha de emisión seleccionable

Mostrar un selector:

```text
Fecha de emisión de guía
Fecha de emisión de factura
```

```http
GET {API}/api/carrierGuide
GET {API}/api/carrierGuide_export_excel
```

Parámetros: `dateType` (`guide` o `invoice`; acepta `guia`/`factura`), `startDate`, `endDate`, `numero`, `document`, `typeGuia`, `statusGuia`, `statusFacturado`, `observation`, `vehicles`, `drivers`, `route`, `branch_office_id`, `page`, `per_page`.

`dateType=guide` filtra por emisión/creación de guía; `dateType=invoice` por la fecha del movimiento de venta relacionado. Enviar los mismos filtros al Excel. La exportación incluye `ESTADO FACTURACION`; en guías manuales el backend muestra `Enviado`.

## 3. Eliminación de cargos

```http
DELETE {API}/api/cargo/{id}
PUT    {API}/api/cargo-delete-password
```

El frontend debe ocultar el botón para usuarios no autorizados, pero el backend vuelve a validar el permiso. Pueden eliminar cargos:

- El usuario autorizado configurado en `CARGO_DELETE_AUTHORIZED_USER`.
- Usuarios con rol Administrador Backend (`typeofUser_id=1`).
- Usuarios con rol Administrador Transporte (`typeofUser_id=2`).

Todos deben ingresar la clave de eliminación. Antes de eliminar, mostrar confirmación y solo actualizar la lista si la respuesta es exitosa. Se elimina el registro y el archivo físico.

Para registrar cargos, enviar `multipart/form-data` con archivos anidados como `cargos[0][file]`, `cargos[1][file]`, etc. Se aceptan `pdf`, `jpg`, `jpeg`, `png`, `doc` y `docx`, con un máximo de 20 MB por archivo.

Para cambiar la clave:

```json
{
  "current_password": "4828",
  "password": "1234",
  "password_confirmation": "1234"
}
```

Las claves deben tener cuatro dígitos. La clave inicial es `4828`, salvo que ya haya sido modificada. Manejar `403` (sin permiso), `404` (no existe) y `422` (validación).

## 4. Una referencia remitente por guía transportista

Una misma referencia de guía remitente solo puede asociarse a una guía transportista activa. La validación aplica al crear y editar, ignora mayúsculas/espacios y no considera guías anuladas.

Ante duplicado, el backend devuelve `422`:

```json
{
  "message": "La guía remitente ya está asociada a otra guía transportista.",
  "reference": "GR-001234",
  "carrier_guide": "GRT-000123"
}
```

Detener el guardado y mostrar el mensaje; no ocultar ni reemplazar el error.

## 5. Datos adicionales del Excel de control

El archivo `Copia de Control de Guías OLH GENERAL F.xlsx` es referencia funcional. Para analizarlo se leen solo las primeras 20 filas de cada hoja; no se importa toda la información.

Todo campo adicional debe ser opcional, reversible, no destructivo y compatible con registros actuales. El frontend debe soportar `null`, cadena vacía o ausencia del campo; mostrar `-` cuando corresponda y no sobrescribir datos con valores vacíos.

Antes de agregar un campo definir nombre, tabla, tipo, origen, visibilidad, filtros y exportación. No convertirlo en obligatorio sin aprobación funcional.

## 6. Preservar el estado `Entregado`

Editar manifiesto, vehículo, conductor o viaje no debe cambiar automáticamente una guía `Entregado` a `En Transito`.

```http
PUT {API}/api/carrierGuide/{id}/status
```

```json
{ "status": "Entregado" }
```

Estados: `Pendiente`, `En Transito`, `Entregado`. El frontend debe enviar el estado solo cuando el usuario lo cambie expresamente. No llamar este endpoint al guardar otros datos del viaje. Usar el estado devuelto por el backend.

## 7. Motivo obligatorio de anulación

```http
PUT {API}/api/changeStatusFacturado/{id}
```

Para anular:

```json
{
  "statusFacturado": "Anulada",
  "motivoAnulacion": "Error en los datos del destinatario"
}
```

También se acepta `motivo_anulacion`. Estados: `Anulada`, `Pendiente`, `Enviado`. El modal debe exigir el motivo, permitir máximo 500 caracteres y confirmar antes de enviar. Si está vacío, no llamar al backend.

## 8. Filtro de glosa/observación

```http
GET {API}/api/reception?glosa=texto
GET {API}/api/reporteRecepcionesExcel?glosa=texto
```

También se aceptan `observacion` y `observation`. La búsqueda es parcial, ignora mayúsculas/minúsculas y revisa carga/detalle, observación de guía y comentario de recepción. Aplicar el mismo filtro al listado y al Excel. Se recomienda debounce de 300–500 ms o ejecutar al presionar filtros.

## 9. Guías matriz y guías derivadas

Una matriz puede tener muchas hijas; cada hija solo puede depender de una matriz. Mostrar el check opcional `Esta guía depende de otra guía` y, si se marca, enviar:

```json
{ "parent_carrier_guide_id": 123 }
```

Consultar:

```http
GET {API}/api/carrierGuide/{id}/relations
```

Vincular una o varias:

```http
POST {API}/api/carrierGuide/{id}/relations
```

```json
{
  "related_guide_ids": [456, 457],
  "relation_type": "reparto"
}
```

También se acepta `related_guide_id`. En el endpoint `{id}` es la matriz.

Desvincular:

```http
DELETE {API}/api/carrierGuide/{matrizId}/relations/{guiaHijaId}
```

El backend impide auto-vínculos, ciclos, dos matrices para una hija y guías anuladas.

## 10. Caja Yadira y control de abastecimiento de combustible

### 10.1 Caja Yadira

Forma de pago:

```http
GET {API}/api/cajaYadira/payment-method
```

Respuesta:

```json
{
  "code": "CAJA_YADIRA",
  "name": "Caja Yadira"
}
```

En el formulario de gastos usar el código `CAJA_YADIRA`.

Registrar movimiento:

```http
POST {API}/api/cajaYadira/movement
Content-Type: multipart/form-data
```

Campos:

| Campo | Req. | Descripción |
|---|---:|---|
| `movement_date` | Sí | Fecha. |
| `movement_time` | No | Hora `HH:mm`. |
| `type` | Sí | `Ingreso` o `Egreso`. |
| `operation_number` | No | Número de operación. |
| `amount` | Sí | Mayor que cero. |
| `person_id` | No | Persona relacionada. |
| `concept` | Sí | Concepto. |
| `observation` | No | Observación. |
| `image` | No | Boleta/factura/sustento; máximo 10 MB. |

Listado:

```http
GET {API}/api/cajaYadira
```

Filtros: `date_start`, `date_end`, `type`, `concept`, `page`, `per_page`.

La respuesta incluye `data`, paginación y el resumen:

```json
{
  "summary": {
    "ingresos": 4873.90,
    "egresos": 4835.95,
    "saldo": 37.95
  }
}
```

Exportaciones y detalle:

```http
GET    {API}/api/cajaYadira/export-excel
GET    {API}/api/cajaYadira/export-pdf
GET    {API}/api/cajaYadira/{id}
DELETE {API}/api/cajaYadira/{id}
```

Enviar los mismos filtros a Excel/PDF. Confirmar antes de eliminar.

### 10.2 Abastecimiento de combustible

#### Grifos

```http
GET    {API}/api/fuel/stations
POST   {API}/api/fuel/stations
PUT    {API}/api/fuel/stations/{id}
DELETE {API}/api/fuel/stations/{id}
```

```json
{
  "name": "Grifo ABC",
  "ruc": "20123456789",
  "address": "Av. Principal 123",
  "phone": "999999999",
  "credit_days": 30,
  "active": true
}
```

Un grifo con abastecimientos no se elimina; debe desactivarse con `active=false`.

#### Asignación chofer–vehículo

```http
GET    {API}/api/fuel/assignments
POST   {API}/api/fuel/assignments
PUT    {API}/api/fuel/assignments/{id}
DELETE {API}/api/fuel/assignments/{id}
```

```json
{
  "vehicle_id": 10,
  "worker_id": 25,
  "start_date": "2026-10-01",
  "end_date": null,
  "active": true
}
```

No se permiten asignaciones activas solapadas para el mismo vehículo. Vehículos y choferes se consultan con los CRUD existentes (`/vehicle` y `/worker`).

#### Registrar abastecimiento

```http
POST {API}/api/fuel/purchases
Content-Type: multipart/form-data
```

Obligatorios:

```text
fuel_station_id
vehicle_id
worker_id
assignment_id
fuel_date
odometer_km
quantity
unit_price
payment_condition       # Contado | Crédito
invoice_number
odometer_image          # imagen obligatoria
pump_start_image        # imagen obligatoria
pump_end_image          # imagen obligatoria
```

Opcionales:

```text
fuel_type
payment_method
payment_operation_number
invoice_date
due_date
observation
```

Ejemplo de campos:

```text
fuel_station_id=1
vehicle_id=10
worker_id=25
assignment_id=3
fuel_date=2026-10-21
odometer_km=125430
fuel_type=Petróleo
quantity=80.500
unit_price=14.2500
payment_condition=Crédito
invoice_number=F001-00012345
invoice_date=2026-10-21
due_date=2026-11-20
odometer_image=<archivo>
pump_start_image=<archivo>
pump_end_image=<archivo>
```

El backend calcula `total = quantity × unit_price` y valida que la asignación corresponda al vehículo, chofer y fecha.

Contado: queda `Pagado`, no crea cuenta por pagar y conserva el número de operación.

Crédito: crea una cuenta por pagar `Pendiente`; usa `due_date` o calcula la fecha de factura/abastecimiento más `credit_days` del grifo.

#### Listado, pagos y reportes

```http
GET  {API}/api/fuel/purchases
GET  {API}/api/fuel/purchases/payables
POST {API}/api/fuel/purchases/{id}/pay
GET  {API}/api/fuel/purchases/export-excel
GET  {API}/api/fuel/purchases/export-pdf
GET  {API}/api/fuel/purchases/{id}
DELETE {API}/api/fuel/purchases/{id}
```

El listado y ambas exportaciones aceptan los filtros `vehicle_id`, `plate`, `worker_id`, `fuel_station_id`, `fuel_station`, `date_start`, `date_end`, `status`, `page` y `per_page`.

Para el reporte solicitado:

- `date_start` y `date_end`: rango de fecha del abastecimiento, inclusivo.
- `plate`: búsqueda parcial por placa; también se puede enviar `vehicle_id`.
- `fuel_station`: búsqueda parcial por nombre del grifo; también se puede enviar `fuel_station_id`.
- `status`: `Pagado`, `En deuda`/`Deuda`, o `Vencido`.

Ejemplos:

```http
GET {API}/api/fuel/purchases/export-excel?date_start=2026-01-01&date_end=2026-03-31&plate=ABC123&fuel_station_id=2&status=deuda
GET {API}/api/fuel/purchases/export-pdf?date_start=2026-01-01&date_end=2026-03-31&plate=ABC123&fuel_station_id=2&status=pagado
```

La columna `Situación` se calcula así: `Pagado` cuando el abastecimiento está cancelado, `Vencido` cuando existe saldo y pasó la fecha de vencimiento, y `En deuda` cuando existe saldo pendiente aún no vencido.

Las cuentas por pagar aceptan `status=Pendiente` o `status=Vencido`. Una deuda pendiente cuya fecha ya venció se presenta como `Vencido`.

Pago de crédito:

```json
{
  "paymentDate": "2026-10-25",
  "amount": 500.00,
  "payment_method": "Depósito",
  "operation_number": "OP-9988",
  "comment": "Pago parcial de factura"
}
```

Si el saldo llega a cero pasa a `Pagado`; si queda saldo, permanece `Pendiente`. No eliminar un abastecimiento con pagos registrados.

El Excel histórico incluye fecha, grifo, vehículo, chofer, kilometraje, combustible, cantidad, precio unitario, total, condición de pago, factura, operación, vencimiento, estado y observación.

---

## Reglas generales de integración

- Usar `blob` para descargar Excel y PDF.
- Mantener filtros al cambiar de página.
- Deshabilitar botones mientras exista una petición activa.
- Manejar tanto `error` como `message` en respuestas de error.
- Diferenciar `401`, `403`, `404` y `422`.
- Confirmar todas las eliminaciones.
- Usar `multipart/form-data` para cargos, Caja Yadira y combustible.
- No convertir campos nuevos opcionales en obligatorios.
- No cambiar estados automáticamente desde formularios de edición.
- Antes de probar archivos en el servidor ejecutar:

```bash
php artisan migrate
php artisan storage:link
```

## Checklist de pruebas

- [ ] Rango de fechas del mismo día y rango amplio.
- [ ] Excel con los mismos filtros de pantalla.
- [ ] Situaciones `Pendiente`, `Pagado`, `Vencido` y `Sin Factura`.
- [ ] Eliminación autorizada/no autorizada y confirmación.
- [ ] Motivo obligatorio de anulación.
- [ ] Rechazo de referencia de guía duplicada.
- [ ] Guía `Entregado` permanece `Entregado` al editar viaje.
- [ ] Búsqueda parcial de glosa.
- [ ] Matriz con varias guías hijas, sin ciclos ni doble matriz.
- [ ] Caja Yadira con ingreso, egreso, imagen y saldo.
- [ ] Combustible con las tres imágenes obligatorias.
- [ ] Compra contado, crédito y pago parcial/total.
- [ ] Histórico de combustible filtrado y exportado.
