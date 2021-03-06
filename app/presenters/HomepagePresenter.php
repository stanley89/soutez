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
    private $okrsek;
    private $obec;

    public function __construct(\Nette\Http\Session $session, \App\Ruian $ruian, \App\Prihlasky $prihlasky) {
        $this->section = $session->getSection("soutez");
        $this->ruian = $ruian;
        $this->prihlasky = $prihlasky;
    }
    protected function createComponentPrihlaseni() {
        $form = new \Nette\Application\UI\Form();
        $form->addRadioList("otazka1", "Vadí ti, že stát sleduje s kým si volá, jak dlouho, a kde se při tom nacházíš?",
            array(1 => "Ne, na prostý lidi musí být přísnost.", "No a co, kdo nedělá nic špatnýho, nemá co skrývat. ", "Ano, vadí mi to, stát mě nemá co šmírovat a chci to změnit. "));
//        $form->addRadioList("otazka2", "Myslíš, že je potřeba úprava toho, jak se u nás používají evropské dotace?",
//            array(1 => "Ne, každé ráno se jedu projet na cyklostezku odnikud nikam, je tam krásně", "Vůbec ne, tatínek má poradenskou firmu","Ano, chci to změnit"));
        $form->addRadioList("otazka3", "Vadí ti, že mezinárodní smlouvy nejprve v utajení připraví firmy, kterých se týkají a až potom se k nim dostanou zvolení poslanci?",
            array(1 => "Ne, aspoň je připravují odborníci", "Tak to přece není, jste paranoidní", "Ano, vadí a chci to změnit"));
        $form->addRadioList("otazka4", "Považuješ za problém, že o svobodě Internetu rozhodují lidé, kteří uvázli ve století potrubní pošty?",
            array(1 => "Ne, určitě na to mají poradce", "Já bych ty Internety zakázal(a), každého jenom otravují", "Ano, Internet změnil svět a lidi co mu rozumí, jsou v evropském parlamentu potřeba!"));
        $form->addSubmit("send_quiz", "Odpovědět na otázky");

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
        $form->addSelect("okrsek", "Okrsek")->setPrompt("-- vyber číslo okrsku --")->setAttribute("hidden");
        $form->addSubmit("send_okrsek", "Potvrdit výběr okrsku")->setAttribute("hidden");

        $form->onSuccess[] = callback($this, "okrsek");
        return $form;
    }
    protected function createComponentPrihlaseni3() {
        $form = new \Nette\Application\UI\Form();
        $form->addHidden("okrsek", $this->section->okrsek);
        $form->addText("jmeno", "Jméno a příjmení")->addRule(Form::FILLED, "Vyplň prosím jméno a příjmení.");
        $form->addText("ulice", "Ulice a č.p.");//->addRule(Form::FILLED, "Vyplň prosím ulici a ČP.");
        $form->addText("obec", "Obec");//->addRule(Form::FILLED, "Vyplň prosím obec.");
        $form->addText("psc", "PSČ");//->addRule(Form::FILLED, "Vyplň prosím PSČ.");
        $form->addText("telefon", "Telefon")->addRule(Form::FILLED, "Vyplň prosím telefon.")->addRule(Form::PATTERN, "Telefon musí mít 9 číslic.",'([0-9]\s*){9}');
        $form->addText("email", 'E-mail')->addRule(Form::FILLED, "Vyplň prosím e-mail.")
            ->addRule(Form::EMAIL, "Vyplň prosím platný e-mail.");
        $form->addCheckbox("checkbox1", "Jsem starší 15 let.")->addRule(Form::FILLED, "Pro účast v soutěži musíš být starší než 15 let.");
        $form->addCheckbox("checkbox2", "Souhlasím s ")->addRule(Form::FILLED, "Pro účast v soutěži je třeba souhlasit s pravidly.");
        $form->addCheckbox("agree", "Chci zůstat v databázi příznivců.")->setValue(true);
        $form->addText("referer", "Referenční telefonní číslo")->addCondition(Form::FILLED)->addRule(Form::PATTERN, "Pokud vyplňuješ referenční číslo, musí mít 9 číslic. Referenční číslo je telefonní číslo člověka, který tě do soutěže přivedl. Jeho vyplnění není povinné.",'([0-9]\s*){9}');

        $form->addSubmit("send_address", "Potvrdit přihlášku");

        $form->onSuccess[] = callback($this, "address");
        return $form;
    }

    public function quiz($form) {
        $vals = $form->getValues();
        $body = 0;
        if ($vals["otazka1"]==3) $body++;
//        if ($vals["otazka2"]==3) $body++;
        if ($vals["otazka3"]==3) $body++;
        if ($vals["otazka4"]==3) $body++;
        $this->section->body = $body;
        if ($body>2) {
            $this->flashMessage("Gratulujeme, máš pirátské názory na klíčová témata naší kampaně. Nyní si vyber svůj soutěžní okrsek.");
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
            $this->template->obec = $this->ruian->getObec($vals['obec']);

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
            $this->okrsek = $this->ruian->getOkrsek($vals['okrsek']);
            $this->obec = $this->ruian->getObec($this->okrsek['obec_kod']);
            $this->hranice = $this->ruian->getOkrsekHranice($vals['okrsek']);

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
        if (!empty($prihlaska)) {
            $form['telefon']->addError("Zadané telefonní číslo už je v soutěži zaregistrováno.");
        }

        if (empty($prihlaska) ) {
            $vals['link'] = $this->link("//:Homepage:okrsek", array('kod' => $vals['okrsek']));

            $id = $this->prihlasky->add($vals);

            if (!empty($id)) {
                $this->flashMessage("Vítej v soutěži, tvoje přihlášení proběhlo úspěšně. Aktivuj svůj účet bezplatným prozvoněním čísla 222 703 223, poslechni si vyzváněcí melodii, dostaneš instrukce k soutěži, neboj, automat, který volající obsluhuje ti to nezvedne. Na zadaný e-mail ti přijdou podrobnější pokyny a materiály.");
            } else {
                $this->flashMessage("Přihlášení se bohužel nezdařilo. Když všechno selže, napiš na stanislav.stipl@pirati.cz");
            }
            $this->redirect("prihlaseni4");
        }
    }
    public function actionPrihlaseni2() {
        $this->redirect("prihlaseni");

        if ($this->section->body>2) {
        } else {
            $this->flashMessage("Tvé názory na naše klíčová témata bohužel nekorespondují s názory Pirátů. Pokud je změníš, můžeš to zkusit znovu.");
            $this->redirect("prihlaseni");
        }
        //$this->hranice = $this->ruian->getStatHranice();
    }
    public function renderPrihlaseni2() {
        if (!empty($this->hranice)) {
            $this->template->hranice = $this->ruian->convertHranice($this->hranice);
        }
        if (!empty($this->okrsek)) {
            $this->template->okrsek = $this->okrsek;
            $this->template->obec = $this->obec;

            $form = $this['prihlaseni2'];
            $form['send_okrsek']->setAttribute('hidden', false);
            if ($this->prihlasky->isLocked($this->okrsek['kod'])) {
                $this->template->message = "Vybraný okrsek je bohužel už obsazený a zamčený. Pokud se chceš zapojit do soutěže, vyber si prosím jiný.";
                $form['send_okrsek']->setAttribute('hidden', true);
            } else {
                $prihlaseni = $this->prihlasky->getByKod($this->okrsek['kod']);
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
    }
    public function actionPrihlaseni3() {
        $this->redirect("prihlaseni");

        $okrsek = $this->ruian->getOkrsek($this->section->okrsek);
        $obec = $this->ruian->getObec($okrsek['obec_kod']);
        $this->template->okrsek = $okrsek;
        $this->template->obec = $obec;
    }
	public function renderDefault()
	{
		$this->template->anyVariable = 'any value';
	}
    public function renderPocitadlo() {
        $this->template->cnt = $this->prihlasky->getConfirmedCount();
        $this->template->cnt2 = $this->prihlasky->getLockedCount();

    }
    public function handleMapa($longtitude, $latitude) {
        $okrsek = $this->ruian->getOkrsekByGps($longtitude, $latitude);
        if (!empty($okrsek)) {
            $obec = $this->ruian->getObec($okrsek['obec_kod']);
            $okres = $this->ruian->getOkres($obec['okres_kod']);
            $kraj = $this->ruian->getVusc($okres['vusc_kod']);

            $okresy = $this->ruian->getOkresPairs($kraj['kod']);
            $obce = $this->ruian->getObecPairs($okres['kod']);
            $okrsky = $this->ruian->getOkrsekPairs($obec['kod'],null);
            $ulice = $this->ruian->getUlicePairs($obec['kod'],null);


            $this['prihlaseni2']['okres']->setItems($okresy);
            $this['prihlaseni2']['obec']->setItems($obce);
            $this['prihlaseni2']['okrsek']->setItems($okrsky);
            $this['prihlaseni2']['ulice']->setItems($ulice);

            $this['prihlaseni2']['vusc']->setValue($kraj['kod']);
            $this['prihlaseni2']['okres']->setValue($okres['kod']);
            $this['prihlaseni2']['obec']->setValue($obec['kod']);
            $this['prihlaseni2']['okrsek']->setValue($okrsek['kod']);

            $this['prihlaseni2']['obec']->setAttribute('hidden',false);
            $this['prihlaseni2']['okres']->setAttribute('hidden',false);
            $this['prihlaseni2']['vusc']->setAttribute('hidden',false);
            $this['prihlaseni2']['okrsek']->setAttribute('hidden',false);
            $this['prihlaseni2']['ulice']->setAttribute('hidden',false);

            $this->hranice = $this->ruian->getOkrsekHranice($okrsek['kod']);
            $this->okrsek = $okrsek;
            $this->obec = $obec;

            $this->redrawControl('prihlaseni2');
            $this->redrawControl('mapa');
        }
    }
    public function renderOkrsek($kod) {
        $okrsek = $this->ruian->getOkrsek($kod);
        $this->template->obec = $this->ruian->getObec($okrsek['obec_kod']);
        $this->template->okrsek = $okrsek;
        $this->template->hranice = $this->ruian->convertHranice($this->ruian->getOkrsekHranice($kod));
    }
    public function actionAddOkrsky() {
        $prihlasky = $this->prihlasky->getAll();
        foreach ($prihlasky as $prihlaska) {
            $okrsek = $this->ruian->getOkrsek($prihlaska['okrsek']);
            $obec = $this->ruian->getObec($okrsek['obec_kod']);
            $this->prihlasky->setOkrsek($prihlaska['id'],$obec['nazev'],$okrsek['cislo']);

        }
        $this->terminate();
    }
}
