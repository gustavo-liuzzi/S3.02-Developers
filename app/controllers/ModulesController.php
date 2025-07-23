<?php

class ModulesController extends Controller
{
    public function mentorViewAction()
    {
        $this->view->usuarios = (new Usuario())->listarTodos();
    }

    
}