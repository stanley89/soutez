<?php
/**
 * Created by PhpStorm.
 * User: stiplovi
 * Date: 16.2.14
 * Time: 15:49
 */

namespace App;


class Prihlasky {
    private $db;

    public function __construct(\Nette\Database\Context $db) {
            $this->db = $db;
    }

    public function getByKod($kod) {
        return $this->db->fetch("SELECT * FROM prihlasky WHERE okrsek=?",$kod);
    }

    public function add($vals) {
        $arr = array("okrsek" => $vals['okrsek'],
                    "jmeno" => $vals['jmeno'],
                    "ulice" => $vals['ulice'],
                    "obec" => $vals['obec'],
                    "psc" => $vals['psc'],
                    "telefon" => $vals['telefon'],
                    "email" => $vals['email'],
                    "agree" => $vals['agree'],

        );
        $this->db->query("INSERT INTO prihlasky ",$arr);
        return $this->db->getInsertId();
    }
}