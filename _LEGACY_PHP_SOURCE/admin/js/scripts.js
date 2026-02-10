/*!
* Start Bootstrap - Heroic Features v5.0.6 (https://startbootstrap.com/template/heroic-features)
* Copyright 2013-2023 Start Bootstrap
* Licensed under MIT (https://github.com/StartBootstrap/startbootstrap-heroic-features/blob/master/LICENSE)
*/
// This file is intentionally blank
// Use this file to add JavaScript to your project

//Función para resetear el formulario

function cerrarModal() {
    $('#infoModal').modal('hide');
}

function resetForm() {
    $('#userForm')[0].reset();
    $('#codigo, #nombre, #cedula').empty();
    $('#departamento').attr('placeholder', "");
    $('#preferencia_firma').val("");
}

function resetDepForm() {
    $('#DepForm')[0].reset();
}

//Función crear selectores dinámicos para los campos Código, Nombre y Cédula
function initSelect2() {
    $('.search-select').each(function() {
        var campo = this.id; // 'codigo', 'nombre' o 'cedula'
        $(this).select2({
            minimumInputLength: 2,
            ajax: {
                url: $('#UserAdmMode').is(':checked') ? 'api/buscar_usuario.php' : 'api/buscar_nomina.php',
                dataType: 'json',
                delay: 250,
                data: function (params) {
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
                        results: $.map(data, function (item) {
                            var text = item[campo];
                            return {
                                id: item.codigo,
                                text: text,
                                item: item
                            };
                        })
                    };
                }
            }
        }).on('select2:select', function (e) {

            resetForm(); // Limpiar campos del formulario antes de rellenarlos

            var data = e.params.data.item;
            if ($('#UserAdmMode').is(':checked')) {
                // Modo Modificar Usuario
                $.ajax({
                    url: 'api/buscar_usuario.php',
                    dataType: 'json',
                    data: {
                        termino: data.codigo,
                        campo: 'codigo'
                    },
                    success: function(respuesta) {
                        if (respuesta.usuario_en_sistema) {
                            rellenarFormularioModificacion(respuesta);
                            $('#eliminarUsuario').show();
                        } else {
                            // Mostrar mensaje de error si no se encuentra el usuario
                            $('#infoModal .modal-title').text("Ups!");
                            $('#infoModal .modal-body').text('El usuario no se encuentra en el sistema. Por favor, crea el usuario primero.');
                            $('#infoModal').modal('show');
                            return { results: [] };
                        }
                    },
                    error: function() {
                        // Mostrar mensaje de error
                        $('#infoModal .modal-title').text("Error");
                        $('#infoModal .modal-body').text(respuesta.error);
                        $('#infoModal').modal('show');
                        return { results: [] };
                    }
                });
            }

            // Rellenar campos básicos del empleado en la nómina
            rellenarCampos(data);
        });
    });
}

//Función para llenar automáticamente la coincidencia seleccioanda de la base de datos de la nómina
function rellenarCampos(data) {
    // Actualizar el campo Código
    $('#codigo').empty().append(new Option(data.codigo, data.codigo, true, true)).trigger('change');

    // Actualizar el campo Nombre
    $('#nombre').empty().append(new Option(data.nombre, data.nombre, true, true)).trigger('change');

    // Actualizar el campo Cédula
    $('#cedula').empty().append(new Option(data.cedula, data.cedula, true, true)).trigger('change');

    // Actualizar los otros campos de texto
    $('#cargo').val(data.cargo);
    $('#departamento').attr('placeholder', data.departamento);
    $('#sueldo').val(data.salario_mensual);
}

function formatHora(hora) {
    if (hora) {
        var partes = hora.split(':');
        if (partes.length >= 2) {
            var horas = partes[0].length === 1 ? '0' + partes[0] : partes[0];
            var minutos = partes[1].length === 1 ? '0' + partes[1] : partes[1];
            return horas + ':' + minutos;  // Solo retorna horas y minutos
        }
    }
    return hora;
}

