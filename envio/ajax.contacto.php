<?php

ini_set('display_errors', 0);

include('inc.start.php');

$result = 0;

$form_nombre = $web->getVarchar('nombre', '');
$form_apellido = $web->getVarchar('apellido', '');
$form_email = $web->getVarchar('email', '');
$form_tel = $web->getVarchar('tel', '');

include_once($urlbase.'lib/class.mailsender.php');

/////////////////////////////////////////////////
$oMailUs = new MailSender($urlbase.'lib/base');


$oMailUs->set('urlbase', $urlbase);
$oMailUs->set('alttitle', $alttitle);
$oMailUs->set('nombre', $web->getVarchar('nombre', ''), '');
$oMailUs->set('apellido', $web->getVarchar('apellido', ''), '');
$oMailUs->set('email', $web->getVarchar('email', ''), '');
$oMailUs->set('tel', $web->getVarchar('tel', ''), '');
$oMailUs->set('mensaje', $web->getVarchar('mensaje', ''), '');

$form_email = $web->getVarchar('email', '');

$oMailUs->from($form_email);
$oMailUs->replyto($emailcopia);

//Enviamos al cliente
$oMailUs->to($email);
$oMailUs->subject($asunto);

$oMailUs->body('html.contacto.mail.php');

if ($sended = $oMailUs->send()) $result = 1;

?>
<?=$result?>