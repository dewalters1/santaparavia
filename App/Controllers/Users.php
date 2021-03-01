<?php

namespace App\Controllers;

use Core\Controller;
use Core\View;

class Users extends Controller
{
    /**
     * Show the index page
     *
     * @return void
     */
    public function indexAction()
    {
        View::renderTemplate('Users/dashboard.html');
    }

    /**
     * Show the index page
     *
     * @return void
     */
    public function gamesAction()
    {
        View::renderTemplate('Users/gamelist.html');
    }

    /**
     * Show the index page
     *
     * @return void
     */
    public function settingsAction()
    {
        View::renderTemplate('Users/settings.html');
    }
}