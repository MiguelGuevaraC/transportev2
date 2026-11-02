# Cambios de backend: series y validación de anexos

## 1. Filtro por serie en guías

Se agregó un catálogo de series para llenar el nuevo selector del listado de guías:

```http
GET {API}/api/carrierGuide/series
```

Respuesta:

```json
{
  "data": [
    { "serie": "V001", "name": "V001" },
    { "serie": "V002", "name": "V002" }
  ],
  "total": 2
}
```

También se puede filtrar el catálogo por sucursal:

```http
GET {API}/api/carrierGuide/series?branch_office_id=1
```

Para filtrar el listado de guías, enviar `serie`:

```http
GET {API}/api/carrierGuide?serie=V001&page=1&per_page=15
```

El endpoint también acepta `series` como alias, pero se recomienda utilizar `serie`. El filtro compara la serie completa antes del guion de `numero`; por ejemplo, `V001` encuentra `V001-00026063`.

El listado `GET /api/carrierGuide` incluye en cada elemento el campo `amortizaciones_resumen` con el detalle de las cuotas, montos pagados, saldo pendiente y pagos realizados. Si la guía no tiene deuda ni amortizaciones, devuelve:

```json
{
  "amortizaciones_resumen": "No tienes deudas ni amortizaciones registradas."
}
```

El contenido puede incluir saltos de línea; el frontend debe mostrarlo como texto multilínea o usar `white-space: pre-line`.

El mismo parámetro `serie` se puede enviar al exportar:

```http
GET {API}/api/carrierGuide_export_excel?serie=V001&startDate=2026-10-01&endDate=2026-10-30&dateType=guide
```

Cuando se envía un rango de fechas, el Excel se genera con el formato detallado de la pestaña `GUÍAS` del control operativo: carga material, remitente, destinatario, origen, destino, documentos anexos, peso, flete, deuda, condición de pago, estados, fechas, conductores, placas y MTC.

## 2. Validación de anexos

La validación de anexos continúa aplicando la regla: una guía remitente activa no puede estar asociada a más de una guía transportista.

Se corrigió el falso positivo para que no se consideren como conflicto:

- Guías transportistas huérfanas, sin recepción relacionada.
- Guías cuya recepción fue eliminada.
- Guías anuladas.

Solo se bloquea cuando el anexo ya está asociado a otra guía transportista activa y con recepción vigente. El frontend puede mostrar el mensaje devuelto por el backend:

```json
{
  "message": "La guía remitente ya está asociada a otra guía transportista.",
  "reference": "T004-3340",
  "carrier_guide": "V001-00012345"
}
```

## 3. Rutas modificadas

```http
GET {API}/api/carrierGuide/series
GET {API}/api/carrierGuide?serie=V001
GET {API}/api/carrierGuide_export_excel?serie=V001
```

## 4. Actualizar fotos del abastecimiento de combustible

Para subir fotos posteriormente, usar:

```http
POST {API}/api/fuel/purchases/{id}/images
Content-Type: multipart/form-data
```

El frontend puede enviar una, dos o las tres imágenes. Los nombres permitidos son:

```text
odometer_image       # Foto del kilometraje/contómetro
pump_start_image     # Surtidor antes de abastecer
pump_end_image       # Surtidor después de abastecer
```

Cada archivo debe ser una imagen de máximo 10 MB. No se deben enviar los demás datos del abastecimiento; la API solo actualiza las fotos enviadas.

Ejemplo:

```bash
curl -X POST "{API}/api/fuel/purchases/25/images" \
  -H "Authorization: Bearer {TOKEN}" \
  -F "pump_end_image=@surtidor-final.jpg"
```

Respuesta exitosa:

```json
{
  "message": "Fotos del abastecimiento actualizadas correctamente.",
  "data": {
    "id": 25,
    "odometer_image": "/storage/fuel-control/odometro.jpg",
    "pump_start_image": "/storage/fuel-control/surtidor-inicio.jpg",
    "pump_end_image": "/storage/fuel-control/surtidor-final.jpg"
  }
}
```
