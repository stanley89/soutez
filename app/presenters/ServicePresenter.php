<?php
/**
 * Created by PhpStorm.
 * User: stanley
 * Date: 3.3.14
 * Time: 16:00

*/
namespace App\Presenters;


class ServicePresenter extends BasePresenter {

    public function actionTelfa() {
        $body = file_get_contents('php://input');
        // zpracovani prijateho jsonu do objektu
        $json = json_decode($body);

        // nacteni cisla volajiciho a cisla volaneho z json objektu
        $caller_number = $json -> caller_number;
        $called_number = $json -> called_number;

        // paklize volajici hovor polozi, posle Telfa parametr hangup
        $hangup = $json -> hangup;

        // libovolna vlastni promenna, kterou posilate v odpovedi Telfe se Vam opet vrati, diky tomu je mozne zjistovat, co jste naposled udelal pro kazdy z hovoru
        $pokracovani = $json -> pokracovani;

        // paklize volajici polozi hovor, polozime ho taky
        if( empty($hangup) ){

            // kdyz prichozi hovor vola API poprve, neobsahuje vlastni promennou pokracovani
            if( empty($pokracovani) ){
                $found = $this->prihlasky->phoneCall($caller_number);
                if ($found) {
                    $a = array( 'action' => 'read', 'female' => 'false', 'text_to_speak' => 'Vážený pane Štípl, děkujeme za autorizaci vaší registrace na webu Pirátů. Přejeme hezký den', 'pokracovani' => '2' );
                } else {
                    $a = array( 'action' => 'read', 'female' => 'false', 'text_to_speak' => 'Vážený pane Štípl, děkujeme za autorizaci vaší registrace na webu Pirátů. Přejeme hezký den', 'pokracovani' => '2' );
                }
                // posleme povel prehraj hlasku s urcitym ID a vlastni promennou “pokracovani” s hodnotou 1, diky ktere pozname ze uz jsme u tohoto hovoru provedli prvni krok
                //$a = array( 'action' => 'play', 'recording_id' => '889', 'pokracovani' => '1' );
                // jakmile Telfa dokonci prehravani hlasky, opet zavola vasi URL, tentokrate bude request obsahovat vlastni promennou „pokracovani“ nastavenou na hodnotu 1
            } elseif ( $pokracovani == 1 ){
                // ve druhém kroku můžeme využijeme řečový syntetizér Telfy a přečteme zadaný text, zároveň pošleme parametr „pokracovani“ posleme s hodnotou 2
                // pri opetovnem zavolani URL posleme povel k polozeni hovoru, vzhledem k tomu, ze hovor nebyl zvednut a hlasky se prehravaly misto vyzvaneciho tonu, hovor je volajicimu odmitnut
                $a = array( 'action' => 'hangup');
            }
            // polozeni hovoru kdyz ho polozi volajici
        } else {
            $a = array( 'action' => 'hangup' );
        }
        echo json_encode($a);
    }
}
