

let departamentos = {};
let gerencias = {};
let empleadoSeleccionadoValido = false;
let fechasValidas = [];
// Configurar clendario
let calendario  = flatpickr("#calendario", {
    inline: true,
    mode: "multiple",
    dateFormat: "d-m-Y",
    locale: 'es'
});

function cargarDepartamentos() {
    $.ajax({
        url: '../admin/api/get_departamentos.php',
        dataType: 'json',
        success: function(respuesta) {
            // Convertir el array de departamentos a un objeto para acceso rápido por ID
            respuesta.forEach(function(depto) {
                departamentos[depto.id_departamento] = depto.nombre;
            });
        },
        error: function() {
            alert("Error al cargar los departamentos.");
        }
    });
}
// Función para cargar los datos de las gerencias
function cargarGerencias() {
    $.ajax({
        url: '../admin/api/get_gerencias.php',
        dataType: 'json',
        success: function(respuesta) {
            // Convertir el array de gerencias a un objeto para acceso rápido por ID
            respuesta.forEach(function(ger) {
                gerencias[ger.id_gerencia] = ger.nombre;
            });
        },
        error: function() {
            alert("Error al cargar las gerencias.");
        }
    });
}
//  Cerrar Modal
function cerrarModal() {
    $('#infoModal').modal('hide');
}
function resetCard() {
    $('#srchCard .Nombre').text("");
    $('#srchCard .Cargo').text("");
    $('#srchCard .Dependencia').text("");
    $('#srchCard .Sueldo').text("");
}
function searchBarError() {
    alert("No disponible aún");
}
//Función crear selector dinámico para el campo de búsqueda
function initSelect2() {
    var campo = 'codigo';
    $('#dbSearch').select2({
        minimumInputLength: 1,
        placeholder: 'Buscar usuario...', // Texto del placeholder
        ajax: {
            url: '../admin/api/buscar_nomina.php',
            dataType: 'json',
            delay: 250,
            data: function (params) {
                // Inicialmente, asumimos que el campo es 'nombre' si el término incluye letras
                var campo = /[a-zA-Z]/.test(params.term) ? 'nombre' : null;

                // Si el término es exclusivamente numérico (sin letras), cambiamos el campo a 'codigo'
                if (/^\d+$/.test(params.term)) {
                    campo = 'codigo';
                }

                // Si el campo sigue siendo null, significa que el término contiene caracteres especiales o espacios sin letras (inapropiado para ambos casos)
                if (!campo) {
                    searchBarError();
                    return false; // Detiene la ejecución si el término no es válido
                }

                // Retornamos el término de búsqueda y el campo determinado
                return {
                    termino: params.term,
                    campo: campo
                };
            },
            processResults: function (data) {
                if (data.error) {
                    // Mostrar mensaje de error si no se encuentra el usuario
                    $('#infoModal .modal-title').text("Error");
                    $('#infoModal .modal-body').text(data.error);
                    $('#infoModal').modal('show');
                    return { results: [] };
                }
                return {
                    results: $.map(data, function(item) {
                        return {
                            id: item.codigo,
                            text: item.nombre, // Asumimos que quieres mostrar el nombre en el select
                            item: item // Conservamos el objeto completo por si necesitas más datos
                        };
                    })
                };
            },
            cache: true
        }
    }).on('select2:select', function (e) {
            empleadoSeleccionadoValido = false; // Reiniciar
            resetCard(); // Limpiar campos de la tarjeta de usuario antes de rellenarla con la coincidencia
            var data = e.params.data.item; // Variable para almacenar datos usuario
            // Modo Modificar Usuario
            $.ajax({
                url: '../admin/api/buscar_usuario.php',
                dataType: 'json',
                data: {
                    termino: data.codigo,
                    campo: 'codigo'
                },
                success: function(respuesta) {
                    if (respuesta.usuario_en_sistema) {
                        // Aquí es donde necesitas verificar el departamento
                        // Validar si el usuario puede seleccionar a este colaborador
                        $.ajax({
                            url: '../admin/api/validarSeleccionEmpleado.php', // Asegúrate de que la ruta es correcta
                            type: 'GET', // Método GET explícitamente
                            data: {
                                depID_Selected: respuesta.departamento_id, // ID de departamento del usuario seleccionado
                                nivelCorporativo: respuesta.nivelCorporativo, // Nivel corporativo del empleado seleccionado
                                gerencia_id: respuesta.gerencia, // id de la gerencia del empleado seleccionado
                                codigoEmpleado: data.codigo // Código del empleado seleccionado
                            },
                            dataType: 'json',
                            success: function(response) {
                                if (!response.esValido) {
                                    // alert(response.mensaje);
                                    if(response.mensaje == "Departamento" || response.mensaje == "Gerencia") {
                                        let nombreDependenncia = "???"
                                        if(response.mensaje == "Departamento"){
                                            nombreDependenncia = departamentos[respuesta.departamento] || 'Departamento desconocido'; 
                                        } else {
                                            nombreDependenncia = gerencias[respuesta.gerencia] || 'Gerencia desconocida';
                                        }
                                        empleadoSeleccionadoValido = false; // Reiniciar si la selección no es válida
                                        $('#srchCardEmpty').show();
                                        $('#srchCard').show().addClass('d-none');
                                        $('#infoModal .modal-title').text("Error");
                                        $('#infoModal .modal-body').text('Este empleado pertenece a: ['+ nombreDependenncia +'] No puedes seleccionar colaboradores de otros departamentos.');
                                        $('#infoModal').modal('show');
                                        $('#dbSearch').val(null).trigger('change'); // Restablecer el campo de selección
                                    } else if (response.mensaje == "Nivel") {
                                        empleadoSeleccionadoValido = false; // Reiniciar si la selección no es válida
                                        $('#srchCardEmpty').show();
                                        $('#srchCard').show().addClass('d-none');
                                        $('#infoModal .modal-title').text("Error");
                                        $('#infoModal .modal-body').text('No puede generar horas extras para este colaborador debido a que no pertenece al personal de apoyo.');
                                        $('#infoModal').modal('show');
                                        $('#dbSearch').val(null).trigger('change'); // Restablecer el campo de selección
                                    }
                                } else {
                                    // La selección es válida, proceder según sea necesario
                                    empleadoSeleccionadoValido = true; // Seleccion válido
                                    calendario.clear(); // Resetear paso 2: calendario
                                    detalleDiaVacio(); // Resetear paso 3: detalleDia
                                    reportePagoVacio(); // Resetar paso 4: reporte de pago del período
                                    printEmpInfo(respuesta); // Si es el mismo departamento, procede normalmente
                                    $('#srchCardEmpty').hide();
                                    $('#srchCard').show().removeClass('d-none');
                                }
                            },
                            error: function(jqXHR, textStatus, errorThrown) {
                                // Mejor manejo del error para depuración
                                empleadoSeleccionadoValido = false; // Reiniciar si la selección no es válida
                                calendario.clear(); // Resetear paso 2: calendario
                                detalleDiaVacio(); // Resetear paso 3: detalleDia
                                reportePagoVacio(); // Resetar paso 4: reporte de pago del período
                                alert("Error al realizar la validación: " + textStatus + ", " + errorThrown);
                            }
                        });
                    } else {
                        empleadoSeleccionadoValido = false; // Reiniciar si la selección no es válida
                        calendario.clear(); // Resetear paso 2: calendario
                        detalleDiaVacio(); // Resetear paso 3: detalleDia
                        reportePagoVacio(); // Resetar paso 4: reporte de pago del período
                        $('#srchCardEmpty').show();
                        $('#srchCard').show().addClass('d-none');
                        // Mostrar mensaje de error si no se encuentra el usuario
                        $('#infoModal .modal-title').text("Ups!");
                        $('#infoModal .modal-body').text('El usuario no se encuentra en el sistema. Por favor, crea el usuario primero.');
                        $('#infoModal').modal('show');
                        return { results: [] };
                    }
                },
                error: function() {
                    empleadoSeleccionadoValido = false; // Reiniciar si la selección no es válida
                    calendario.clear(); // Resetear paso 2: calendario
                    detalleDiaVacio(); // Resetear paso 3: detalleDia
                    reportePagoVacio(); // Resetar paso 4: reporte de pago del período
                    $('#srchCardEmpty').show();
                    $('#srchCard').show().addClass('d-none');
                    // Mostrar mensaje de error
                    $('#infoModal .modal-title').text("Error");
                    $('#infoModal .modal-body').text(respuesta.error);
                    $('#infoModal').modal('show');
                    return { results: [] };
                }
            });

            // Limpia el campo de Select2
            $('#dbSearch').val(null).trigger('change');
    });
}

