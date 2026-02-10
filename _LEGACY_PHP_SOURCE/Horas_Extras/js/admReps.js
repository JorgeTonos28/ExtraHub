$(document).ready(function() {
  generateEmployeesTable();
  generateLotesTable();
  generateObjecionesTable();

  $('#buscarLote').click(function() {
    $('#listaEmpleados').hide();
    $('#historialLotes').show();
  });

  $('.backBtn').click(function() {
    $('#historialLotes').hide();
    $('#listaEmpleados').show();
  });

  $('#crearReporte').click(function() {
    $('#PanelInicial').hide();
    $('#SelectEmpleado').show();
    initSelect2(); // Inicializar Select2
  });
});

function generateEmployeesTable() {
  var empleados = [
    { codigo: "5005", nombre: "Juan Perez", horas: "8", totalPagar: "RD$ 1,000.00", estado: "Pendiente" },
    { codigo: "2456", nombre: "Maria Lopez", horas: "6", totalPagar: "RD$ 800.00", estado: "Pendiente" },
    { codigo: "1829", nombre: "Carlos Fernandez", horas: "9", totalPagar: "RD$ 1,125.00", estado: "Completado" },
    { codigo: "3076", nombre: "Ana Torres", horas: "7", totalPagar: "RD$ 875.00", estado: "En proceso" },
    { codigo: "4590", nombre: "Luis Ramos", horas: "5", totalPagar: "RD$ 625.00", estado: "Completado" },
    { codigo: "2841", nombre: "Carmen Diaz", horas: "8", totalPagar: "RD$ 1,000.00", estado: "Pendiente" },
    { codigo: "3325", nombre: "Jose Jimenez", horas: "10", totalPagar: "RD$ 1,250.00", estado: "Completado" },
    { codigo: "7890", nombre: "Sofia Castro", horas: "6", totalPagar: "RD$ 750.00", estado: "Pendiente" },
    { codigo: "6543", nombre: "Roberto Alvarez", horas: "8", totalPagar: "RD$ 1,000.00", estado: "En proceso" },
    { codigo: "3025", nombre: "Patricia Salas", horas: "7", totalPagar: "RD$ 875.00", estado: "Completado" },
    { codigo: "1478", nombre: "Eduardo Rojas", horas: "5", totalPagar: "RD$ 625.00", estado: "Pendiente" },
    { codigo: "2683", nombre: "Diana Morales", horas: "9", totalPagar: "RD$ 1,125.00", estado: "En proceso" },
    { codigo: "3940", nombre: "Miguel Angel Torres", horas: "6", totalPagar: "RD$ 750.00", estado: "Completado" },
    { codigo: "2109", nombre: "Gabriela Sanchez", horas: "8", totalPagar: "RD$ 1,000.00", estado: "Pendiente" },
    { codigo: "8765", nombre: "Andres Nuñez", horas: "7", totalPagar: "RD$ 875.00", estado: "Completado" }
  ];

  var tbody = $('#empleadosLotePendiente');
  tbody.empty(); // Limpiar la tabla antes de insertar los nuevos datos

  $.each(empleados, function(i, empleado) {
    tbody.append(
      `<tr>
        <td>${empleado.codigo}</td>
        <td>${empleado.nombre}</td>
        <td>${empleado.horas}</td>
        <td>${empleado.totalPagar}</td>
        <td>${empleado.estado}</td>
      </tr>`
    );
  });
}

function generateLotesTable() {
  var lotes = [
    { periodo: "Ene-Feb", fechaCorte: "2024-02-28", estado: "Completado", totalPagado: "RD$ 20,000.00" },
    { periodo: "Feb-Mar", fechaCorte: "2024-03-31", estado: "Completado", totalPagado: "RD$ 15,000.00" },
    { periodo: "Mar-Abr", fechaCorte: "2024-04-30", estado: "Pendiente", totalPagado: "RD$ 18,000.00" },
    { periodo: "Abr-May", fechaCorte: "2024-05-31", estado: "Pendiente", totalPagado: "RD$ 22,000.00" },
    { periodo: "May-Jun", fechaCorte: "2024-06-30", estado: "En proceso", totalPagado: "RD$ 16,500.00" },
    { periodo: "Jun-Jul", fechaCorte: "2024-07-31", estado: "En proceso", totalPagado: "RD$ 19,000.00" },
    { periodo: "Jul-Ago", fechaCorte: "2024-08-31", estado: "En proceso", totalPagado: "RD$ 17,250.00" },
    { periodo: "Ago-Sep", fechaCorte: "2024-09-30", estado: "Pendiente", totalPagado: "RD$ 20,750.00" },
    { periodo: "Sep-Oct", fechaCorte: "2024-10-31", estado: "Completado", totalPagado: "RD$ 15,800.00" },
    { periodo: "Oct-Nov", fechaCorte: "2024-11-30", estado: "Completado", totalPagado: "RD$ 21,000.00" }
  ];

  var tbody = $('#loteTableBody');
  tbody.empty(); // Limpiar la tabla antes de insertar los nuevos datos

  $.each(lotes, function(i, lote) {
    tbody.append(
      `<tr>
        <td>${lote.periodo}</td>
        <td>${lote.fechaCorte}</td>
        <td>${lote.estado}</td>
        <td>${lote.totalPagado}</td>
      </tr>`
    );
  });
}

function generateObjecionesTable() {

  var objeciones = [
    { codigo: "2456", nombre: "Maria Lopez", horas: "2", cantidad: "1"},
    { codigo: "4590", nombre: "Luis Ramos", horas: "3", cantidad: "2"},
    { codigo: "3325", nombre: "Jose Jimenez", horas: "2", cantidad: "1"},
    { codigo: "3325", nombre: "Jose Jimenez", horas: "1", cantidad: "3" },
    { codigo: "8765", nombre: "Andres Nuñez", horas: "2", cantidad: "1"}
  ];

  var tbody = $('#objecionesLotePendiente');
  tbody.empty(); // Limpiar la tabla antes de insertar los nuevos datos

  $.each(objeciones, function(i, objeciones) {
    tbody.append(
      `<tr>
        <td>${objeciones.codigo}</td>
        <td>${objeciones.nombre}</td>
        <td>${objeciones.horas}</td>
        <td>${objeciones.cantidad}</td>
      </tr>`
    );
  });
}