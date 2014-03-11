<?php
/**
 * Created by PhpStorm.
 * User: stanley
 * Date: 3.3.14
 * Time: 16:00

*/
namespace App\Presenters;


use Nette\Diagnostics\Debugger;

class ServicePresenter extends BasePresenter {

    private $prihlasky;
    public function injectPrihlasky(\App\Prihlasky $prihlasky) {
        $this->prihlasky = $prihlasky;
    }
    public function actionTelfa() {
        $body = file_get_contents('php://input');
        // zpracovani prijateho jsonu do objektu
        $json = json_decode($body);
        Debugger::log($json);

        // nacteni cisla volajiciho a cisla volaneho z json objektu
        $caller_number = $json -> caller_number;
        $called_number = $json -> called_number;

        // libovolna vlastni promenna, kterou posilate v odpovedi Telfe se Vam opet vrati, diky tomu je mozne zjistovat, co jste naposled udelal pro kazdy z hovoru
        $pokracovani = 0;
        if (!empty($json->pokracovani)) {
            $pokracovani = $json -> pokracovani;
        }

        // paklize volajici polozi hovor, polozime ho taky
        if( empty($json->hangup) ){

            // kdyz prichozi hovor vola API poprve, neobsahuje vlastni promennou pokracovani
            if( empty($pokracovani) ){
                $found = $this->prihlasky->phoneCall($caller_number);
                if ($found) {
                    $a = array( 'action' => 'read', 'female' => 'true', 'text_to_speak' =>  'Ahoj, právě ses úspěšně zaregistroval do soutěže s Piráty do Evropy! Komunikace v soutěži probíhá prioritně přes tvůj soutěžní email, kde se mimo jiné dozvíš, jak si pojistit vítězství ve svém soutěžním okrsku. Už teď bys měl mít ve své schránce ovzkaz od organizátorů. Pokud tam není, zkontroluj, jestli není ve spamu. Pokud ani tam email není, napiš organizátorům na soutež zavináč pirati.cz. Jestli chceš, můžeš si na webu objednat Balíček soutěžícího za 190 Kč, který obsahuje mimo jiné soutěžní tričko! Hodně úspěchů v soutěži Piráti do Evropy!', 'pokracovani' => '2' );
                } else {
                    $a = array( 'action' => 'read', 'female' => 'true', 'text_to_speak' => ' Ahoj, soutěžící, vítej v soutěži s Piráty do Evropy. Bohužel telefon, který jsi zaregistroval do soutěže nesouhlasí s číslem telefonu, ze kterého právě voláš. Pokus ses přepsal, zaregistruj se prosím znovu a opět nás prozvoň, případně zavolej z telefonního čísla, které jsi uvedl v soutěžní registraci. Pokud si s tímhle nevíš rady, zavolej na kontaktní číslo XXX XXX XXX.', 'pokracovani' => '2' );
                }
                // posleme povel prehraj hlasku s urcitym ID a vlastni promennou “pokracovani” s hodnotou 1, diky ktere pozname ze uz jsme u tohoto hovoru provedli prvni krok
                //$a = array( 'action' => 'play', 'recording_id' => '889', 'pokracovani' => '1' );
                // jakmile Telfa dokonci prehravani hlasky, opet zavola vasi URL, tentokrate bude request obsahovat vlastni promennou „pokracovani“ nastavenou na hodnotu 1
            } elseif ( $pokracovani == 2) {
                // ve druhém kroku můžeme využijeme řečový syntetizér Telfy a přečteme zadaný text, zároveň pošleme parametr „pokracovani“ posleme s hodnotou 2
                // pri opetovnem zavolani URL posleme povel k polozeni hovoru, vzhledem k tomu, ze hovor nebyl zvednut a hlasky se prehravaly misto vyzvaneciho tonu, hovor je volajicimu odmitnut
                $a = array( 'action' => 'hangup');
            }
            // polozeni hovoru kdyz ho polozi volajici
        } else {
            $a = array( 'action' => 'hangup' );
        }
        echo json_encode($a);
        $this->terminate();
    }
}