function capitalize(text) {
    // Asegura que text sea tratado como una cadena
    text = String(text);
    // Utiliza una expresión regular que maneja caracteres Unicode
    return text.toLowerCase().replace(/(^|\s|\p{P})\p{L}/gu, (s) => s.toUpperCase());
}


function printEmpInfo(respuesta) {
    $('#srchCard .Nombre').html(`<strong>${capitalize(respuesta.preferenciaFirma)} - ${respuesta.codigo}</strong>`)
    $('#InfoEmp .Nombre').html(`<strong>${capitalize(respuesta.preferenciaFirma)} - ${respuesta.codigo}</strong>`)
    $('.Emp-info .Nombre').html(`${respuesta.codigo} - ${capitalize(respuesta.preferenciaFirma)}`);
    $('#srchCard .Cargo').html(`<em>${capitalize(respuesta.cargo)}</em>`);
    $('#InfoEmp .Cargo').html(capitalize(respuesta.cargo));

    // Usar el nombre del departamento basado en el ID
    let nombreDepartamento = departamentos[respuesta.departamento] || 'Departamento desconocido';
    let nombreGerencia = gerencias[respuesta.gerencia] || 'Gerencia desconocida';
    $('#srchCard .Dependencia').html(`<span>${capitalize(nombreDepartamento)}</span>`);
    $('#InfoEmp .Dependencia').text(nombreDepartamento);
    $('#InfoEmp .Gerencia').text(nombreGerencia.toUpperCase());
    $('#srchCard .Sueldo').html(`<small><em>RD$${parseFloat(respuesta.salario_mensual).toLocaleString()}.00</em></small>`);
    $('#srchCard .Gerencia').html(`<span>${nombreGerencia}</span>`);
    $('#srchCard').show();
    $('#srchCardEmpty').hide();

    // Imprimir horario 
    const dias = ['lunes', 'martes', 'miercoles', 'jueves', 'viernes', 'sabado', 'domingo'];
    
    dias.forEach(dia => {
        let esDiaLibre = !respuesta.horario[dia] || respuesta.horario[dia].trim() === "";
        let $diaDiv = $(`.dayNames .${dia}`);
        let $diaDiv2 = $(`.Emp-Hr .dayNames .${dia}`);
        let $diaHrsDiv = $(`.dayHrs .${dia}`);

        if (esDiaLibre) {
            $diaDiv.removeClass('text-secondary-emphasis bg-secondary-subtle border-secondary-subtle').addClass('border-success-subtle border-3 text-success');
            $diaDiv.find('span').addClass('fw-bold'); // Asegura que la inicial esté en negrita

            $diaDiv2.removeClass('text-secondary-emphasis bg-secondary-subtle border-secondary-subtle').addClass('border-success-subtle border-3 text-success fw-bold');
            $diaDiv2.tooltip('dispose').attr("data-bs-title","Libre").tooltip(); // Actualizar tooltip
            
            $diaHrsDiv.addClass('text-success');
            $diaHrsDiv.find('span').html('Libre');
            
        } else {
            let horarioTexto = calcularHoraSalida(respuesta.horario[dia]).replace(/:00/g, ''); // Elimina los segundos para el formato deseado
            $diaDiv.removeClass('border-success-subtle border-3 text-success').addClass('text-secondary-emphasis bg-secondary-subtle border-secondary-subtle');
            $diaDiv.find('span').removeClass('fw-bold'); // Remueve la negrita de la inicial

            $diaDiv2.removeClass('border-success-subtle border-3 text-success fw-bold').addClass('text-secondary-emphasis bg-secondary-subtle border-secondary-subtle');
            $diaDiv2.tooltip('dispose').attr("data-bs-title",horarioTexto).tooltip(); // Actualizar tooltip

            $diaHrsDiv.removeClass('text-success');
            $diaHrsDiv.find('span').text(horarioTexto);
            
        }
    });
}

// Función para calcular la hora de salida de cada día de colaborador seleccionado
function calcularHoraSalida(horaEntrada) {
    if (!horaEntrada) return 'Libre'; // Si no hay hora de entrada, es un día libre

    let [hora, minutos] = horaEntrada.split(':');
    let fechaEntrada = new Date();
    fechaEntrada.setHours(parseInt(hora), parseInt(minutos), 0); // Establece la hora de entrada

    let fechaSalida = new Date(fechaEntrada.getTime() + 8 * 60 * 60 * 1000); // Suma 8 horas

    // Convierte a formato 12 horas con AM/PM
    let horasEntrada = fechaEntrada.getHours();
    let periodoEntrada = horasEntrada >= 12 ? 'pm' : 'am';
    horasEntrada = horasEntrada % 12;
    horasEntrada = horasEntrada ? horasEntrada : 12; // El '0' se convierte en '12'

    let horasSalida = fechaSalida.getHours();
    let periodoSalida = horasSalida >= 12 ? 'pm' : 'am';
    horasSalida = horasSalida % 12;
    horasSalida = horasSalida ? horasSalida : 12; // El '0' se convierte en '12'

    let horaTextoEntrada = horasEntrada + periodoEntrada;
    let horaTextoSalida = horasSalida + periodoSalida;

    return `${horaTextoEntrada} - ${horaTextoSalida}`;
}



