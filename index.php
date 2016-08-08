<?php

error_reporting(E_ALL);
ini_set('display_errors', 'On');


require_once __DIR__ . "/loader.php";

use Ondrejnov\EET\Exceptions\ServerException;
use Ondrejnov\EET\Dispatcher;
use Ondrejnov\EET\Receipt;

header('Content-Type: application/json; charset=utf-8');

if (empty($_POST))
{
    $response = array("hotovo" => false, "chyba"=>0, "popis"=>"data nesmí být prázdná");
    echo json_encode($response);
}
else
{

    if (empty($_POST["uuid_zpravy"]) || empty($_POST["dic_popl"]) || empty($_POST["id_provoz"]) || empty($_POST["id_pokl"]) || empty($_POST["porad_cis"]) || empty($_POST["celk_trzba"]))
    {
        $response = array("hotovo" => false, "chyba"=>1, "popis"=>"spatny format odeslanych dat");
        echo json_encode($response);
    }
    else
    {

        $dispatcher = new Dispatcher(PLAYGROUND_WSDL, DIR_CERT . '/pospichal/key.pem', DIR_CERT . '/pospichal/certificate.pem');
        $dispatcher->trace = TRUE;

        // Example receipt
        $r = new Receipt();
        $r->uuid_zpravy = $_POST["uuid_zpravy"];//'b3a09b52-7c87-4014-a496-4c7a53cf9120';
        $r->dic_popl = $_POST["dic_popl"];//'CZ72080043';
        $r->id_provoz = $_POST["id_provoz"];//'181';
        $r->id_pokl = $_POST["id_pokl"];//'1';
        $r->porad_cis = $_POST["porad_cis"];//'1';
        if (!empty($_POST["dat_trzby"]))
        {
            $r->dat_trzby = $_POST["dat_trzby"];//new \DateTime();
        }
        else
        {
            $r->dat_trzby = new \DateTime();
        }
        $r->celk_trzba = $_POST["celk_trzba"];//1000;

        $bkp = $dispatcher->getBKP($r);
        $pkp = $dispatcher->getPKP($r);

        try
        {
            $fik = $dispatcher->send($r); // Send request
            //$data = sprintf("{'success':true, 'fik': %s, 'uid': %s}",$fik, $r->uuid_zpravy);
            $response = array("hotovo" => true, "fik" => $fik, "uuid_zpravy"=> $r->uuid_zpravy, "zpracovano"=>$r->dat_trzby, "bkp"=>$bkp, "pkp"=>$pkp);
            echo json_encode($response);
        }
        catch (ServerException $e)
        {
            $response = array("hotovo" => false, "chyba"=>2, "vraceno"=>$e->getMessage(), "popis"=>"nektera ze zadanych hodnot v systemu je spatne", "bkp"=>$bkp, "pkp"=>$pkp);
            echo json_encode($response);
            //var_dump($e->getMessage()); // See exception
        }
        catch (\Exception $e)
        {
            $response = array("hotovo" => false, "chyba"=>3, "vraceno"=>$e->getMessage(), "popis"=>"Technicka chyba, prosim ihned kontaktujte spravce systemu", "bkp"=>$bkp, "pkp"=>$pkp);
            echo json_encode($response);
            //var_dump($e); // Fatal error
        }
    }

}



?>