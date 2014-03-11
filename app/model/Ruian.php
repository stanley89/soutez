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
    public function getOkrsekPairs($obec_kod, $ulice_kod) {
        if (empty($ulice_kod)) {
                return $this->db->fetchPairs("SELECT rn_volebni_okrsek.kod,rn_volebni_okrsek.cislo FROM rn_volebni_okrsek
                WHERE obec_kod=?
                ORDER BY cislo;",$obec_kod);
        } else {
                return $this->db->fetchPairs("SELECT o.kod,o.cislo FROM rn_ulice u, rn_volebni_okrsek o
                WHERE o.obec_kod=? AND u.kod=? AND ST_Intersects(u.definicni_cara,o.hranice)
                ORDER BY cislo;",$obec_kod,$ulice_kod);
        }
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
    public function getOkrskyHraniceUlice($ulice_kod) {
        return $this->db->fetchField("SELECT ST_ASText(ST_Transform(ST_Union(o.hranice), 4326))
              FROM rn_volebni_okrsek o, rn_ulice u
              WHERE ST_Intersects(o.hranice, u.definicni_cara) AND u.kod=?;
        ",$ulice_kod);
    }
    public function convertHranice($hranice) {
        $hranice = str_replace("MULTIPOLYGON","",$hranice);
        $hranice = str_replace("(((","",$hranice);
        $hranice = str_replace(")))","",$hranice);
        $hranice = explode(",",$hranice);
        return $hranice;
    }
    public function getOkrsek($kod) {
        return $this->db->fetch("SELECT * FROM rn_volebni_okrsek WHERE kod=?;",$kod);
    }
    public function getObec($kod) {
        return $this->db->fetch("SELECT * FROM rn_obec WHERE kod=?;",$kod);
    }
    public function getOkres($kod) {
        return $this->db->fetch("SELECT * FROM rn_okres WHERE kod=?;",$kod);
    }
    public function getVusc($kod) {
        return $this->db->fetch("SELECT * FROM rn_vusc WHERE kod=?;",$kod);
    }
    public function getOkrsekByGps($longtitude, $latitude) {
        return $this->db->fetch("SELECT o.* FROM rn_volebni_okrsek o WHERE ST_Contains(o.hranice, ST_Transform(st_setsrid(ST_GeomFromText('POINT(".$longtitude." ".$latitude.")'),4326),5514) )");
    }
} 
