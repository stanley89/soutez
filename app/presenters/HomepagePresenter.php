<?php

namespace App\Presenters;

use Nette,
	App\Model;

/**
 * Homepage presenter.
 */
class HomepagePresenter extends BasePresenter
{
    private $section;
    private $ruian;
    private $hranice;

    public function __construct(\Nette\Http\Session $session, \App\Ruian $ruian) {
        $this->section = $session->getSection("soutez");
        $this->ruian = $ruian;
    }
    protected function createComponentPrihlaseni() {
        $form = new \Nette\Application\UI\Form();
        $form->addRadioList("otazka1", "Otázka na možnost 2",array(1 => "Možnost 1", "Možnost 2", "Možnost 3"));
        $form->addRadioList("otazka2", "Otázka na možnost 1",array(1 => "Možnost 1", "Možnost 2", "Možnost 3"));
        $form->addRadioList("otazka3", "Otázka na možnost 3",array(1 => "Možnost 1", "Možnost 2", "Možnost 3"));
        $form->addRadioList("otazka4", "Otázka na možnost 3",array(1 => "Možnost 1", "Možnost 2", "Možnost 3"));
        $form->addSubmit("send_quiz", "Odeslat");
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
        $form->addSubmit("send_okrsek", "Odeslat")->setAttribute("hidden");

        $form->onSuccess[] = callback($this, "okrsek");
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
        $this->redirect("prihlaseni2");

    }
    public function okrsek($form) {
        $vals = $form->getValues();
        if ($form['send_okrsek']->isSubmittedBy()) {

        } else {
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
                $this->hranice = $this->ruian->getObecHranice($vals['obec']);

                $vals = $form->getValues();
            }
            if (!empty($vals['okrsek'])) {
                $this->template->okrsek = $this->ruian->getOkrsekHranice($vals['okrsek']);
                $this->hranice = $this->ruian->getOkrsekHranice($vals['okrsek']);
		$form['send_okrsek']->setAttribute('hidden', false);
            }
            $this->redrawControl('prihlaseni2');
            $this->redrawControl('mapa');

        }
    }
    public function actionPrihlaseni2() {
        if ($this->section->body>2) {
            $this->flashMessage("Gratuluji, prošel jsi vědomostním testem pro účast v soutěži. Nyní si vyber okrsek, ve kterém budeš dělat kampaň.");
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
	public function renderDefault()
	{
		$this->template->anyVariable = 'any value';
	}

}
