<?

date_default_timezone_set('America/Buenos_Aires');

$urlactual = 'http://www.grupoimpessa.com/';
$email = 'info@grupoimpessa.com';
$emailcopia = 'info@grupoimpessa.com';
$alttitle = 'grupoimpessa.com';
$asunto = 'Consulta desde ' . $alttitle;
$urlbase = '../envio/';

include_once($urlbase.'lib/class.vksuccess.php');
include_once($urlbase.'lib/class.vkexception.php');
include_once($urlbase.'lib/class.vkwarning.php');
include_once($urlbase.'lib/class.web.php');
include_once($urlbase.'lib/class.session.php');
include_once($urlbase.'lib/class.vkmysql.php');
include_once($urlbase.'lib/class.appobject.php');
include_once($urlbase.'lib/class.tuple.php');
include_once($urlbase.'lib/class.tuplecoll.php');
include_once($urlbase.'lib/class.query.php');
include_once($urlbase.'lib/class.dataset.php');
include_once($urlbase.'lib/class.vkfile.php');
include_once($urlbase.'lib/class.vkimage.php');
include_once($urlbase.'lib/class.control.php');

$web = new Web('Titulo', '');
$session = new Session();


?>