//  Función para generar secciones reporte de horas por día
function generarCamposHoras() {

    let menu = $('#listaFechasDropdown');
    menu.empty(); // Limpia las entradas anteriores
    let index = 0;
    fechasValidas.forEach(fecha => {
        index ++;
        let fechaFormateada = formatearFecha(fecha);
        menu.append(`<li><a class="dropdown-item" href="#" data-fecha="${fecha}">${fechaFormateada}</a></li>`);
        if (index == 1) {
            $('#dropdownMenuButton').text(fechaFormateada);
        }
    });

    let fechaFormateada2 = formatDateToYYYYMMDD(fechasValidas[0]);
    $.ajax({
        url: '../admin/api/gestionar_dia.php',
        type: 'POST',
        dataType: 'json',
        data: {
            accion: 'obtener_ponchado',
            fecha: fechaFormateada2
        },
        success: function(response) {
            // Aquí es donde manipulas el valor del objeto response
            if (response.valido) {
                let entrada = new Date('1970-01-01T' + response.mensaje['entrada']).toLocaleTimeString('en-US', {
                    hour: '2-digit',
                    minute: '2-digit',
                    hour12: true
                });

                let salida = new Date('1970-01-01T' + response.mensaje['salida']).toLocaleTimeString('en-US', {
                    hour: '2-digit',
                    minute: '2-digit',
                    hour12: true
                });
                
                // Si el ponchado es válido
                $('#ponchadoDiaSelecc').html(`<i class="fa-solid fa-fingerprint mx-1"></i> ${entrada} - ${salida}`);
                if (response.mensaje['excepcion'] != "--") {
                    $('#ponchadoDiaSelecc').tooltip('dispose').attr("data-bs-title",response.mensaje['excepcion']).tooltip(); // Actualizar tooltip
                }
                
                // Luego de actualizar el día activo, obtener los datos del reporte para ese día
                $.ajax({
                    url: '../admin/api/gestionar_dia.php',
                    type: 'POST',
                    dataType: 'json',
                    data: {
                        accion: 'obtener_reporte',
                        fecha: fechaFormateada2
                    },
                    success: function(reportResponse) {
                        if (reportResponse.success) {
                            // Actualizar los inputs con los datos del reporte
                            if (reportResponse.data.hora_entrada !== null) {
                                $('#HoraEntrada').val(reportResponse.data.hora_entrada);
                            } else {
                                $('#HoraEntrada').val(''); // Limpiar si es null
                            }
                            if (reportResponse.data.hora_salida !== null) {
                                $('#HoraSalida').val(reportResponse.data.hora_salida);
                            } else {
                                $('#HoraSalida').val(''); // Limpiar si es null
                            }
                            if (reportResponse.data.descripcion !== null) {
                                $('#detDia').val(reportResponse.data.descripcion);
                            } else {
                                $('#detDia').val(''); // Limpiar si es null
                            }
                            // Verificar horario y días libres
                            $.ajax({
                                url: '../admin/api/gestionar_dia.php',
                                type: 'POST',
                                dataType: 'json',
                                data: {
                                    accion: 'obtener_horario',
                                    fecha: fechaFormateada2
                                },
                                success: function(horario) {
                                    if (!horario.diaLaboral) {
                                        $('#tipoDia').text('Libre');
                                        $('#tipoDia').removeClass('text-primary').addClass('text-success');
                                        if (reportResponse.data.bono_dia_libre !== "0.00") {
                                            $('#subTotalHRs .valor').text((parseFloat(reportResponse.data.total_decimal - reportResponse.data.bono_dia_libre).toFixed(2)));
                                        }else{
                                            $('#subTotalHRs .valor').text("0.00");
                                        }
                                        $('#subTotalHRs').show();
                                        $('#bonoDiaLibre .valor').text(parseFloat(reportResponse.data.bono_dia_libre).toFixed(2));
                                        $('#bonoDiaLibre').show();
                                        $('#totalHRs .valor').html('<span class="badge rounded-pill bg-info mx-2 tinyTxt">Hrs</span>' + parseFloat(reportResponse.data.total_decimal).toFixed(2)); 
                                    }else{
                                        $('#tipoDia').text('Regular');
                                        $('#tipoDia').removeClass('text-success').addClass('text-primary');
                                        $('#subTotalHRs .valor').text("0.00");
                                        $('#subTotalHRs').hide();
                                        $('#bonoDiaLibre .valor').text("0.00");
                                        $('#bonoDiaLibre').hide();
                                        $('#totalHRs .valor').html('<span class="badge rounded-pill bg-info mx-2 tinyTxt">Hrs</span>' + parseFloat(reportResponse.data.total_decimal).toFixed(2));
                                    }
                                },
                                error: function(xhr, status, error) {
                                    console.error('Error al obtener datos del horario:', error);
                                }
                            });
                        } else {
                            console.error("Error al obtener datos del reporte:", reportResponse.message);
                            $('#HoraEntrada').val('');
                            $('#HoraSalida').val('');
                            $('#detDia').val('');
                        }
                    },
                    error: function(xhr, status, error) {
                        console.error('Error al obtener datos del reporte:', error);
                        $('#HoraEntrada').val('');
                        $('#HoraSalida').val('');
                        $('#detDia').val('');
                    }
                });
            } else {
                // Si el ponchado no es válido
                // Usa el mensaje de la respuesta para dar más detalle
                let mensaje = response.mensaje || "La fecha no es válida.";
                console.error(mensaje);
            }
        },
        error: function(xhr, status, error) {
            console.error('Error al conectar con el servidor: ' + error);
            alert('Error de conexión con el servidor');
        }
    });
}

// Función para mostrar los detalles de la fecha seleccionada
function mostrarDetallesFecha(fecha) {
    // Aquí puedes ajustar lo que necesitas hacer para mostrar los detalles
    $('#DiaSelect').children().hide(); // Oculta todos los detalles de las fechas
    $(`#detalles-${formatDateToYYYYMMDD(fecha)}`).show(); // Muestra los detalles específicos de la fecha seleccionada
}

