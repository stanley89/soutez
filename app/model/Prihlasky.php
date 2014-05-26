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

    public function __construct(\Nette\Database\Context $db,  \Nette\Mail\IMailer $mailer) {
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
            ->setSubject("Potvrzení registrace do soutěže s Piráty do Evropy")
            ->addTo($vals['email'])
            ->addBcc("stanislav.stipl@pirati.cz")
            ->setHtmlBody($template);

        @$this->mailer->send($mail);

        return $this->db->getInsertId('prihlasky_id_seq');
    }
    public function isLocked($kod) {
        return $this->db->fetchField("SELECT locked FROM prihlasky WHERE okrsek=? AND locked=true;",$kod);
    }
    public function getByEmail($email) {
        return $this->db->fetch("SELECT * FROM prihlasky WHERE email=?;",$email);
    }
    public function getByTelefon($telefon) {
        return $this->db->fetch("SELECT * FROM prihlasky WHERE telefon=?;",$telefon);
    }
    public function activate($id) {
        $this->db->query("UPDATE prihlasky SET confirmed=true WHERE confirmed=false AND id=?;",$id);
    }
    public function phoneCall($telefon) {
        $prihlaska = $this->getByTelefon($telefon);
        if (empty($prihlaska)) {
            return false;
        } else {
            if ($prihlaska->confirmed==0) {
                $this->activate($prihlaska->id);
                $template = new \Nette\Templating\FileTemplate(__DIR__.'/@email2.latte');
                $template->registerFilter(new \Nette\Latte\Engine);
                $template->registerHelperLoader('Nette\Templating\Helpers::loader');
                $template->prihlaska = $prihlaska;

                $mail = new \Nette\Mail\Message;
                $mail->setFrom('soutez@pirati.cz')
                    ->setSubject("Potvrzení aktivace účtu")
                    ->addTo($prihlaska['email'])
                    ->addBcc("stanislav.stipl@pirati.cz")
                    ->setHtmlBody($template);


                $this->mailer->send($mail);
            }
            return true;
        }
    }
    public function getConfirmedCount() {
        return $this->db->fetchField("SELECT count(id) FROM prihlasky WHERE confirmed=true;");
    }
    public function getLockedCount() {
        return $this->db->fetchField("SELECT count(id) FROM prihlasky WHERE locked=true;");
    }
    public function getAll() {
        return $this->db->fetchAll("SELECT * FROM prihlasky;");
    }
    public function setOkrsek($id, $okrsek_obec,$okrsek_cislo) {
        $arr = array("okrsek_obec" => $okrsek_obec, "okrsek_cislo" => $okrsek_cislo);
        $this->db->query("UPDATE prihlasky SET ",$arr," WHERE id=?;",$id);
    }
}