function rellenarFormularioModificacion(data) {

    // Actualizar el campo Código
    $('#codigo').empty().append(new Option(data.codigo, data.codigo, true, true)).trigger('change');

    // Actualizar el campo Nombre
    $('#nombre').empty().append(new Option(data.nombre, data.nombre, true, true)).trigger('change');

    // Actualizar el campo Cédula
    $('#cedula').empty().append(new Option(data.cedula, data.cedula, true, true)).trigger('change');

    $('#cargo').val(data.cargo);
    $('#sueldo').val(data.salario_mensual);

    if (data.usuario_en_sistema) {
        // Rellenar el resto de los campos si el usuario está en el sistema
        $('#gerencia').val(data.gerencia).trigger('change');
        $('#departamento').val(data.departamento).trigger('change');
        $('#nivelCorporativo').val(data.nivelCorporativo).trigger('change');
        $('#correo').val(data.correo);
        $('#rol').val(data.rol).trigger('change');
        // Configurar horario y tipo de horario
        $('#switchHorario').prop('checked', data.tipoHorario === 'rotativo');
        $.each(data.horario, function(dia, hora) {
            $('#' + dia).val(formatHora(hora));
        });

        // Iterar sobre cada checkbox de apps
        $('#apps .form-check-input[type=checkbox]').each(function() {

            // Verificar si el id del checkbox está en el array de apps
            $(this).prop('checked', data.apps && data.apps.includes(this.id));
        });

        $('#preferencia_firma').val(data.preferenciaFirma);
    } else {
        // Mostrar modal de advertencia si el usuario no está en el sistema
        $('#infoModal .modal-title').text("Usuario no encontrado");
        $('#infoModal .modal-body').text("El usuario no se encuentra en el sistema. Por favor, crea el usuario primero.");
        $('#infoModal').modal('show');
    }
}


