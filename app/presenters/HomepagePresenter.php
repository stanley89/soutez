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

    public function __construct(\Nette\Http\Session $session) {
        $this->section = $session->getSection("soutez");
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
        $form->addSelect("kraj", "Kraj", array("Královéhradecký"))->setPrompt("-- vyber kraj --");
        $form->addSelect("okres", "Okres", array("Hradec Králové"))->setPrompt("-- vyber okres --");
        $form->addSelect("obec", "Obec", array("Hradec Králové"))->setPrompt("-- vyber obec --");
        $form->addSelect("ulice", "Ulice", array("Vydrova"))->setPrompt("-- vyber ulici --");
        $form->addSelect("okrsek", "Okrsek", array("48"))->setPrompt("-- vyber číslo okrsku --");


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
    public function actionPrihlaseni2() {
        if ($this->section->body>2) {
            $this->flashMessage("Gratuluji, prošel jsi vědomostním testem pro účast v soutěži. Nyní si vyber okrsek, ve kterém budeš dělat kampaň.");
        } else {
            $this->flashMessage("Tvoje odpovědi nestačí pro účast v soutěži. Můžeš to ale zkusit znovu.");
            $this->redirect("prihlaseni");
        }
    }
	public function renderDefault()
	{
		$this->template->anyVariable = 'any value';
	}

}
