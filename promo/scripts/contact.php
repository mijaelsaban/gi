<?php
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if(!empty($_POST['contactname']) && !empty($_POST['contactemail']) && !empty($_POST['contactmessage'])) {
	$to = 'info@grupoimpessa.com, maxipasteris@gmail.com'; // Your e-mail address here.
	$body = "\nNombre: {$_POST['contactname']}
	\nEmail: {$_POST['contactemail']}
	\nTeléfono:  {$_POST['contacttelefono']}
	\nLocalidad: {$_POST['contactlocalidad']}
	\n\n\n{$_POST['contactmessage']}\n\n";
	mail($to, "Conversión en landing de cercos", $body, "From: {$_POST['contactemail']}"); // E-Mail subject here.
    }
}
?>