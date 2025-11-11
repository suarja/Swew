<?php

namespace App\Controller;

class IndexController
{
    public function index()
    {
        #Route("/home", name="app_index")
        return  $this->render('base.html.twig');
    }
}
