<?php
/**
 * Created by PhpStorm.
 * User: stiplovi
 * Date: 22.2.14
 * Time: 17:38
 */

namespace App\Presenters;


class ServicePresenter extends BasePresenter {
    public function actionTelfa() {
        print_r($this->getHttpRequest()->getPost());
        $this->terminate();
    }
} 