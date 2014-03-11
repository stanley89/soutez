<?php

namespace App\Presenters;
use Nette\Mail\SendmailMailer;

use Nette,
	App\Model;
use Nette\Application\UI\Form;
/**
 * Homepage presenter.
 */
class HomepagePresenter extends BasePresenter
{
    private $section;
    private $ruian;
    private $hranice;
    private $prihlasky;

    public function __construct(\Nette\Http\Session $session, \App\Ruian $ruian, \App\Prihlasky $prihlasky) {
        $this->section = $session->getSection("soutez");
        $this->ruian = $ruian;
        $this->prihlasky = $prihlasky;
    }
    protected function createComponentPrihlaseni() {
        $form = new \Nette\Application\UI\Form();
        $form->addRadioList("otazka1", "Otázka na možnost 2",array(1 => "Možnost 1", "Možnost 2", "Možnost 3"));
        $form->addRadioList("otazka2", "Otázka na možnost 1",array(1 => "Možnost 1", "Možnost 2", "Možnost 3"));
        $form->addRadioList("otazka3", "Otázka na možnost 3",array(1 => "Možnost 1", "Možnost 2", "Možnost 3"));
        $form->addRadioList("otazka4", "Otázka na možnost 3",array(1 => "Možnost 1", "Možnost 2", "Možnost 3"));
        $form->addSubmit("send_quiz", "Odeslat");

        $renderer = $form->getRenderer();
        $renderer->wrappers['controls']['container'] = 'dl';
        $renderer->wrappers['pair']['container'] = NULL;
        $renderer->wrappers['label']['container'] = 'dt';
        $renderer->wrappers['control']['container'] = 'dd';

        $form->onSuccess[] = callback($this, "quiz");
        return $form;
    }
    protected function createComponentPrihlaseni2() {
        $form = new \Nette\Application\UI\Form();
        $form->addSelect("vusc", "Kraj", $this->ruian->getVuscPairs())->setPrompt("-- vyber kraj --");
        $form->addSelect("okres", "Okres" )->setPrompt("-- vyber okres --")->setAttribute("hidden");
        $form->addSelect("obec", "Obec" )->setPrompt("-- vyber obec --")->setAttribute("hidden");
        $form->addSelect("ulice", "Ulice" )->setPrompt("-- vyber ulici --")->setAttribute("hidden");
        $form->addSelect("cp", "Čp" )->setPrompt("-- vyber ulici --")->setAttribute("hidden");
        $form->addSelect("okrsek", "Okrsek")->setPrompt("-- vyber číslo okrsku --")->setAttribute("hidden");
        $form->addSubmit("send_okrsek", "Odeslat")->setAttribute("hidden");

        $form->onSuccess[] = callback($this, "okrsek");
        return $form;
    }
    protected function createComponentPrihlaseni3() {
        $form = new \Nette\Application\UI\Form();
        $form->addHidden("okrsek", $this->section->okrsek);
        $form->addText("jmeno", "Jméno a příjmení")->addRule(Form::FILLED, "Vyplň prosím jméno a příjmení.");
        $form->addText("ulice", "Ulice a ČP")->addRule(Form::FILLED, "Vyplň prosím ulici a ČP.");
        $form->addText("obec", "Obec")->addRule(Form::FILLED, "Vyplň prosím obec.");
        $form->addText("psc", "PSČ")->addRule(Form::FILLED, "Vyplň prosím PSČ.");
        $form->addText("telefon", "Telefon")->addRule(Form::FILLED, "Vyplň prosím telefon.")->addRule(Form::PATTERN, "Telefon musí mít 9 číslic.",'([0-9]\s*){9}');
        $form->addText("email", 'E-mail')->addRule(Form::FILLED, "Vyplň prosím e-mail.")
            ->addRule(Form::EMAIL, "Vyplň prosím platný e-mail.");
        $form->addCheckbox("checkbox1", "Jsem starší 15 let.")->addRule(Form::FILLED, "Pro účast v soutěži musíš být starší než 15 let.");
        $form->addCheckbox("checkbox2", "Souhlasím s ")->addRule(Form::FILLED, "Pro účast v soutěži je třeba souhlasit s pravidly.");
        $form->addCheckbox("agree", "Chci zůstat v databázi příznivců.")->setValue(true);
        $form->addText("referer", "Referenční číslo (nepovinné)")->addCondition(Form::FILLED)->addRule(Form::PATTERN, "Pokud vyplňuješ referenční číslo, musí mít 9 číslic. Referenční číslo je telefonní číslo člověka, který tě do soutěže přivedl. Jeho vyplnění není povinné.",'([0-9]\s*){9}');

        $form->addSubmit("send_address", "Potvrdit přihlášku");

        $form->onSuccess[] = callback($this, "address");
        return $form;
    }

