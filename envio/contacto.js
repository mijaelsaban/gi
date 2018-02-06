/******************************************************************************
 Funciones de validación
 */
function validarEmail(valor) {
    re=/^[_a-zA-Z0-9-]+(.[_a-zA-Z0-9-]+)*@[a-zA-Z0-9-]+(.[a-zA-Z0-9-]+)*(.[a-zA-Z]{2,3})$/
    return re.exec(valor);
}

function validarTel(valor) {
    re=/^[0-9() \-\+#]+([0-9() \-\+#]+)$/
    return re.exec(valor);
}

function validarWeb(valor) {
    re=/^[0-9a-zA-Z]([-.\w]*[0-9a-zA-Z])*(:(0-9)*)*(\/?)( [a-zA-Z0-9\-\.\?\,\'\/\\\+&%\$#_]*)?$/
    return re.exec(valor);
}
function validarCP(valor) {
    if ((valor.length > 0) && (valor.length < 4)) {
        return false;
    } else {
        re=/^[0-9]+([0-9]+)$/
        return re.exec(valor);
    }
}
function validarNro(valor) {
    re=/^([0-9]+)$/
    return re.exec(valor);
}

function enviarContacto() {
    $.ajax({
        type: 'POST',
        async: false,
        url: 'http://grupoimpessa.com/envio/ajax.contacto.php',
        data: $('#formContacto').serialize(),
        success: function(respuesta) {
            try {
                if (respuesta == '1') {
                    $('#formError').css('display', 'block');
                    $('#formError').css('color', '#00FF00');
                    $('#formError').html('El envío se realizó correctamente.');
                    $('#form_nombre').val('');
					$('#form_apellido').val('');
                    $('#form_email').val('');
                    $('#form_tel').val('');
                    $('#form_mensaje').val('');
                }else{
                    $('#formError').css('display', 'block');
                    $('#formError').css('color', '#FF0000');
                    $('#formError').html('No se pudo realizar el envío, pruebe en otro momento.');
                }
            }catch(e) {
                console.log(e);
                $('#formError').css('display', 'block');
                $('#formError').html('No se pudo realizar el envío, pruebe en otro momento.');
            }
        },
        error: function(xhr, status, error) {
            console.log(error);
        }
    });
}

$(document).ready(function() {

    $('#formContacto').submit(function() {

        $('#formError').html('');
        $('#formError').css('display', 'none');

        valido = true;

        if ($('#form_nombre').val() == '') {
            $('#formError').css('display', 'block');
            $('#formError').html('El NOMBRE no puede ser vacío.');
            return false;
        }

        if ($('#form_email').val() == '') {
            $('#formError').css('display', 'block');
            $('#formError').html('El E-MAIL no puede ser vacío.');
            return false;
        }

        if (!validarEmail($('#form_email').val())) {
            $('#formError').css('display', 'block');
            $('#formError').html('El E-MAIL debe ser válido.');
            return false;
        }

        if ($('#form_mensaje').val() == '') {
            $('#formError').css('display', 'block');
            $('#formError').html('El MENSAJE no puede ser vacío.');
            return false;
        }

        enviarContacto();
        return false;

    });
});