// Función para actualizar el dropdown con las fechas validadas y enviarlas al servidor
function actualizarDropdownFechas() {
    let menu = $('#listaFechasDropdown');
    menu.empty(); // Limpia las entradas anteriores

    // Prepara un array para enviar todas las fechas validadas
    let fechasParaEnviar = fechasValidas.map(fecha => fecha.toISOString().substring(0, 10));

    fechasValidas.forEach(fecha => {
        let fechaFormateada = formatearFecha(fecha);
        menu.append(`<li><a class="dropdown-item" href="#" data-fecha="${fecha}">${fechaFormateada}</a></li>`);
    });

    $.ajax({
        url: '../admin/api/actFechasSelecc.php',
        type: 'POST',
        dataType: 'json',
        data: {
            fechas: fechasParaEnviar
        },
        success: function(response) {
            if (response.success) {
                // console.log("Fechas actualizadas en la sesión.");
            } else {
                console.error("Error al actualizar fechas en la sesión:", response.message);
            }
        },
        error: function(xhr, status, error) {
            console.error('Error al conectar con el servidor:', error);
        }
    });
}


// Función para dar formato a fechas seleccionadas
function formatearFecha(fechaISO) {
    const dias = ['Domingo', 'Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes', 'Sábado'];
    const meses = ['Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio', 'Julio', 'Agosto', 'Septiembre', 'Octubre', 'Noviembre', 'Diciembre'];

    const fecha = new Date(fechaISO);
    fecha.setMinutes(fecha.getMinutes() + fecha.getTimezoneOffset());
    const diaSemana = dias[fecha.getDay()];
    const dia = fecha.getDate();
    const mes = meses[fecha.getMonth()];
    const año = fecha.getFullYear();

    return `${diaSemana} ${dia} de ${mes} del ${año}`;
}

// Función para verificar ponchado del día antes de seleccionarlo en el calendario
function verificarPonchadoYExcepciones(fecha, codigoEmpleado, callback) {
    $.ajax({
        url: '../admin/api/gestionar_dia.php',
        type: 'POST',
        dataType: 'json',
        data: {
            accion: 'obtener_ponchado',
            fecha: fecha,
            codigo: codigoEmpleado // Código del empleado
        },
        success: function(response) {
            if (!response.valido) {
                // Si el ponchado no es válido
                let mensaje = response.mensaje || "La fecha no es válida.";
                callback(false, mensaje);
                return; // Asegúrate de terminar la ejecución aquí
            }

            // Continuar solo si el ponchado es válido
            $.ajax({
                url: '../admin/api/gestionar_dia.php',
                type: 'POST',
                dataType: 'json',
                data: {
                    accion: 'obtener_reporte',
                    fecha: fecha,
                    codigo: codigoEmpleado
                },
                success: function(respuesta) {
                    if (!respuesta.success) {
                        callback(true, "La fecha es válida.");
                        return;
                    }

                    // Verificar el estado del reporte
                    const estadosNoPermitidos = ['revisar', 'pendiente', 'aprobado', 'pagado'];
                    if (estadosNoPermitidos.includes(respuesta.data.estado)) {
                        console.log('Este día tiene un reporte en proceso en el sistema.');
                        callback(false, 'No puede seleccionar este día debido a que ya tiene un reporte en proceso en el sistema para el mismo.');
                    } else {
                        callback(true, "La fecha es válida.");
                    }
                },
                error: function(xhr, status, error) {
                    console.error('Error al obtener datos del reporte:', error);
                    callback(false, 'Error de conexión intentando obtener los datos del reporte');
                }
            });
        },
        error: function(xhr, status, error) {
            console.error('Error al conectar con el servidor:', error);
            callback(false, 'Error de conexión con el servidor');
        }
    });
}

function formatDateToYYYYMMDD(date) {
    let day = date.getDate();
    let month = date.getMonth() + 1; // Los meses empiezan en 0
    let year = date.getFullYear();

    // Asegúrate de que el día y el mes siempre tengan dos dígitos
    day = day < 10 ? '0' + day : day;
    month = month < 10 ? '0' + month : month;

    return year + '-' + month + '-' + day;
}

// Función de manejo de selección de fecha con validación y creación de reporte
function handleDateSelection(date) {
    // Aquí asumimos que date ya ha sido validada como una fecha en formato correcto
    $.ajax({
        url: '../admin/api/gestionar_dia.php',
        type: 'POST',
        data: {
            accion: 'iniciar_reporte',
            fecha: date
        },
        dataType: 'json',
        success: function(response) {
            if (response.success) {
                // console.log(response.message);
                // Puedes actualizar la UI aquí si es necesario
            } else {
                console.error(response.message);
                // Mostrar un mensaje en la interfaz de usuario para explicar el error
                // alert('Error: ' + response.message);
            }
        },
        error: function(xhr, status, error) {
            console.error('Error al conectar con el servidor: ' + error);
            alert('Error de conexión con el servidor');
        }
    });
}

function verificarHorario(fecha, horaEntrada, horaSalida, descripcion, inputId) {
    $.ajax({
        url: '../admin/api/gestionar_dia.php',
        type: 'POST',
        dataType: 'json',
        data: {
            accion: 'revisar_horario',
            fecha: fecha
        },
        success: function(response) {
            if (response.valido) {
                actualizarReporte(fecha, horaEntrada, horaSalida, descripcion, inputId);
            } else {
                alert(response.mensaje);
                $(`#${inputId}`).val(''); // Limpiar input si el horario no es válido
            }
        },
        error: function(xhr, status, error) {
            console.error('Error al verificar el horario:', error);
        }
    });
}

function actualizarDescripcion(fecha, descripcion) {
    $.ajax({
        url: '../admin/api/gestionar_dia.php',
        type: 'POST',
        dataType: 'json',
        data: {
            accion: 'actualizar_descripcion',
            fecha: fecha,
            descripcion: descripcion
        },
        success: function(response) {
            if (!response.success) {
                alert(response.message);
            }
        },
        error: function(xhr, status, error) {
            console.error('Error al actualizar la descripción:', error);
        }
    });
}

