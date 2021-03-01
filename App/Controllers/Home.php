<?php

namespace App\Controllers;

use Core\Controller;
use \Core\View;

/**
 * Home controller
 *
 * PHP version 5.4
 */
class Home extends Controller
{

    /**
     * Before filter
     *
     * @return void
     */
    protected function before()
    {
        //echo "(before) ";
        //return false;
    }

    /**
     * After filter
     *
     * @return void
     */
    protected function after()
    {
        //echo " (after)";
    }

    /**
     * Show the index page
     *
     * @return void
     */
    public function indexAction()
    {
        View::renderTemplate('Home/index.html');
    }

    /**
     * Show the success page
     * @return void
     */
    public function successAction()
    {
        View::renderTemplate('Home/success.html');
    }
}
