$(document).ready(function() {
    var url = window.location.pathname;
    if (url.includes('/login.php')) {

         //::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::\\
        //----[CÓDIGO JAVASCRIPT PARA LA PÁGINA LOGIN.PHP]----\\

        //Enviar formulario de login
        $("#loginForm").on("submit", function(event){
            event.preventDefault();
		    $.ajax({
		        url: 'Authenticate.php',
		        type: 'POST',
		        data: $(this).serialize(),
		        dataType: 'json',
		        success: function(response){
		            if(response.success) {
		                window.location.href = response.redirect; // Redirección en caso de éxito
		            } else {
		                // Muestra el modal con el mensaje de error
		                $('#infoModal .modal-title').text("Error");
		                $('#infoModal .modal-body').text(response.error);
		                $('#infoModal').modal('show');
		            }
		        },
		        error: function(xhr, status, error) {
		            // Manejo de errores en la solicitud AJAX
		            console.error(error);
		        }
		    });
        });

    }
});