function actualizarReporte(fecha, horaEntrada, horaSalida, descripcion, inputId) {
    $.ajax({
        url: '../admin/api/gestionar_dia.php',
        type: 'POST',
        dataType: 'json',
        data: {
            accion: 'actualizar_reporte',
            fecha: fecha,
            hora_entrada: horaEntrada,
            hora_salida: horaSalida,
            descripcion: descripcion
        },
        success: function(response) {
            if (response.success && response.estado_cambiado) {
                alert('Reporte actualizado y marcado como creado.');
            } else if (!response.success) {
                alert(response.message);
                $(`#${inputId}`).val(''); // Limpiar input si la actualización falla
            }
        },
        error: function(xhr, status, error) {
            console.error('Error al actualizar el reporte:', error);
        }
    });
}
function detalleDiaVacio() {
    $('#dropdownMenuButton').text("Selecciona una fecha");
    $('#ponchadoDiaSelecc').html(`<i class="fa-solid fa-fingerprint mx-1"></i> 00:00 - 00:00`);
    $('#tipoDia').text("Desconocido");
    $('#HoraEntrada').val('');
    $('#HoraSalida').val('');
    $('#detDia').val('');
    $('#subTotalHRs .valor').text("0.00");
    $('#bonoDiaLibre .valor').text("0.00");
    $('#totalHRs .valor').text("0.00");
}
function reportePagoVacio() {
    let html = `
            <table class="table table-striped text-start">                                                        
                    <thead>
                        <tr>
                        <th scope="col">No.</th>
                        <th scope="col">Día</th>
                        <th scope="col">Estado</th>
                        <th scope="col">Desde</th>
                        <th scope="col">Hasta</th>
                        <th scope="col">Horas</th>
                        <th scope="col">Factor</th>
                        <th scope="col">Total</th>
                        </tr>
                    </thead>
                    <tbody class="table-group-divider">
                        <tr role="button" data-bs-toggle="tooltip" data-bs-placement="bottom" data-bs-title="--">
                            <td class="text-danger text-center" colspan="8">No hay datos para mostrar T__T</td>
                         </tr>
                        <tr style="font-weight: bold; font-size: 20px;">
                            <td class="text-success text-end" colspan="7">Total</td>
                            <td class="text-success">0.00</td>
                        </tr>
                </tbody>
            </table> `;
            $('#RepTabl').html(html); // Imprimir reporte en HTML
}
function generarReporteDePago() {
    return new Promise((resolve, reject) => {
        $.ajax({
            url: '../admin/api/crear_reporte.php',
            type: 'POST',
            data: { accion: 'generar_reporte' },
            dataType: 'json',
            success: function(response) {
                if (response.success) {

                    // Formateador de moneda
                    const formatter = new Intl.NumberFormat('es-DO', {
                        style: 'currency',
                        currency: 'DOP',
                        minimumFractionDigits: 2
                    });

                    // Formateador de fecha
                    const dateFormatter = new Intl.DateTimeFormat('es-DO', {
                        year: 'numeric',
                        month: '2-digit',
                        day: '2-digit'
                    });

                    // Aquí construyes la tabla y el recuadro con la respuesta
                    let reportes = response.reportes;
                    let html = `<table class="table table-striped text-start">                                                        
                                    <thead>
                                        <tr>
                                        <th scope="col">No.</th>
                                        <th scope="col">Día</th>
                                        <th scope="col">Estado</th>
                                        <th scope="col">Desde</th>
                                        <th scope="col">Hasta</th>
                                        <th scope="col">Horas</th>
                                        <th scope="col">Factor</th>
                                        <th scope="col">Total</th>
                                        </tr>
                                    </thead>
                                    <tbody class="table-group-divider">`;
                    reportes.forEach((reporte, index) => {
                        let subTotal = reporte.total_decimal - reporte.bono_dia_libre;
                        subTotal = subTotal.toFixed(2);
                        let factor = 1.00;
                        if (reporte.bono_dia_libre > 0) {
                            factor = 1.30;
                        }
                        factor = factor.toFixed(2);
                        let formattedDate = dateFormatter.format(new Date(reporte.fecha));
                        html += `<tr role="button" data-bs-toggle="tooltip" data-bs-placement="bottom" data-bs-title="${reporte.descripcion}">
                                    <th scope="row">${index + 1}</th>
                                    <td>${reporte.fecha}</td>
                                    <td>${reporte.estado}</td>
                                    <td>${reporte.hora_entrada}</td>
                                    <td>${reporte.hora_salida}</td>
                                    <td>${subTotal}</td>
                                    <td>${factor}</td>
                                    <td class="text-success fw-bold">${reporte.total_decimal}</td>
                                 </tr>`;
                    });
                    let totalHoras = response.totalHoras;
                    totalHoras = totalHoras.toFixed(2);
                    html += `
                                <tr style="font-weight: bold; font-size: 20px;">
                                    <td class="text-success text-end" colspan="7">Total</td>
                                    <td class="text-success">${totalHoras}</td>
                                </tr>
                        </tbody>
                    </table>`;
                    
                    $('#RepTabl').html(html); // Imprimir reporte en HTML
                    $('[data-bs-toggle="tooltip"]').tooltip(); // Inicializa los tooltips
                    $('#RepDet .Sueldo').text(formatter.format(response.sueldo));
                    $('#RepDet .Tasa_Hora').text(formatter.format(response.tasaHora));
                    $('#RepDet .Pago_Total').text(formatter.format(response.pagoTotal));
                    $('#RepDet .Porciento_Sueldo').html('<span class="badge rounded-pill bg-success-subtle text-success-emphasis">' + response.porcentajeDelSueldo.toFixed(2) + '% del sueldo</span>');
                    resolve();  // Resuelve la promesa si todo sale bien
                    // console.log('Reportes:', reportes);
                } else {
                    reject('Error en la generación del reporte.');  // Rechaza la promesa si hay un error
                    $('#infoModal .modal-title').text("Error");
                    $('#infoModal .modal-body').text(response.message);
                    $('#infoModal').modal('show');
                }
            },
            error: function(xhr, status, error) {
                $('#infoModal .modal-title').text("Error");
                $('#infoModal .modal-body').text('Error al recuperar la información:' + error);
                $('#infoModal').modal('show');
                reject('Error en la generación del reporte.');  // Rechaza la promesa si hay un error
            }
        });
    });
}


function actualizarEstado() {
    return new Promise((resolve, reject) => {
        $.ajax({
            url: '../admin/api/crear_reporte.php',
            type: 'POST',
            data: { accion: 'actualizar_estado' },
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    console.log('Estados actualizados correctamente.');
                    // Aquí podrías redirigir al usuario o recargar la interfaz para reflejar los cambios
                    resolve();  // Resuelve la promesa si todo sale bien
                } else {
                    let fechasProblemaStr = response.fechas.join(", ");
                    alert("Algunos reportes nuevos tienen descripciones vacias o muy cortas: " + fechasProblemaStr + ".");
                    reject('Error en la actualización de estados.');  // Rechaza la promesa si hay un error
                }
            },
            error: function(xhr, status, error) {
                console.error('Error al actualizar estados:', error);
                alert('Error al actualizar los estados de los reportes: ' + error);
                reject('Error en la actualización de estados.');  // Rechaza la promesa si hay un error
            }
        });
    });
}