    public function quiz($form) {
        $vals = $form->getValues();
        $body = 0;
        if ($vals["otazka1"]==2) $body++;
        if ($vals["otazka2"]==1) $body++;
        if ($vals["otazka3"]==3) $body++;
        if ($vals["otazka4"]==3) $body++;
        $this->section->body = $body;
        if ($body>2) {
            $this->flashMessage("Gratuluji, prošel jsi vědomostním testem pro účast v soutěži. Nyní si vyber okrsek, ve kterém budeš dělat kampaň.");
        }
        $this->redirect("prihlaseni2");

    }
    public function okrsek($form) {
        $vals = $form->getValues();

        if (!empty($vals['vusc'])) {
                $okresy = $this->ruian->getOkresPairs($vals['vusc']);
		$form['okres']->setItems($okresy);
		if (count($okresy)==1) {
			$form['okres']->setValue(key($okresy));
		}
		$form['okres']->setAttribute('hidden',false);
                $this->hranice = $this->ruian->getVuscHranice($vals['vusc']);

                $vals = $form->getValues();
        }

        if (!empty($vals['okres'])) {
		    $obce = $this->ruian->getObecPairs($vals['okres']);
		    $form['obec']->setItems($obce);
		    if (count($obce)==1) {
			    $form['obec']->setValue(key($obce));
		    }
		    $form['obec']->setAttribute('hidden',false);
            $this->hranice = $this->ruian->getOkresHranice($vals['okres']);
            $vals = $form->getValues();
        }
        if (!empty($vals['obec'])) {
            $ulice = $this->ruian->getUlicePairs($vals['obec']);
    		if (count($ulice)>1) {
	    		$form['ulice']->setAttribute('hidden',false);
		    	$form['ulice']->setItems($ulice);
		    }
            $vals = $form->getValues();
            $okrsky = $this->ruian->getOkrsekPairs($vals['obec'],$vals['ulice']);
		    $form['okrsek']->setAttribute('hidden',false);
		    $form['okrsek']->setItems($okrsky);
		    if (count($okrsky)==1) {
			    $form['okrsek']->setValue(key($okrsky));
		    }
            $vals = $form->getValues();
            if (!empty($vals['ulice'])) {
                $this->hranice = $this->ruian->getOkrskyHraniceUlice($vals['ulice']);
            } else {
                $this->hranice = $this->ruian->getObecHranice($vals['obec']);
            }
        }
        if (!empty($vals['okrsek'])) {
            $this->template->okrsek = $this->ruian->getOkrsekHranice($vals['okrsek']);
            $this->hranice = $this->ruian->getOkrsekHranice($vals['okrsek']);
            $form['send_okrsek']->setAttribute('hidden', false);
            if ($this->prihlasky->isLocked($vals['okrsek'])) {
                $this->template->message = "Vybraný okrsek je bohužel už obsazený a zamčený. Pokud se chceš zapojit do soutěže, vyber si prosím jiný.";
                $form['send_okrsek']->setAttribute('hidden', true);
            } else {
                $prihlaseni = $this->prihlasky->getByKod($vals['okrsek']);
                if ($prihlaseni) {
                    if (count($prihlaseni)==1) {
                        $this->template->message = "Vybraný okrsek je obsazený, ale ještě není uzamčený. V okrsku je přihlášen 1 účastník soutěže. Přihlaš se do okrsku, pošli důkaz o kampani dřív než on a okrsek bude tvůj!";
                    } elseif (count($prihlaseni)<5) {
                        $this->template->message = "Vybraný okrsek je obsazený, ale ještě není uzamčený. V okrsku jsou přihlášeni ".count($prihlaseni)." účastníci soutěže. Přihlaš se do okrsku, pošli důkaz o kampani dřív než oni a okrsek bude tvůj!";
                    } else {
                        $this->template->message = "Vybraný okrsek je obsazený, ale ještě není uzamčený. V okrsku je přihlášeno ".count($prihlaseni)." účastníků soutěže. Přihlaš se do okrsku, pošli důkaz o kampani dřív než oni a okrsek bude tvůj!";
                    }
                } else {
                    $this->template->message = "Vybraný okrsek je volný! Rychle se přihlaš a pošli důkaz o kampani, ať jej neobsadí někdo jiný.";
                }
            }
        }
        $this->redrawControl('prihlaseni2');
        $this->redrawControl('mapa');

        if ($form['send_okrsek']->isSubmittedBy()) {
            $okrsek = $this->ruian->getOkrsek($vals['okrsek']);
            $obec = $this->ruian->getObec($okrsek['obec_kod']);
            if ($this->prihlasky->isLocked($vals['okrsek'])) {
                $this->flashMessage("Tento okrsek je už zamčený. Vyberte si prosím jiný okrsek.");
            } else {
                $this->flashMessage("Úspěšně jsi vybral okrsek ".$obec['nazev']." (".$okrsek['cislo']."). Teď už zbývá jen vyplnit své údaje.");
                $this->section->okrsek = $vals['okrsek'];
                $this->redirect("prihlaseni3");
            }
        }
    }
    public function address($form) {
        $vals = $form->getValues();
        $prihlaska = $this->prihlasky->getByTelefon($vals['telefon']);
        $prihlaska2 = $this->prihlasky->getByEmail($vals['email']);
        if (!empty($prihlaska)) {
            $form['telefon']->addError("Zadané telefonní číslo už je v soutěži zaregistrováno.");
        }
        if (!empty($prihlaska2)) {
            $form['email']->addError("Zadaný e-mail je už v soutěži zaregistrován.");
        }
        if (empty($prihlaska) && empty($prihlaska2)) {
            $id = $this->prihlasky->add($vals);
            if (!empty($id)) {
                $this->flashMessage("Tvoje přihlášení do soutěže proběhlo úspěšně. Na e-mail ti přijdou podrobnější pokyny a materiály.");
            } else {
                $this->flashMessage("Přihlášení se bohužel nezdařilo. Když všechno selže, napiš na stanislav.stipl@pirati.cz");
            }
            $this->redirect("prihlaseni4");
        }
    }
    public function actionPrihlaseni2() {
        if ($this->section->body>2) {
        } else {
            $this->flashMessage("Tvoje odpovědi nestačí pro účast v soutěži. Můžeš to ale zkusit znovu.");
            $this->redirect("prihlaseni");
        }
        //$this->hranice = $this->ruian->getStatHranice();
    }
    public function renderPrihlaseni2() {
        if (!empty($this->hranice)) {
            $this->template->hranice = $this->ruian->convertHranice($this->hranice);
        }
    }
    public function actionPrihlaseni3() {
        $okrsek = $this->ruian->getOkrsek($this->section->okrsek);
        $obec = $this->ruian->getObec($okrsek['obec_kod']);
        $this->template->okrsek = $okrsek;
        $this->template->obec = $obec;
    }
	public function renderDefault()
	{
		$this->template->anyVariable = 'any value';
	}

}
