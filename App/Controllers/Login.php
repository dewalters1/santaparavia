<?php


namespace App\Controllers;

use App\Auth;
use App\Flash;
use App\Models\User;
use Core\Controller;
use Core\View;

class Login extends Controller
{
    /**
     * Show the index page
     *
     * @return void
     */
    public function indexAction()
    {
        View::renderTemplate('Home/login.html');
    }

    /**
     * Authenticate user
     *
     * @return void
     */
    public function authAction()
    {
        $user = User::authenticate($_POST['email'], $_POST['password']);

        $remember_me = isset($_POST['remember_me']);

        if ($user) {

            Auth::login($user, $remember_me);

            Flash::addMessage('Login successful');

            $this->redirect(Auth::getReturnToPage());

        } else {

            Flash::addMessage('Login unsuccessful, please try again', Flash::WARNING);

            View::renderTemplate('Login/new.html', [
                'email' => $_POST['email'],
                'remember_me' => $remember_me
            ]);
        }

//        if (!User::authenticate($_POST['email'], $_POST['password'])) {
//            View::renderTemplate('Home/login.html', ['badlogin'=>1]);
//            exit();
//        } else {
//            session_start();
//
//            View::renderTemplate('Users/dashboard.html');
//        }

    }

    /**
     * Display fort password page
     *
     * @return void
     */
    public function forgotpwdAction()
    {
        View::renderTemplate('Home/forgotpwd.html');
    }

    /**
     * Send forgot password email
     *
     * @return void
     */
    public function forgotAction()
    {


    }

    /**
     * Log out a user
     *
     * @return void
     */
    public function destroyAction()
    {
        Auth::logout();

        $this->redirect('/login/show-logout-message');
    }

    /**
     * Show a "logged out" flash message and redirect to the homepage. Necessary to use the flash messages
     * as they use the session and at the end of the logout method (destroyAction) the session is destroyed
     * so a new action needs to be called in order to use the session.
     *
     * @return void
     */
    public function showLogoutMessageAction()
    {
        Flash::addMessage('Logout successful');

        $this->redirect('/');
    }

}