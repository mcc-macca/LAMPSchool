<?php
/*
  Copyright (C) 2015 Pietro Tamburrano
  Copyright (C) 2022 Pietro Tamburrano, Vittorio Lo Mele
  Questo programma è un software libero; potete redistribuirlo e/o modificarlo secondo i termini della
  GNU Affero General Public License come pubblicata
  dalla Free Software Foundation; sia la versione 3,
  sia (a vostra scelta) ogni versione successiva.

  Questo programma è distribuito nella speranza che sia utile
  ma SENZA ALCUNA GARANZIA; senza anche l'implicita garanzia di
  POTER ESSERE VENDUTO o di IDONEITA' A UN PROPOSITO PARTICOLARE.
  Vedere la GNU Affero General Public License per ulteriori dettagli.

  Dovreste aver ricevuto una copia della GNU Affero General Public License
  in questo programma; se non l'avete ricevuta, vedete http://www.gnu.org/licenses/
 */

/* Programma per il login al registro. */

if (isset($_GET['suffisso']))
    $suffisso = $_GET['suffisso'];
else
    $suffisso = "";
//require_once '../php-ini.php';
require_once "../php-ini" . $suffisso . ".php";
require_once '../lib/funzioni.php';

// si pulisce tutto il contenuto della sessione 
// e si torna alla pagina di login

session_start();

// controlla se la sessione esistente è OIDC
if($_SESSION["oidc-step2"]){
    // effettua il logout anche sull'IDP OIDC
    require "../lib/oidc/OpenIDConnectClient.php";
    $oidc = new Jumbojett\OpenIDConnectClient($_SESSION["oidc_issuer"], $_SESSION["oidc_client_id"], $_SESSION["oidc_client_secret"]);
    $token = $_SESSION["oidc_idtoken"];
    $redi = $_SESSION["oidc_redirect_uri"];
    session_unset();
    session_destroy();
    session_start();
    $oidc->signOut($token, $redi);
}

session_unset();
session_destroy();
session_start();

$con = mysqli_connect($db_server, $db_user, $db_password, $db_nome);
require "../lib/req_assegna_parametri_a_sessione.php";
$_SESSION["prefisso"] = $prefisso_tabelle;
//$_SESSION["annoscol"] = $_SESSION['annoscol'];
$_SESSION["suffisso"] = $suffisso;

// controllo presenza file devmode
if(file_exists("../.devmode")){
    $_SESSION["devmode"] = true;
}

//$_SESSION["versioneprecedente"]=$_SESSION['versioneprecedente'];
//$_SESSION["nomefilelog"] = $_SESSION['nomefilelog'];
$_SESSION["alias"] = false;

$json = leggeFileJSON('../lampschool.json');
$_SESSION['versione'] = $json['versione'];

if($_SESSION["oidc_enabled"] == "exclusive"){
    header("Location: oidclogin.php");
}

$titolo = "Inserimento dati di accesso";
$seedcasuale = mt_rand(100000, 999999);
$seme = md5(date('Y-m-d') . $seedcasuale);

$script = "<script src='../lib/js/crypto.js'></script>\n";
$script .= "<script>

var isIE = /*@cc_on!@*/false || !!document.documentMode;
var isEdge = !isIE && !!window.StyleMedia;
if (isEdge)
    alert('L\'uso di Edge può portare ad anomalie nel funzionamento del registro! Usa Chrome, Firefox o Safari.');

function codifica()
{
    seme='$seme';
   
    document.getElementById('passwordmd5').value = hex_md5(hex_md5(hex_md5(document.getElementById('password').value))+seme);
    document.getElementById('password').value = '';
    return true;
}
   

</script>\n";
stampa_head($titolo, "", $script, "", false);
stampa_testata("Accesso al registro", "", $_SESSION['nome_scuola'], $_SESSION['comune_scuola']);

$messaggio = stringa_html('messaggio');

if (strlen($messaggio) > 0) {
    $mex = '<center><font color="red"><br><b>';

    if ($messaggio == 'errore') {
        $mex .= 'Nome utente e/o password errati !';
    } else {
        $mex .= $messaggio;
    }
    echo $mex . '</b><br></font></center>';
}
?>
<center>
    <form id='formLogin' action='logincheck.php' method='POST' onsubmit='return codifica();'>
        <table border='0'>
            <tr>
                <td> Utente</td>
                <td><input type='text' name='utente' id='utente'></td>
            </tr>
            <tr>
                <td> Password</td>
                <td><input type='password' name='pass' id='password'></td>
            </tr>
            <tr>
                <td colspan='2' align='center'><br/><input type='submit' name='OK' value='Accedi'></td>
            </tr>
        </table>
        <noscript>
        <input name='js_enabled' type='hidden' value='1'>
        </noscript>
        <input type='hidden' name='password' id='passwordmd5'>
    </form>
    <br/>
    <br>
<?php

if ($_SESSION["oidc_enabled"] == "yes") {
    print "<a href='oidclogin.php?suffisso=" . $_SESSION['suffisso'] . "'>Accedi con " . $_SESSION['oidc_provider_name']."</a>";
}

print "<br><br><a href='richresetpwd.php?suffisso=" . $_SESSION['suffisso'] . "'>Password dimenticata</a>";

print "<br><br><a href='" . $_SESSION['sito_scuola'] . "' target='_top'>Ritorna ad home page</a>";
?>
</center>
<script>
    document.getElementById('utente').focus();
</script>
    <?php
//$json = leggeFileJSON('../lampschool.json');

    stampa_piede($_SESSION['versioneprecedente']);
    eseguiQuery($con, "insert into " . $prefisso_tabelle . "tbl_seed(seed) values('$seme')", false, false);
    ?>
