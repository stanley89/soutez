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
        $form->addSelect("okres", "Okres" )->setPrompt("-- vyber okres --");
        $form->addSelect("obec", "Obec" )->setPrompt("-- vyber obec --");
        $form->addSelect("ulice", "Ulice" )->setPrompt("-- vyber ulici --");
        $form->addSelect("okrsek", "Okrsek")->setPrompt("-- vyber číslo okrsku --");
        $form->addSubmit("send_okrsek", "Odeslat");

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
                $form['okres']->setItems($this->ruian->getOkresPairs($vals['vusc']));
                $this->hranice = $this->ruian->getVuscHranice($vals['vusc']);

                $vals = $form->getValues();
            }
            if (!empty($vals['okres'])) {
                $form['obec']->setItems($this->ruian->getObecPairs($vals['okres']));
                $this->hranice = $this->ruian->getOkresHranice($vals['okres']);

                $vals = $form->getValues();
            }
            if (!empty($vals['obec'])) {
                $form['ulice']->setItems($this->ruian->getUlicePairs($vals['obec']));
                $this->hranice = $this->ruian->getObecHranice($vals['obec']);

                $vals = $form->getValues();
            }
            if (!empty($vals['ulice'])) {
                $form['okrsek']->setItems($this->ruian->getOkrsekPairs($vals['ulice']));
                $this->hranice = $this->ruian->getUliceHranice($vals['ulice']);

                $vals = $form->getValues();
            }
            if (!empty($vals['okrsek'])) {
                $this->template->okrsek = $this->ruian->getOkrsekHranice($vals['okrsek']);
                $this->hranice = $this->ruian->getOkrsekHranice($vals['okrsek']);

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
        $this->hranice = $this->ruian->getStatHranice();
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
