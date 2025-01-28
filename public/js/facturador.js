function declararBoletaFactura(idventa, idtipodocumento, empresa_id) {
  if (idtipodocumento == 3) {
    var funcion = "enviarBoleta";
  } else {
    var funcion = "enviarFactura";
  }
  $.ajax({
    type: "GET",
    url:
      "https://develop.garzasoft.com:81/transporteFacturadorZip/controlador/contComprobante.php?funcion=" +
      funcion,
    data: "idventa=" + idventa + "&empresa_id=" + empresa_id,
    success: function (a) {
      console.log(a);
    },
  });
}
function getArchivosDocument(idventa, $typeDocument) {
  var funcion = "buscarNumeroSolicitud";

  $.ajax({
    type: "GET",
    url:
      "https://develop.garzasoft.com:81/transporteFacturadorZip/controlador/contComprobante.php?funcion=" +
      funcion,
    data: "idventa=" + idventa + "&typeDocument=" + $typeDocument,
    success: function (a) {
      console.log(a);
    },
  });
}

function declararNotaCredito(idventa, empresa_id) {
  var funcion = "enviarNotaCredito";

  $.ajax({
    type: "GET",
    url:
      "https://develop.garzasoft.com:81/transporteFacturadorZip/controlador/contComprobante.php?funcion=" +
      funcion,
    data: "idventa=" + idventa + "&empresa_id=" + empresa_id,
    success: function (a) {
      console.log(a);
    },
  });
}

function declararGuia(idGuia, empresa_id) {
  var funcion = "enviarGuiaRemision";

  $.ajax({
    type: "GET",
    url:
      "https://develop.garzasoft.com:81/transporteFacturadorZip/controlador/contComprobante.php?funcion=" +
      funcion,
    data: "idventa=" + idGuia + "&empresa_id=" + empresa_id,
    success: function (a) {
      console.log(a);
    },
  });
}

$(document).ready(function () {
  // getArchivosDocument(79,"venta");
  // getArchivosDocument(26,"nota");
});

// Ejemplo de cómo llamar la función:
$(document).ready(function () {
  // declararGuia(3, 1);
  // declararNotaCredito(6, 1);
});

// Ejemplo de cómo llamar la función:
// $(document).ready(function () {
//  declararBoletaFactura(630, 3,1); // Llamada de prueba con valores de ejemplo
// });