$(document).ready(function() {

    //Cerrar modal
    $('.cerrarModal').click(function() {
        cerrarModal();    
    });
    var url = window.location.pathname;
    if (url.includes('/crear_usuario.php')) {

         //::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::\\
        //----[CÓDIGO JAVASCRIPT PARA LA PÁGINA CREAR_NUEVO_USUARIO.PHP]----\\

        //Carga de lista de correos que coinciden con el nombre ingresado
        
        //Carga de Gerencias
        $.getJSON('api/get_gerencias.php', function(data) {
            $.each(data, function(key, entry) {
                $('#gerencia').append($('<option></option>').attr('value', entry.id_gerencia).text(entry.nombre));
            });
        });

        //Carga de Departamentos
        var listaDepartamentos = []; // Variable para almacenar todos los departamentos
        $.getJSON('api/get_departamentos.php', function(data) {
            listaDepartamentos = data;
            // $.each(data, function(key, entry) {
            //     $('#departamento').append($('<option></option>').attr('value', entry.id_departamento).text(entry.nombre));
            // });
        });

        //Actualizar departamentos en modo crear cuándo se seleccione una gerencia
        $('#gerencia').change(function() {
            var gerenciaSeleccionada = $(this).val(); // Obtener el valor de la gerencia seleccionada
            actualizarDepartamentos(gerenciaSeleccionada);
        });

        function actualizarDepartamentos(gerenciaId) {
            $('#departamento').empty(); // Vaciar el selector de departamentos
            $('#departamento').append($('<option></option>').attr('value', '').text('Seleccionar')); // Opción por defecto

            // Filtrar y añadir solo los departamentos que coincidan con la gerencia seleccionada
            listaDepartamentos.forEach(function(departamento) {
                if(departamento.gerencia_id === gerenciaId) {
                    $('#departamento').append($('<option></option>').attr('value', departamento.id_departamento).text(departamento.nombre));
                }
            });
        }

        //Carga de roles
        $.getJSON('api/get_roles.php', function(data) {
            $.each(data, function(key, entry) {
                $('#rol').append($('<option></option>').attr('value', entry.id_rol).text(entry.nombre_rol));
            });
        });

        // Carga de aplicaciones
        $.getJSON('api/get_aplicaciones.php', function(data) {
            var appsDiv = $('#apps');
            $.each(data, function(key, app) {
                var appDiv = $('<div class="form-check">').appendTo(appsDiv);
                $('<input class="form-check-input" type="checkbox">')
                    .attr('value', app.id_app)
                    .attr('id', app.nombre_app + '_app')
                    .appendTo(appDiv);
                $('<label class="form-check-label">')
                    .attr('for', app.nombre_app + '_app')
                    .text(app.nombre_app)
                    .appendTo(appDiv);
            });
        });

        var contadorPermiso = 1; // Inicializar el contador de permisos
        initSelect2(); // Inicializar Select2

        // Asegúrate de que los cambios en Select2 se reflejen en el select original
        $('.search-select').on('select2:select', function() {
            $(this).trigger('change');
        });

        // Reestablecer formulario crear usuario incluyendo los campos con Select2
        $('#clearForm').click(function() {
            resetForm();
        });

        //Botón eliminar usuario
        $('#eliminarUsuario').click(function() {
            var codigoUsuario = $('#codigo').val(); // Asumiendo que '#codigo' es el campo con el código del usuario

            if (confirm("¿Estás seguro de que deseas eliminar este usuario?")) {
                $.ajax({
                    type: 'POST',
                    url: 'api/eliminar_usuario.php', // Asegúrate de usar la ruta correcta
                    data: { codigo: codigoUsuario },
                    dataType: 'json',
                    success: function(response) {
                        if (response.success) {
                            // Muestra el modal con el mensaje
                            $('#infoModal .modal-title').text("Ready!");
                            $('#infoModal .modal-body').text("Usuario eliminado exitosamente");
                            $('#infoModal').modal('show');
                            resetForm();
                            // Aquí puedes añadir lógica adicional, como redirigir o actualizar la página
                        } else if (response.error) {
                            // Muestra el modal con el mensaje de error
                            $('#infoModal .modal-title').text("Nope!");
                            $('#infoModal .modal-body').text("Error: " + response.error);
                            $('#infoModal').modal('show');
                            resetForm();
                        } else {
                            alert("Respuesta inesperada del servidor");
                        }
                    },
                    error: function(jqXHR, textStatus, errorThrown) {
                        console.error('Error AJAX:', textStatus, errorThrown); // Registro de depuración
                        alert('Error al procesar la solicitud de eliminación');
                    }
                });
            }
        });


        //Completar horarios en función del horario del lunes
        $('#completarHr').click(function() {
            // Obtener el valor del input de lunes
            var horaLunes = $('#lunes').val();

            // Establecer ese valor en todos los inputs de tipo 'time'
            $('.input-hora-dia').val(horaLunes);
        });

        //Enviar formulario
        $('#crearUsuario').click(function() {
            var form = $('#userForm')[0];  // Obtener el elemento del formulario nativo
            if (form.checkValidity()) {

                // Verificar si todos los campos de hora están llenos
                var Dlibres = 0;
                $('.input-hora-dia').each(function() {
                    if ($(this).val() === '') {
                        Dlibres = Dlibres + 1
                    }
                });

                if (Dlibres > 2 || Dlibres <= 1) {

                    var texto = "";
                    if (Dlibres < 1) {
                        texto = "El horario determinado no tiene días libres. Revise las informaciones e intente nuevamente.";
                    }else if (Dlibres = 1) {
                        texto = "El horario determinado sólo tiene 1 día libre. Revise las informaciones e intente nuevamente.";
                    }else if (Dlibres > 2) {
                        texto = "El horario determinado tiene más de 2 días libres. Revise las informaciones e intente nuevamente.";
                    }
                    // Muestra el modal con el mensaje de error
                    $('#infoModal .modal-title').text("Nope!");
                    $('#infoModal .modal-body').text(texto);
                    $('#infoModal').modal('show');
                    return; // Detiene la ejecución adicional del evento submit
                }
                // Verificar si al menos una aplicación está seleccionada
                else if ($('#apps .form-check-input:checked').length === 0) { 
                    // Muestra el modal con el mensaje de error
                    $('#infoModal .modal-title').text("Ups!");
                    $('#infoModal .modal-body').text("Por favor, selecciona al menos una aplicación.");
                    $('#infoModal').modal('show');
                    return; // Detiene la ejecución adicional del evento submit
                }else{
                    $('#userForm').submit();
                }
            } else {
                form.reportValidity();  // Mostrar los mensajes de error de validación
            }
        });

        $('#userForm').on('submit', function(e) {
            e.preventDefault(); // Evita que el formulario se envíe de la manera tradicional

            // Obtén los valores necesarios
            var rolSeleccionado = $('#rol').val();
            var cargo = $('#cargo').val().toUpperCase();
            var accesoHorasExtras = $('#Horas_Extras_app').is(':checked'); // Asume que este es el ID del checkbox de Horas Extras

            // Lista de cargos permitidos
            var cargosPermitidos = [
                'CHOFER', 'CONSERJE', 'MENSAJERO INTERNO', 'MENSAJERO EXTERNO', 'OFICIAL DE SEGURIDAD',
                'PINTOR', 'PLOMERO', 'CAMARERO', 'JARDINERO', 'LAVADOR DE VEHÍCULO',
                'AUXILIAR DE ALMACÉN', 'TÉCNICO DE MANTENIMIENTO', 'ANALISTA DE CCTV',
                'AUXILIAR DE SERVICIOS GENERALES', 'AUXILIAR DE TRANSPORTACIÓN',
                'AUXILIAR DE EVENTOS', 'ASISTENTE DE TRANSPORTACIÓN'
            ];

            // Verifica si el rol es Colaborador, tiene acceso a Horas Extras y el cargo no está en la lista
            if (rolSeleccionado === '4' && accesoHorasExtras && !cargosPermitidos.includes(cargo)) {
                // Muestra el modal con el mensaje de error
                $('#infoModal .modal-title').text("Ups!");
                $('#infoModal .modal-body').text("La creación de horas extras está permitida únicamente para el personal de apoyo que pertenezca al 5to nivel corporativo.");
                $('#infoModal').modal('show');
                return; // Detiene la ejecución adicional del evento submit
            }

            var formData = new FormData(this); // Crea un objeto FormData con los datos del formulario

            // Establecer el valor del tipo de horario basado en el estado del switch
            var tipoHorario = $('#switchHorario').is(':checked') ? 'rotativo' : 'fijo';
            formData.append('tipoHorario', tipoHorario);

            // Obtener el valor de la preferencia de firma y añadirlo al formData
            var preferenciaFirma = $('#preferencia_firma').val();
            formData.append('preferencia_firma', preferenciaFirma);

            // Obtiene todas las aplicaciones seleccionadas y las añade a formData
            $('#apps .form-check-input:checked').each(function() {
                formData.append('apps[]', $(this).val());
            });

            // Determinar la URL del endpoint según el modo
            var endpointUrl = $('#UserAdmMode').is(':checked') 
                              ? '../admin/api/Modf_Usuario.php'  // URL para modificar usuario
                              : '../admin/api/crear_nuevo_usuario.php'; // URL para crear usuario
            $.ajax({
                type: 'POST',
                url: endpointUrl,
                data: formData,
                processData: false,  // No procesar los datos
                contentType: false,  // No establecer un tipo de contenido
                dataType: 'json',
                success: function(response) {
                    if(response.success) {
                        // Muestra el modal con el mensaje de error
                        $('#infoModal .modal-title ').text("Listo!");
                        $('#infoModal .modal-body').text(response.success);
                        $('#infoModal').modal('show');
                        resetForm();
                    } else if(response.error) {
                        // Muestra el modal con el mensaje de error
                        $('#infoModal .modal-title ').text("Hmm...");
                        $('#infoModal .modal-body').text(response.error);
                        $('#infoModal').modal('show');
                    }
                },
                error: function() {
                    alert('Error al procesar el formulario');
                }
            });
        });

        // Cambio entre Crear y Modificar Usuario
        $('#UserAdmMode').change(function() {
            var enlacesUsuarios = $('#collapseTwo .collapse-item');
            resetForm(); // Limpiar campos del formulario antes de rellenarlos
            if ($(this).is(':checked')) {
                // Modo Modificar Usuario
                $('#contrasena').removeAttr('required');
                $('#userForm input[readonly]').addClass('bg-gray-500');
                $('#userForm input[readonly], #userForm input[disabled]').addClass('bg-gray-500');
                $('#FormTittle').text('Modificar Usuario');
                $('#Crear_Modificar_Form').addClass('bg-gradient-light');
                enlacesUsuarios.removeClass('active');
                enlacesUsuarios.eq(1).addClass('active');
                $('#crearUsuario').html('<i class="fa-solid fa-pen-to-square mr-2" aria-hidden="true"></i>Modificar');
                // Agregar más lógica para el modo Modificar si es necesario
            } else {
                // Modo Crear Usuario
                $('#contrasena').attr('required');
                $('#userForm input[readonly]').removeClass('bg-gray-500');
                $('#FormTittle').text('Crear Nuevo Usuario');
                $('#Crear_Modificar_Form').removeClass('bg-gradient-light');
                enlacesUsuarios.removeClass('active');
                enlacesUsuarios.eq(0).addClass('active');
                $('#crearUsuario').html('<i class="fa-solid fa-circle-plus mr-2"></i>Crear');
                $('#eliminarUsuario').hide();
                // Revertir cambios para el modo Modificar si es necesario
            }
        });

        //Función para personalizar mensajes de error en campos requeridos del formulario
        function getCustomMessage(elementId) {
            const messages = {
                'codigo': 'Por favor, ingresa el código del usuario.',
                'nombre': 'Por favor, ingresa el nombre del usuario.',
                'cedula': 'Por favor, ingresa la cédula del usuario.',
                'cargo': 'Por favor, ingresa el cargo del usuario.',
                'departamento': 'Por favor, ingresa el departamento del usuario.',
                'gerencia': 'Por favor, selecciona la gerencia a la que pertenece este usuario.',
                'sueldo': 'Por favor, ingresa el sueldo del usuario.',
                'nivelCorporativo': 'Por favor, selecciona el nivel corporativo al que pertenece este usuario.',
                'correo': 'Por favor, ingresa una dirección de correo válida.',
                'contrasena': 'Por favor, ingresa una contraseña.',
                'rol': 'Por favor, selecciona un rol de la lista.',
                'permisos': 'Por favor, selecciona al menos un permiso de la lista.',
            };
            return messages[elementId] || 'Este campo es requerido.';
        }
    }else if (url.includes('/Departamentos.php')) {


         //::::::::::::::::::::::::::::::::::::::::::::::::::::::::::\\
        //----[CÓDIGO JAVASCRIPT PARA LA PÁGINA DEPARTAMETNOS.PHP]----\\


        // Reestablecer formulario crear departamento
        $('#clearDepForm').click(function() {
            resetDepForm();
        });

        //Obtener lista con los tipos de departamentos disponibles
        $.getJSON('api/get_tipo_departamentos.php', function(data) {
            if (data.error) {
                console.log(data.error);
                return;
            }
            $.each(data, function(key, entry) {
                $('#tipoDepartamento').append($('<option></option>')
                    .attr('value', entry.id_tipo).text(entry.nombre));
            });
        });

        //Carga de Gerencias
        $.getJSON('api/get_gerencias.php', function(data) {
            $.each(data, function(key, entry) {
                $('#gerencia').append($('<option></option>').attr('value', entry.id_gerencia).text(entry.nombre));
            });
        });

        //Carga de Departamentos
        var listaDepartamentos = []; // Variable para almacenar todos los departamentos
        $.getJSON('api/get_departamentos.php', function(data) {
            listaDepartamentos = data;
            // $.each(data, function(key, entry) {
            // $('#dependencia').append($('<option></option>').attr('value', entry.id_departamento).text(entry.nombre));
            // });
        });

        //Actualizar departamentos cuándo se seleccione una gerencia
        $('#gerencia').change(function() {
            var gerenciaSeleccionada = $(this).val(); // Obtener el valor de la gerencia seleccionada
            actualizarDepartamentos(gerenciaSeleccionada);
        });

        function actualizarDepartamentos(gerenciaId) {
            $('#dependencia').empty(); // Vaciar el selector de departamentos
            $('#dependencia').append($('<option></option>').attr('value', '').text('Seleccionar')); // Opción por defecto

            // Filtrar y añadir solo los departamentos que coincidan con la gerencia seleccionada
            listaDepartamentos.forEach(function(departamento) {
                if(departamento.gerencia_id === gerenciaId) {
                    $('#dependencia').append($('<option></option>').attr('value', departamento.id_departamento).text(departamento.nombre));
                }
            });
        }

        // Convertir a mayúsculas el campo de nombre
        $('#nombreDepartamento').on('input', function() {
            $(this).val($(this).val().toUpperCase());
        });

        // Enviar formulario de creación de departamentos
        $('#DepForm').on('submit', function(e) {
            
            e.preventDefault();

            // Crear un objeto FormData con los datos del formulario
            var formData = new FormData(this);

            // Realizar la solicitud POST con fetch
            fetch('../admin/api/crear_depto.php', { // Asegúrate de ajustar la ruta
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Mostrar un mensaje de éxito
                    $('#infoModal .modal-title').text("¡Éxito!");
                    $('#infoModal .modal-body').text(data.success);
                    $('#infoModal').modal('show');

                    resetDepForm();
                } else if (data.error) {
                    // Mostrar un mensaje sde error
                    $('#infoModal .modal-title').text("¡Ups!");
                    $('#infoModal .modal-body').text(data.error);
                    $('#infoModal').modal('show');
                }
            })
            .catch(error => {
                // Manejar errores de red y de solicitud
                console.error('Error al enviar el formulario:', error);
            });
        });

    }else if (url.includes('/nomina.php')) {


         //::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::\\
        //----[CÓDIGO JAVASCRIPT PARA LA PÁGINA NOMINA.PHP]----\\

        //Formateador de números en RD$
        var formatter = new Intl.NumberFormat('es-DO', {
            style: 'currency',
            currency: 'DOP',
            minimumFractionDigits: 2
        });



        //--{FUNCIÓN PARA ACTUALIZAR TABLA NÓMINA EN FRONTEND}--//

        // Definir la función que actualiza la tabla
        function actualizarTablaNomina() {
            $.ajax({
                url: 'api/get_nomina.php',
                type: 'GET',
                dataType: 'json',
                success: function(data) {
                    if(data.error) {
                        alert("Error: " + data.error);
                    } else {
                        var tbody = $('#nominaTable tbody');
                        tbody.empty(); // Limpiar las filas existentes
                        $.each(data, function(i, item) {
                            var tr = $('<tr>').append(
                                $('<td>').text(item.codigo),
                                $('<td>').text(item.nombre),
                                $('<td>').text(item.cedula),
                                $('<td>').text(item.departamento),
                                $('<td>').text(item.cargo),
                                $('<td>').text(formatter.format(item.salario_mensual)),
                                $('<td>').text(formatter.format(item.compensacion_vehiculo))
                            );
                            tbody.append(tr);
                        });
                    }
                },
                error: function(jqXHR, textStatus, errorThrown) {
                    alert('Hubo un error al recuperar los datos de la nómina: ' + textStatus);
                }
            });
        }

        // Actualizar la tabla al cargar la página
        actualizarTablaNomina();

        //--{BOTÓN PARA SUBIR NÓMINA}--//

        // Subir Nomina
        $("#ActualizarNomina").click(function() {
            $("#inputArchivoNomina").val(''); // Resetea el input antes de cada nueva carga
            $("#inputArchivoNomina").click();
        });

        // Cuando se selecciona un archivo, se envía automáticamente
        $("#inputArchivoNomina").change(function() {
            var formData = new FormData();
            formData.append('archivoNomina', this.files[0]);

            $.ajax({
                url: 'api/subir_nomina.php',
                type: 'POST',
                data: formData,
                dataType: 'json',
                success: function (response) {
                    console.log(response)
                    if(response.error) {
                        alert("Error: " + response.error);
                        return;
                    } else {

                        // Dar formato a la sumatoria como moneda en RD$
                        var sumatoriaFormateada = formatter.format(response.sumatoriaSalarioMensual);

                        // Pide confirmación al usuario
                        var confirmar = confirm("La sumatoria del salario mensual es: " + sumatoriaFormateada + ". ¿Deseas proceder con la actualización de la nómina?");
                        if(confirmar) {
                            // Aquí iría el código para proceder con la actualización, si fuera necesario
                            alert("Nómina actualizada correctamente.");
                            actualizarTablaNomina(); // Llama a la función que actualiza la tabla.
                        }
                    }
                },
                error: function(jqXHR, textStatus, errorThrown) {
                    console.log("Error en la solicitud AJAX: ", textStatus, errorThrown);
                    alert('Hubo un error al subir el archivo.');
                },
                cache: false,
                contentType: false,
                processData: false
            });
        });
    }else if (url.includes('/ponchado.php')) {
         //::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::\\
        //----[CÓDIGO JAVASCRIPT PARA LA PÁGINA PONCHADO.


        //--{FUNCIÓN PARA ACTUALIZAR TABLA NÓMINA EN FRONTEND}--//

        // Definir la función que actualiza la tabla
        function actualizarTablaPonchado() {
            $.ajax({
                url: 'api/get_ponchado.php',
                type: 'GET',
                dataType: 'json',
                success: function(data) {
                    if(data.error) {
                        alert("Error: " + data.error);
                    } else {
                        $('#UltAct').text("Última actualización: " + data.ultAct);
                        var tbody = $('#ponchadoTable tbody');
                        tbody.empty(); // Limpiar las filas existentes
                        $.each(data.ponchado, function(i, item) {
                            var entrada = item.entrada === null ? "--" : item.entrada;
                            var salida = item.salida === null ? "--" : item.salida;
                            var tr = $('<tr>').append(
                                $('<td>').text(item.codigo),
                                $('<td>').text(item.fecha),
                                $('<td>').text(entrada),
                                $('<td>').text(salida),
                                 $('<td>').text(item.excepcion || "--") // Usar "--" si excepcion es null
                            );
                            tbody.append(tr);
                        });
                    }
                },
                error: function(jqXHR, textStatus, errorThrown) {
                    alert('Hubo un error al recuperar los datos de la nómina: ' + textStatus);
                }
            });
        }

        // Actualizar la tabla al cargar la página
        actualizarTablaPonchado();

        //--{BOTÓN PARA SUBIR PONCHADO}--//

        // Subir Ponchado
        $('#ActualizarPonchado').click(function() {
            $.ajax({
                url: 'api/get_sheet_data.php',
                type: 'GET',
                dataType: 'json',
                success: function(data) {
                    console.log(data); // Muestra los datos obtenidos en la consola para depuración
                    actualizarTablaPonchado(); // Llama a la función que actualiza la tabla.
                    alert("Ponchado actualizado con éxito al: " + data.ultimaAct);
                    $('#UltAct').text("Última actualización: " + data.ultimaAct);
                },
                error: function(xhr, status, error) {
                    // Manejo de errores de la solicitud AJAX
                    console.error("Error: " + status + " " + error);
                }
            });
        });
    }
});

document.addEventListener('DOMContentLoaded', function() {
    var inputs = document.querySelectorAll('input[required], select[required], textarea[required]');

    inputs.forEach(function(input) {
        input.addEventListener('invalid', function() {
            this.setCustomValidity(getCustomMessage(this.id));
        });

        input.addEventListener('input', function() {
            this.setCustomValidity('');
        });
    });
});
