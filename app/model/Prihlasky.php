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
    private $mailer;

    public function __construct(\Nette\Database\Context $db,  \Nette\Mail\SendmailMailer $mailer) {
        $this->db = $db;
        $this->mailer = $mailer;
    }

    public function getByKod($kod) {
        return $this->db->fetchAll("SELECT * FROM prihlasky WHERE okrsek=?",$kod);
    }

    public function add($vals) {
        $arr = array("okrsek" => $vals['okrsek'],
                    "jmeno" => $vals['jmeno'],
                    "ulice" => $vals['ulice'],
                    "obec" => $vals['obec'],
                    "psc" => preg_replace('/\s+/', '', $vals['psc']),
                    "telefon" => preg_replace('/\s+/', '', $vals['telefon']),
                    "email" => $vals['email'],
                    "agree" => $vals['agree'],
                    "referer" => preg_replace('/\s+/', '', $vals['referer']),


        );
        $this->db->query("INSERT INTO prihlasky ",$arr);

        $template = new \Nette\Templating\FileTemplate(__DIR__.'/@email.latte');
        $template->registerFilter(new \Nette\Latte\Engine);
        $template->registerHelperLoader('Nette\Templating\Helpers::loader');
        $template->vals = $vals;

        $mail = new \Nette\Mail\Message;
        $mail->setFrom('soutez@pirati.cz')
            ->addTo($vals['email'])
            ->addBcc("stanislav.stipl@pirati.cz")
            ->setHtmlBody($template);


        $this->mailer->send($mail);

        return $this->db->getInsertId();
    }
    public function isLocked($kod) {
        return $this->db->fetchField("SELECT locked FROM prihlasky WHERE okrsek=? AND locked=1;",$kod);
    }
    public function getByEmail($email) {
        return $this->db->fetch("SELECT * FROM prihlasky WHERE email=?;",$email);
    }
    public function getByTelefon($telefon) {
        return $this->db->fetch("SELECT * FROM prihlasky WHERE telefon=?;",$telefon);
    }
    public function phoneCall() {
        return true;
    }
}