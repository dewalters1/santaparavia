<?php

namespace App\Controllers;

use Core\View;
use Core\Controller;
use App\Models\User;

class Register extends Controller
{
    /**
     * Show the index page
     *
     * @return void
     */
    public function indexAction()
    {
        $vars = array();
        if (isset($_POST['name'])) { $vars['name'] = $_POST['name']; }
        if (isset($_POST['email'])) { $vars['email'] = $_POST['email']; }
        View::renderTemplate('Home/register.html', $vars);
    }

    /**
     * Save user registration information
     *
     * @return void
     */
    public function createAction() {
        $user = new User($_POST);

        if ($user->save()) {

            $user->sendActivationEmail();

            $this->redirect('/Home/success');
            exit();
        } else {

            View::renderTemplate('Home/register.html', [
                'user' => $user
            ]);

        }
    }

    /**
     * Show the signup success page
     *
     * @return void
     */
    public function successAction()
    {
        View::renderTemplate('Home/success.html');
    }

    /**
     * Activate a new account
     *
     * @return void
     */
    public function activateAction()
    {
        User::activate($this->route_params['token']);

        $this->redirect('/register/activated');
    }

    /**
     * Show the activation success page
     *
     * @return void
     */
    public function activatedAction()
    {
        View::renderTemplate('Home/activated.html');
    }
}