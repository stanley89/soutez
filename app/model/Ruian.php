<?php
/**
 * Created by PhpStorm.
 * User: stiplovi
 * Date: 16.2.14
 * Time: 10:33
 */

namespace App;


class Ruian {
    private $db;
    public function __construct(\Nette\Database\Context $db) {
        $this->db = $db;
    }

    public function getVuscPairs() {
        return $this->db->fetchPairs("SELECT kod,nazev FROM rn_vusc;");
    }
    public function getOkresPairs($vusc_kod) {
        return $this->db->fetchPairs("SELECT kod,nazev FROM rn_okres WHERE vusc_kod=? ORDER BY nazev;",$vusc_kod);
    }
    public function getObecPairs($okres_kod) {
        return $this->db->fetchPairs("SELECT kod,nazev FROM rn_obec WHERE okres_kod=? ORDER BY nazev;",$okres_kod);
    }
    public function getUlicePairs($obec_kod) {
        return $this->db->fetchPairs("SELECT kod,nazev FROM rn_ulice WHERE obec_kod=? ORDER BY nazev;",$obec_kod);
    }
    public function getOkrsekPairs($ulice_kod) {
        return $this->db->fetchPairs("SELECT rn_volebni_okrsek.kod,rn_volebni_okrsek.cislo FROM rn_ulice, rn_volebni_okrsek
        WHERE rn_ulice.kod=? AND ST_Intersects(rn_ulice.definicni_cara,rn_volebni_okrsek.hranice)
        ORDER BY cislo;",$ulice_kod);
    }
    public function getStatHranice() {
        return $this->db->fetchField("SELECT ST_AsText(ST_Transform(hranice, 4326)) FROM rn_stat;");
    }
    public function getVuscHranice($vusc_kod) {
        return $this->db->fetchField("SELECT ST_AsText(ST_Transform(hranice, 4326))
              FROM rn_vusc
              WHERE kod = ?;",$vusc_kod);
    }
    public function getOkresHranice($okres_kod) {
        return $this->db->fetchField("SELECT ST_AsText(ST_Transform(hranice, 4326))
              FROM rn_okres
              WHERE kod = ?;",$okres_kod);
    }
    public function getObecHranice($obec_kod) {
        return $this->db->fetchField("SELECT ST_AsText(ST_Transform(hranice, 4326))
              FROM rn_obec
              WHERE kod = ?;",$obec_kod);
    }
    public function getUliceHranice($ulice_kod) {
        return $this->db->fetchField("SELECT ST_AsText(ST_Transform(definicni_cara, 4326))
              FROM rn_ulice
              WHERE kod = ?;",$ulice_kod);
    }
    public function getOkrsekHranice($okrsek_kod) {
        return $this->db->fetchField("SELECT ST_AsText(ST_Transform(hranice, 4326))
              FROM rn_volebni_okrsek
              WHERE kod = ?;",$okrsek_kod);
    }
    public function convertHranice($hranice) {
        $hranice = str_replace("MULTIPOLYGON","",$hranice);
        $hranice = str_replace("(((","",$hranice);
        $hranice = str_replace(")))","",$hranice);
        $hranice = explode(",",$hranice);
        return $hranice;
    }
} 