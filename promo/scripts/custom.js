
$(window).load(function() {

	"use strict";


	
//------------------------------------------------------------------------
//						COUNTER SCRIPT
//------------------------------------------------------------------------
   $('.timer').counterUp({
            delay: 20,
            time: 2500
        });



//------------------------------------------------------------------------
//						NAVBAR SLIDE SCRIPT
//------------------------------------------------------------------------ 		
	$(window).scroll(function () {
        if ($(window).scrollTop() > $("nav").height()) {
            $("nav.navbar-slide").addClass("show-menu");
        } else {
            $("nav.navbar-slide").removeClass("show-menu");
			$(".navbar-slide .navMenuCollapse").collapse({toggle: false});
			$(".navbar-slide .navMenuCollapse").collapse("hide");
			$(".navbar-slide .navbar-toggle").addClass("collapsed");
        }
    });
	
//------------------------------------------------------------------------
//						NAVBAR HIDE ON CLICK (COLLAPSED) SCRIPT
//------------------------------------------------------------------------ 		
    $('.nav a').on('click', function(){ 
        if($('.navbar-toggle').css('display') !='none'){
            $(".navbar-toggle").click()
        }
    });
	
})




$(document).ready(function(){
			
	"use strict";

	
	
//------------------------------------------------------------------------
//						ANCHOR SMOOTHSCROLL SETTINGS
//------------------------------------------------------------------------
	$('a.goto, .navbar .nav a').smoothScroll({speed: 1200});




//------------------------------------------------------------------------	
//                    MAGNIFIC POPUP(LIGHTBOX) SETTINGS
//------------------------------------------------------------------------  
	          
    $('.portfolio-list li').magnificPopup({
        delegate: 'a:not(.btn)',
        type: 'image',
        gallery: {
            enabled: true
        }
    });

	

	
//------------------------------------------------------------------------
//						VIDEO BACKGROUND SETTINGS
//------------------------------------------------------------------------
if($('.video-bg')[0]) {
	$(function() {
		var BV = new $.BigVideo({container: $('.video-bg'), useFlashForFirefox:false});
		BV.init();
		if(navigator.userAgent.match(/iPhone|iPad|iPod|Android|BlackBerry|IEMobile/i)) {
				BV.show('images/video_gag.jpg');
		} else{
			if (!!window.opera || navigator.userAgent.indexOf(' OPR/') >= 0) {
				BV.show('video/video_bg.ogv', {doLoop:true, ambient:true});
			} else{
				BV.show('video/video_bg.mp4', {doLoop:true, ambient:true, altSource:'video/video_bg.ogv'});
			}
			BV.getPlayer().on('loadedmetadata', function(){
				$('#big-video-wrap video').fadeIn('slow');
			});
		}
	});	
}


		
	
//------------------------------------------------------------------------
//					SUBSCRIBE FORM VALIDATION'S SETTINGS
//------------------------------------------------------------------------          
    $('#subscribe_form').validate({
        onfocusout: false,
        onkeyup: false,
        rules: {
            email: {
                required: true,
                email: true
            }
        },
        errorPlacement: function(error, element) {
            error.appendTo( element.closest("form"));
        },
        messages: {
            email: {
                required: "Necesitamos su dirección de email para poder contactarlo",
                email: "Por favor, ingresá una dirección email válida"
            }
        },
					
        highlight: function(element) {
            $(element)
        },                    
					
        success: function(element) {
            element
            .text('').addClass('valid')
        }
    }); 
	

		
				
//------------------------------------------------------------------------------------
//						SUBSCRIBE FORM MAILCHIMP INTEGRATIONS SCRIPT
//------------------------------------------------------------------------------------		
    $('#subscribe_form').submit(function() {
        $('.error').hide();
        $('.error').fadeIn();
        // submit the form
        if($(this).valid()){
            $('#subscribe_submit').button('loading'); 
            var action = $(this).attr('action');
            $.ajax({
                url: action,
                type: 'POST',
                data: {
                    newsletter_email: $('#subscribe_email').val()
                },
                success: function(data) {
                    $('#subscribe_submit').button('reset');
					
					//Use modal popups to display messages
					$('#modalMessage .modal-title').html('<i class="icon icon-envelope-open"></i>' + data);
					$('#modalMessage').modal('show');
					
                },
                error: function() {
                    $('#subscribe_submit').button('reset');
					
					//Use modal popups to display messages
					$('#modalMessage .modal-title').html('<i class="icon icon-ban"></i>Oops!<br>Algo anduvo mal!');
					$('#modalMessage').modal('show');
					
                }
            });
        }
        return false; 
    });
	  
	  
	  
	  
//------------------------------------------------------------------------------------
//						CONTACT FORM VALIDATION'S SETTINGS
//------------------------------------------------------------------------------------		  
    $('#contact_form').validate({
        onfocusout: false,
        onkeyup: false,
        rules: {
            nombre: "required",
            email: {
                required: true,
                email: true
            },
            mensaje: "required"
        },
        errorPlacement: function(error, element) {
            error.insertAfter(element);
        },
        messages: {
            nombre: "Ingrese su nombre",
            mensaje: "¿Cuál es el mensaje?",
            email: {
                required: "Ingrese su dirección de email",
                email: "Por favor, ingrese una dirección de email válida"
            }
        },
					
        highlight: function(element) {
            $(element)
            .text('').addClass('error')
        },                    
					
        success: function(element) {
            element
            .text('').addClass('valid')
        }
    });   




//------------------------------------------------------------------------------------
//								CONTACT FORM SCRIPT
//------------------------------------------------------------------------------------	
	
    $('#contact_form').submit(function() {
        // submit the form
        if($(this).valid()){
            $('#contact_submit').button('loading'); 
            var action = $(this).attr('action');
            $.ajax({
                url: action,
                type: 'POST',
                data: {
                    contactname: $('#contact_name').val(),
                    //documento: $('#contact_documento').val(),
                    contacttelefono: $('#contact_telefono').val(),
                    contactemail: $('#contact_email').val(),
                    //direccion: $('#contact_direccion').val(),
                    contactlocalidad: $('#contact_localidad').val(),
                    contactmessage: $('#contact_message').val()
                },
                success: function() {
                   //$('#contact_submit').button('reset');
				  // $('#modalContact').modal('hide');
					
					//Use modal popups to display messages
					//$('#modalMessage .modal-title').html('<i class="icon icon-envelope-open"></i>Tu mensaje ha sido enviado.<br>Nos contactaremos a la brevedad. Gracias.');
				//	$('#modalMessage').modal('show');
				window.location.href = 'http://grupoimpessa.com/promo/gracias.html';
                },
                error: function() {
                    $('#contact_submit').button('reset');
					$('#modalContact').modal('hide');
					
					//Use modal popups to display messages
					$('#modalMessage .modal-title').html('<i class="icon icon-ban"></i>Ha ocurrido un error al enviar el mensaje.');
					$('#modalMessage').modal('show');
                }
            });
        } else {
            $('#contact_submit').button('reset')
        }
        return false; 
    });	    	  

});
