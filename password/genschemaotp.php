


<?php

session_start();
/**
 * Elenco degli indici del database
 *
 * @copyright  Copyright (C) 2014 Renato Tamilio
 * @license    GNU Affero General Public License versione 3 o successivi; vedete agpl-3.0.txt
 */
require_once '../php-ini' . $_SESSION['suffisso'] . '.php';
require_once '../lib/funzioni.php';

//require_once '../lib/ db / query.php';
//$lQuery = LQuery::getIstanza();
// istruzioni per tornare alla pagina di login 
////session_start();

$tipoutente = $_SESSION["tipoutente"]; //prende la variabile presente nella sessione

if ($tipoutente == "")
{
    header("location: ../login/login.php?suffisso=" . $_SESSION['suffisso']);
    die;
}
$titolo = "Generazione schemi OTP";
$script = "";
stampa_head($titolo, "", $script, "PMSDA");
stampa_testata("<a href='../login/ele_ges.php'>PAGINA PRINCIPALE</a> - $titolo", "", "$nome_scuola", "$comune_scuola");
$con = mysqli_connect($db_server, $db_user, $db_password, $db_nome);

$query = "select idutente,schematoken from tbl_utenti";
$ris = eseguiQuery($con, $query);
while ($rec = mysqli_fetch_array($ris))
{
    if ($rec['schematoken'] = "")
    {
        $schema = generaSchemaToken();
        $query = "update tbl_utenti set schematoken='$schema' where idutente=" . $rec['idutente'];
        eseguiQuery($con, $query);
    }
}
print "<br><br><center>SCHEMI PER OTP GENERATI CORRETTAMENTE</center>";



stampa_piede();