$(document).ready(function() {

    $('#srchCard').hide(); // Oculta el div con el modelo de carga de usuario
    $('#srchCardEmpty').show(); // Muestra el div con el carrusel de tips

    let fechasYaValidadas = []; // Almacena las fechas que ya han sido validadas
    // Función para cargar los datos de los departamentos
    cargarDepartamentos();
    cargarGerencias();

    //Inicializar los PopOvers
    const tooltipTriggerList = document.querySelectorAll('[data-bs-toggle="tooltip"]');
    const tooltipList = [...tooltipTriggerList].map(tooltipTriggerEl => new bootstrap.Tooltip(tooltipTriggerEl));
    
    //Cerrar modal
    $('.cerrarModal').click(function() {
        cerrarModal();    
    });

    // Navegación entre secciones
    let pasoActual = 1;
    let diaActivo = 1;

    calendario.config.onChange.push(function(selectedDates, dateStr, instance) {
        // Convierte el estado actual de fechas seleccionadas a formato YYYY-MM-DD
        let estadoActualFechas = selectedDates.map(fecha => formatDateToYYYYMMDD(fecha));

        // Determina las fechas agregadas o eliminadas comparando los estados
        let fechasAgregadas = estadoActualFechas.filter(fecha => 
            !fechasYaValidadas.includes(fecha) || !fechasValidas.map(fecha => formatDateToYYYYMMDD(fecha)).includes(fecha)
        );

        let fechasEliminadas = fechasYaValidadas.filter(fecha => !estadoActualFechas.includes(fecha));

        // Actualiza fechasValidas basado en fechas eliminadas
        fechasValidas = fechasValidas.filter(fecha => !fechasEliminadas.includes(formatDateToYYYYMMDD(fecha)));
        
        if (fechasEliminadas.length > 0 && fechasValidas.length > 1) {
            actualizarDropdownFechas();
        }
        

        // Maneja la selección de nuevas fechas
        fechasAgregadas.forEach(fechaFormateada => {
            verificarPonchadoYExcepciones(fechaFormateada, 'codigoEmpleado', function(esValido, mensaje) {
                if (!esValido) {
                    alert(mensaje);
                    // Elimina la fecha inválida
                    fechasValidas = fechasValidas.filter(fecha => formatDateToYYYYMMDD(fecha) !== fechaFormateada);
                    actualizarDropdownFechas();
                } else {
                    // Agrega la fecha válida si no está ya incluida
                    let fecha = selectedDates.find(fecha => formatDateToYYYYMMDD(fecha) === fechaFormateada);
                    if (fecha && !fechasValidas.map(fecha => formatDateToYYYYMMDD(fecha)).includes(fechaFormateada)) {
                        fechasValidas.push(fecha);
                        actualizarDropdownFechas();
                        handleDateSelection(formatDateToYYYYMMDD(fecha));
                    }
                }
                // Actualiza el calendario con solo las fechas válidas para asegurar consistencia
                instance.setDate(fechasValidas, false);
            });
        });

        // Actualiza el estado anterior con el estado actual para el próximo cambio
        fechasYaValidadas = estadoActualFechas.slice();
    });

    // Procesar reporte de día
    $('#HoraEntrada, #HoraSalida').blur(function() {
        let inputId = $(this).attr('id');
        let fecha = formatDateToYYYYMMDD(fechasValidas[diaActivo - 1]);
        let descripcion = $('#detDia').val();
        let horaEntrada = $('#HoraEntrada').val();
        let horaSalida = $('#HoraSalida').val();

        // Verificar que ambos campos están llenos
        if (horaEntrada !== "" && horaSalida !== "") {
            // Crear objetos de fecha y tiempo para comparación
            let entradaDateTime = new Date('1970-01-01T' + horaEntrada + 'Z');
            let salidaDateTime = new Date('1970-01-01T' + horaSalida + 'Z');

            // Verificar si la hora de salida es mayor que la hora de entrada
            if (salidaDateTime <= entradaDateTime) {
                alert("La hora de salida debe ser mayor que la hora de entrada. Verifique y vuelva a intentar.");
                $(`#${inputId}`).val('');
                return; // Salir de la función si la condición no se cumple
            }
        }

        if ($(this).val() != ""){
            // Primero verificar el ponchado
            $.ajax({
                url: '../admin/api/gestionar_dia.php',
                type: 'POST',
                dataType: 'json',
                data: {
                    accion: 'crear_reporte',
                    horaEntrada: horaEntrada,
                    horaSalida: horaSalida,
                    fecha: fecha,
                    descripcion: descripcion
                },
                success: function(response) {
                    if (response.valido) {
                        // Ahora verificar el horario
                        if (response.tiempo) {
                            if (response.tiempo['bonoDiaLibre']) {
                                $('#subTotalHRs .valor').text(response.tiempo['SubTotal_Decimal']);
                                $('#subTotalHRs').show();
                                $('#bonoDiaLibre .valor').text(response.tiempo['bonoDiaLibre']);
                                $('#bonoDiaLibre').show();
                                $('#totalHRs .valor').html('<span class="badge rounded-pill bg-info mx-2 tinyTxt">Hrs</span>' + response.tiempo['Total_Decimal']);
                            }else{
                                $('#subTotalHRs .valor').text("0.00");
                                $('#subTotalHRs').hide();
                                $('#bonoDiaLibre .valor').text("0.00");
                                $('#bonoDiaLibre').hide();
                                $('#totalHRs .valor').html('<span class="badge rounded-pill bg-info mx-2 tinyTxt">Hrs</span>' + response.tiempo['Total_Decimal']);
                            }
                        }
                    } else {
                        alert(response.mensaje);
                        $(`#${inputId}`).val(''); // Limpiar input si el ponchado no es válido
                    }
                },
                error: function(xhr, status, error) {
                    console.error('Error:', error);
                }
            });
        }else{
            $(`#${inputId}`).val('');
        }
    });

    $('#detDia').blur(function() {
        let descripcion = $(this).val();
        let fecha = formatDateToYYYYMMDD(fechasValidas[diaActivo - 1])
        if (descripcion != "") {
            actualizarDescripcion(fecha, descripcion);
        }
    });

    $("#StartUp").click(function() {

        if (!empleadoSeleccionadoValido) {
            $('#infoModal .modal-title').text("Ups!");
            $('#infoModal .modal-body').text('Por favor, selecciona un empleado válido antes de continuar.');
            $('#infoModal').modal('show');
            e.preventDefault(); // Prevenir que se continúe sin una selección válida
            return false; // Detener la ejecución adicional del handler
        }

        $('#btnSiguiente, #btnAnterior').hide();
        // Ocultar la sección actual
        const seccionActual = $(`.SeccionRep:eq(${pasoActual - 1})`);
        seccionActual.addClass('animate__bounceOut animate__faster').on('animationend', () => {
            seccionActual.removeClass('animate__bounceOut animate__faster');
            seccionActual.hide();
            seccionActual.off('animationend');

            // Incrementar pasoActual y asegurar que no exceda el total de pasos
            pasoActual ++;

            // Mostrar la siguiente sección
            const siguienteSeccion = $(`.SeccionRep:eq(${pasoActual - 1})`);
            
            $('.Emp-Pill, .Emp-Hr').addClass('invisible');
            siguienteSeccion.show();
            siguienteSeccion.addClass('animate__bounceIn animate__faster').on('animationend', () => {
                siguienteSeccion.removeClass('animate__bounceIn animate__faster');
                siguienteSeccion.off('animationend');
                $('#btnSiguiente, #btnAnterior').show();
                $('#btnSiguiente, #btnAnterior').addClass('animate__bounceIn animate__faster').on('animationend', () => {
                    //  Mostrar botones Siguiente y Anterior
                    $('#btnSiguiente, #btnAnterior').removeClass('animate__bounceIn animate__faster');
                    $('#btnSiguiente, #btnAnterior').off('animationend');
                    //  Mostrar Pill con info básica empleado
                    $('.Emp-Pill, .Emp-Hr').removeClass('invisible').addClass('visible');
                    $(`.Emp-Pill:eq(${0})`).addClass('animate__flipInX').on('animationend', () => {
                        $('.Emp-Pill').removeClass('animate__flipInX');
                    });
                    $(`.Emp-Hr:eq(${0})`).addClass('animate__flipInX').on('animationend', () => {
                        $('.Emp-Hr').removeClass('animate__flipInX');
                    });
                });
            });
        });
    });

    $('#btnSiguiente, #SendRep').click(function() {
        
        if (!empleadoSeleccionadoValido) {
            alert("Por favor, selecciona un empleado válido antes de continuar.");
            e.preventDefault(); // Prevenir que se continúe sin una selección válida
            return false; // Detener la ejecución adicional del handler
        }

        $('#btnSiguiente').addClass('animate__headShake animate__faster').on('animationend', () => {
            $('#btnSiguiente').removeClass('animate__headShake animate__faster');
        });

        // Recolectar fechas seleccionadas e imprimirlas en el próximo paso
        if (pasoActual === 2) {
            // Obtener las fechas seleccionadas
            let fechasSeleccionadas = calendario.selectedDates.map(date => date.toISOString().substring(0, 10));
            if (fechasSeleccionadas.length === 0) {
                // Mostrar mensaje de error si no se encuentra el usuario
                $('#infoModal .modal-title').text("Oops!");
                $('#infoModal .modal-body').text("Debes seleccionar al menos una fecha...");
                $('#infoModal').modal('show');
                return; // Detener la ejecución si no hay fechas seleccionadas
            }

            generarCamposHoras();
            //  Mostrar primer día
            $('.dayRow').show();
            diaActivo = 1;
            totalDias = fechasValidas.length;
        }

        if (pasoActual === 3) {
            generarReporteDePago().then(() => {
                // Solo avanza al paso 5 si la actualización fue exitosa
                avanzarAlSiguientePaso();
            }).catch(error => {
                console.log(error);  // Manejar el error o detener la transición al paso 5
            });
            return;  // Detener la ejecución aquí para esperar la promesa de actualizarEstado
        }

        if (pasoActual === 4) {
            actualizarEstado().then(() => {
                // Solo avanza al paso 5 si la actualización fue exitosa
                avanzarAlSiguientePaso();
            }).catch(error => {
                console.log(error);  // Manejar el error o detener la transición al paso 5
            });
            return;  // Detener la ejecución aquí para esperar la promesa de actualizarEstado
        }

        // Si no es el paso 4, avanza al siguiente paso normalmente
        avanzarAlSiguientePaso();
    });

    function avanzarAlSiguientePaso() {
        if (pasoActual < 5) {
            const seccionActual = $(`.SeccionRep:eq(${pasoActual - 1})`);
            seccionActual.hide();

            // Incrementar pasoActual y asegurar que no exceda el total de pasos
            pasoActual++;

            // Mostrar la siguiente sección
            const siguienteSeccion = $(`.SeccionRep:eq(${pasoActual - 1})`);
            siguienteSeccion.show();
            siguienteSeccion.addClass('animate__zoomIn animate__faster').on('animationend', () => {
                siguienteSeccion.removeClass('animate__zoomIn animate__faster');
                siguienteSeccion.off('animationend');
            });
        }
    }

    $('#btnAnterior').click(function() {
        $('#btnAnterior').addClass('animate__headShake animate__faster').on('animationend', () => {
            $('#btnAnterior').removeClass('animate__headShake animate__faster');
        });
        if (pasoActual > 1) {
            const seccionActual = $(`.SeccionRep:eq(${pasoActual - 1})`);
            seccionActual.hide();

            // Incrementar pasoActual y asegurar que no exceda el total de pasos
            pasoActual --;

            // Mostrar la siguiente sección
            const siguienteSeccion = $(`.SeccionRep:eq(${pasoActual - 1})`);
            siguienteSeccion.show();
            siguienteSeccion.addClass('animate__zoomIn animate__faster').on('animationend', () => {
                siguienteSeccion.removeClass('animate__zoomIn animate__faster');
                siguienteSeccion.off('animationend');
            });
        }
    });

    // Evento para manejar el clic en una fecha del dropdown
    $('#listaFechasDropdown').on('click', 'a.dropdown-item', function(e) {
        e.preventDefault();
        let indiceSeleccionado = $(this).parent().index();
        let fechaSeleccionada = fechasValidas[indiceSeleccionado].toISOString().substring(0, 10);

        // Enviar solicitud para actualizar el día activo en sesión
        $.ajax({
            url: '../admin/api/actFechasSelecc.php',
            type: 'POST',
            dataType: 'json',
            data: {
                diaActivo: indiceSeleccionado + 1  // +1 porque los índices son base 0 y los días base 1
            },
            success: function(response) {
                if (response.success) {
                    $('#dropdownMenuButton').text(formatearFecha(fechasValidas[response.DiaActivo - 1]));
                    // Actualizar la UI para mostrar el día seleccionado
                    let animation = 'animate__fadeInUp';
                    $('.dayRow').hide();
                    $('.dayRow').show();
                    $('#DetalleHoras').addClass(animation + ' animate__faster').on('animationend', () => {
                        $('#DetalleHoras').removeClass('animate__fadeInUp animate__faster');
                        $('#DetalleHoras').off('animationend');
                    });

                    diaActivo = indiceSeleccionado + 1;

                    // Luego de actualizar el día activo, obtener los datos del reporte para ese día
                    $.ajax({
                        url: '../admin/api/gestionar_dia.php',
                        type: 'POST',
                        dataType: 'json',
                        data: {
                            accion: 'obtener_ponchado',
                            fecha: fechaSeleccionada
                        },
                        success: function(response) {
                            // Aquí es donde manipulas el valor del objeto response
                            if (response.valido) {
                                let entrada = new Date('1970-01-01T' + response.mensaje['entrada']).toLocaleTimeString('en-US', {
                                    hour: '2-digit',
                                    minute: '2-digit',
                                    hour12: true
                                });

                                let salida = new Date('1970-01-01T' + response.mensaje['salida']).toLocaleTimeString('en-US', {
                                    hour: '2-digit',
                                    minute: '2-digit',
                                    hour12: true
                                });
                                
                                // Si el ponchado es válido
                                $('#ponchadoDiaSelecc').html(`<i class="fa-solid fa-fingerprint mx-1"></i> ${entrada} - ${salida}`);
                                if (response.mensaje['excepcion'] != "--") {
                                    $('#ponchadoDiaSelecc').tooltip('dispose').attr("data-bs-title",response.mensaje['excepcion']).tooltip(); // Actualizar tooltip
                                }

                                // Luego de actualizar el día activo, obtener los datos del reporte para ese día
                                $.ajax({
                                    url: '../admin/api/gestionar_dia.php',
                                    type: 'POST',
                                    dataType: 'json',
                                    data: {
                                        accion: 'obtener_reporte',
                                        fecha: fechaSeleccionada
                                    },
                                    success: function(reportResponse) {
                                        if (reportResponse.success) {
                                            // Actualizar los inputs con los datos del reporte
                                            if (reportResponse.data.hora_entrada !== null) {
                                                $('#HoraEntrada').val(reportResponse.data.hora_entrada);
                                            } else {
                                                $('#HoraEntrada').val(''); // Limpiar si es null
                                            }
                                            if (reportResponse.data.hora_salida !== null) {
                                                $('#HoraSalida').val(reportResponse.data.hora_salida);
                                            } else {
                                                $('#HoraSalida').val(''); // Limpiar si es null
                                            }
                                            if (reportResponse.data.descripcion !== null) {
                                                $('#detDia').val(reportResponse.data.descripcion);
                                            } else {
                                                $('#detDia').val(''); // Limpiar si es null
                                            }

                                            // Verificar horario y días libres
                                            $.ajax({
                                                url: '../admin/api/gestionar_dia.php',
                                                type: 'POST',
                                                dataType: 'json',
                                                data: {
                                                    accion: 'obtener_horario',
                                                    fecha: fechaSeleccionada
                                                },
                                                success: function(horario) {
                                                    if (!horario.diaLaboral) {
                                                        $('#tipoDia').text('Libre');
                                                        $('#tipoDia').removeClass('text-primary').addClass('text-success');
                                                        if (reportResponse.data.bono_dia_libre !== "0.00") {
                                                            $('#subTotalHRs .valor').text((parseFloat(reportResponse.data.total_decimal - reportResponse.data.bono_dia_libre).toFixed(2)));
                                                        }else{
                                                            $('#subTotalHRs .valor').text("0.00");
                                                        }
                                                        $('#subTotalHRs').show();
                                                        $('#bonoDiaLibre .valor').text(parseFloat(reportResponse.data.bono_dia_libre).toFixed(2));
                                                        $('#bonoDiaLibre').show();
                                                        $('#totalHRs .valor').html('<span class="badge rounded-pill bg-info mx-2 tinyTxt">Hrs</span>' + parseFloat(reportResponse.data.total_decimal).toFixed(2)); 
                                                    }else{
                                                        $('#tipoDia').text('Regular');
                                                        $('#tipoDia').removeClass('text-success').addClass('text-primary');
                                                        $('#subTotalHRs .valor').text("0.00");
                                                        $('#subTotalHRs').hide();
                                                        $('#bonoDiaLibre .valor').text("0.00");
                                                        $('#bonoDiaLibre').hide();
                                                        $('#totalHRs .valor').html('<span class="badge rounded-pill bg-info mx-2 tinyTxt">Hrs</span>' + parseFloat(reportResponse.data.total_decimal).toFixed(2));
                                                    }
                                                },
                                                error: function(xhr, status, error) {
                                                    console.error('Error al obtener datos del horario:', error);
                                                }
                                            });
                                        } else {
                                            console.error("Error al obtener datos del reporte:", reportResponse.message);
                                            $('#HoraEntrada').val('');
                                            $('#HoraSalida').val('');
                                            $('#detDia').val('');
                                        }
                                    },
                                    error: function(xhr, status, error) {
                                        console.error('Error al obtener datos del reporte:', error);
                                        $('#HoraEntrada').val('');
                                        $('#HoraSalida').val('');
                                        $('#detDia').val('');
                                    }
                                });
                            } else {
                                // Si el ponchado no es válido
                                // Usa el mensaje de la respuesta para dar más detalle
                                let mensaje = response.mensaje || "La fecha no es válida.";
                                console.error(mensaje);
                            }
                        },
                        error: function(xhr, status, error) {
                            console.error('Error al conectar con el servidor: ' + error);
                            alert('Error de conexión con el servidor');
                        }
                    });

                } else {
                    console.error("Error al actualizar el día activo:", response.message);
                }
            },
            error: function(xhr, status, error) {
                console.error('Error al conectar con el servidor:', error);
            }
        });
    });

    $('#diaSiguiente').click(function() {
        if (diaActivo < fechasValidas.length) {
            // Seleccionar siguiente opción del dropdown
            var nextOpt = $('#listaFechasDropdown li:eq('+diaActivo+') a.dropdown-item');
            nextOpt.trigger('click'); // Simula un clic en la segunda opción
        }
    });

    $('#diaAnterior').click(function() {
        if (diaActivo > 1) {
            diaActivo = diaActivo -2;
            // Seleccionar opción anterior del dropdown
            var prevOpt = $('#listaFechasDropdown li:eq('+diaActivo+') a.dropdown-item');
            prevOpt.trigger('click'); // Simula un clic en la segunda opción
        }
    });

    // Botón para volver a inicio desde generación de reportes
    $('#ExitHE').click(function() {
        window.location.href = 'https://infotep.info/Horas_Extras/';
    });
});
