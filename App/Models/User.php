<?php

namespace App\Models;

use App\Mail;
use App\Token;
use PDO;
use PDOException;
use App\Config;
use Core\Model;
use Core\View;

class User extends Model
{
    /**
     * Error messages
     * @var array
     */
    public $errors = [];

    /**
     * Class Constructor
     * @param array $data  Initial property values
     * @return void
     */
    public function __construct($data = []) {
        foreach ($data as $key => $value) {
            $this->$key = $value;
        }
    }

    /**
     * Function Name:  save()
     * Task:           Save user registration information
     * Arguments:
     * Returns:
     * @param $result boolean
     */
    public function save()
    {
        $this->validate();
        if (empty($this->errors)) {

            $password_hash = password_hash($this->password, PASSWORD_DEFAULT);

            $token = new Token();
            $hashed_token = $token->getHash();
            $this->activation_token = $token->getValue();
            $query = "INSERT INTO users (display_name, email_address, password_hash, activation_hash) VALUES (:name, :email, :password_hash, :activation_hash)";
            try {
                $mySqlLink = static::connectToPdo(Config::DB_TYPE, Config::DB_HOST, Config::DB_NAME, Config::DB_USER, Config::DB_PASSWORD);
                $stmt = $mySqlLink->prepare($query);
                $stmt->bindParam(':name', $this->name, PDO::PARAM_STR);
                $stmt->bindParam(':email', $this->email, PDO::PARAM_STR);
                $stmt->bindParam(':password_hash', $password_hash, PDO::PARAM_STR);
                $stmt->bindParam(':activation_hash', $hashed_token, PDO::PARAM_STR);
                return $stmt->execute();
            } catch (PDOException $e) {
                //$errormsg = "ERROR: " . $e->getMessage() . " (" . $e->getCode() . ")";
                //View::renderTemplate("500.html", ['errormsg' => $errormsg, 'goBackUrl' => '']);
                $this->errors[] = "ERROR: " . $e->getMessage() . " (" . $e->getCode() . ")";
                return false;
            }
        }
        return false;
    }

    /**
     * Function name:  validate()
     * Task:           Validate user registration data
     * Arguments:
     * Returns:
     * @return void
     */
    public function validate() {
        // Name
        if ($this->name == '') {
            $this->errors[] = 'Name is required';
        }
        // email address
        if (filter_var($this->email, FILTER_VALIDATE_EMAIL) === false) {
            $this->errors[] = 'Invalid email';
        }
        if (static::emailExists($this->email, $this->id ?? null)) {
            $this->errors[] = 'Email address already exists';
        }
        // Password
        if (isset($this->password)) {
            if (strlen($this->password) < 8) {
                $this->errors[] = 'Please enter at least 8 characters for the password';
            }
            if (preg_match('/.*[a-z]+.*/i', $this->password) == 0) {
                $this->errors[] = 'Password needs at least one lowercase letter';
            }
            if (preg_match('/.*[A-Z]+.*/i', $this->password) == 0) {
                $this->errors[] = 'Password needs at least one uppercase letter';
            }
            if (preg_match('/.*\d+.*/i', $this->password) == 0) {
                $this->errors[] = 'Password needs at least one number';
            }
            if (preg_match('/.*[!@#$%^&*-]+.*/i', $this->password) == 0) {
                $this->errors[] = 'Password needs at least one special character (!@#$%^&*-)';
            }
        }
    }

    /**
     * See if a user record already exists with the specified email
     *
     * @param string $email email address to search for
     * @param string $ignore_id Return false anyway if the record found has this ID
     *
     * @return boolean  True if a record already exists with the specified email, false otherwise
     */
    public static function emailExists($email, $ignore_id = null)
    {
        $user = static::findByEmail($email);

        if ($user) {
            if ($user->id != $ignore_id) {
                return true;
            }
        }

        return false;
    }

    /**
     * Find a user model by email address
     *
     * @param string $email email address to search for
     *
     * @return mixed User object if found, false otherwise
     */
    public static function findByEmail($email)
    {
        $sql = 'SELECT * FROM users WHERE email_address = :email';

        $mySqlLink = static::connectToPdo(Config::DB_TYPE, Config::DB_HOST, Config::DB_NAME, Config::DB_USER, Config::DB_PASSWORD);
        $stmt = $mySqlLink->prepare($sql);
        $stmt->bindValue(':email', $email, PDO::PARAM_STR);

        $stmt->setFetchMode(PDO::FETCH_CLASS, get_called_class());

        $stmt->execute();

        return $stmt->fetch();
    }

    /**
    //* Authenticate a user by email and password.
     * Authenticate a user by email and password. User account has to be active.
     *
     * @param string $email email address
     * @param string $password password
     *
     * @return mixed  The user object or false if authentication fails
     */
    public static function authenticate($email, $password)
    {
        $user = static::findByEmail($email);

        //if ($user) {
        if ($user && $user->is_active) {
            if (password_verify($password, $user->password_hash)) {
                return $user;
            }
        }

        return false;
    }

    /**
     * Find a user model by ID
     *
     * @param string $id The user ID
     *
     * @return mixed User object if found, false otherwise
     */
    public static function findByID($id)
    {
        $sql = 'SELECT * FROM users WHERE id = :id';

        $mySqlLink = static::connectToPdo(Config::DB_TYPE, Config::DB_HOST, Config::DB_NAME, Config::DB_USER, Config::DB_PASSWORD);
        $stmt = $mySqlLink->prepare($sql);
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);

        $stmt->setFetchMode(PDO::FETCH_CLASS, get_called_class());

        $stmt->execute();

        return $stmt->fetch();
    }

    /**
     * Remember the login by inserting a new unique token into the remembered_logins table
     * for this user record
     *
     * @return boolean  True if the login was remembered successfully, false otherwise
     */
    public function rememberLogin()
    {
        $token = new Token();
        $hashed_token = $token->getHash();
        $this->remember_token = $token->getValue();

        $this->expiry_timestamp = time() + 60 * 60 * 24 * 30;  // 30 days from now

        $sql = 'INSERT INTO remembered_logins (token_hash, user_id, expires_at)
                VALUES (:token_hash, :user_id, :expires_at)';

        $mySqlLink = static::connectToPdo(Config::DB_TYPE, Config::DB_HOST, Config::DB_NAME, Config::DB_USER, Config::DB_PASSWORD);
        $stmt = $mySqlLink->prepare($sql);

        $stmt->bindValue(':token_hash', $hashed_token, PDO::PARAM_STR);
        $stmt->bindValue(':user_id', $this->id, PDO::PARAM_INT);
        $stmt->bindValue(':expires_at', date('Y-m-d H:i:s', $this->expiry_timestamp), PDO::PARAM_STR);

        return $stmt->execute();
    }

    /**
     * Send password reset instructions to the user specified
     *
     * @param string $email The email address
     *
     * @return void
     */
    public static function sendPasswordReset($email)
    {
        $user = static::findByEmail($email);

        if ($user) {

            if ($user->startPasswordReset()) {

                $user->sendPasswordResetEmail();

            }
        }
    }

    /**
     * Start the password reset process by generating a new token and expiry
     *
     * @return boolean
     */
    protected function startPasswordReset()
    {
        $token = new Token();
        $hashed_token = $token->getHash();
        $this->password_reset_token = $token->getValue();

        $expiry_timestamp = time() + 60 * 60 * 2;  // 2 hours from now

        $sql = 'UPDATE users
                SET password_reset_hash = :token_hash,
                    password_reset_expires_at = :expires_at
                WHERE id = :id';

        $mySqlLink = static::connectToPdo(Config::DB_HOST, Config::DB_NAME, Config::DB_USER, Config::DB_PASSWORD);
        $stmt = $mySqlLink->prepare($sql);

        $stmt->bindValue(':token_hash', $hashed_token, PDO::PARAM_STR);
        $stmt->bindValue(':expires_at', date('Y-m-d H:i:s', $expiry_timestamp), PDO::PARAM_STR);
        $stmt->bindValue(':id', $this->id, PDO::PARAM_INT);

        return $stmt->execute();
    }

    /**
     * Send password reset instructions in an email to the user
     *
     * @return void
     */
    protected function sendPasswordResetEmail()
    {
        $url = 'http://' . $_SERVER['HTTP_HOST'] . '/password/reset/' . $this->password_reset_token;

        $text = View::getTemplate('Password/reset_email.txt', ['url' => $url]);
        $html = View::getTemplate('Password/reset_email.html', ['url' => $url]);

        Mail::send($this->email, 'Password reset', $text, $html);
    }

    /**
     * Find a user model by password reset token and expiry
     *
     * @param string $token Password reset token sent to user
     *
     * @return mixed User object if found and the token hasn't expired, null otherwise
     */
    public static function findByPasswordReset($token)
    {
        $token = new Token($token);
        $hashed_token = $token->getHash();

        $sql = 'SELECT * FROM users
                WHERE password_reset_hash = :token_hash';

        $mySqlLink = static::connectToPdo(Config::DB_TYPE, Config::DB_HOST, Config::DB_NAME, Config::DB_USER, Config::DB_PASSWORD);
        $stmt = $mySqlLink->prepare($sql);

        $stmt->bindValue(':token_hash', $hashed_token, PDO::PARAM_STR);

        $stmt->setFetchMode(PDO::FETCH_CLASS, get_called_class());

        $stmt->execute();

        $user = $stmt->fetch();

        if ($user) {

            // Check password reset token hasn't expired
            if (strtotime($user->password_reset_expires_at) > time()) {

                return $user;
            }
        }
    }

    /**
     * Reset the password
     *
     * @param string $password The new password
     *
     * @return boolean  True if the password was updated successfully, false otherwise
     */
    public function resetPassword($password)
    {
        $this->password = $password;

        $this->validate();

        //return empty($this->errors);
        if (empty($this->errors)) {

            $password_hash = password_hash($this->password, PASSWORD_DEFAULT);

            $sql = 'UPDATE users
                    SET password_hash = :password_hash,
                        password_reset_hash = NULL,
                        password_reset_expires_at = NULL
                    WHERE id = :id';

            $mySqlLink = static::connectToPdo(Config::DB_TYPE, Config::DB_HOST, Config::DB_NAME, Config::DB_USER, Config::DB_PASSWORD);
            $stmt = $mySqlLink->prepare($sql);

            $stmt->bindValue(':id', $this->id, PDO::PARAM_INT);
            $stmt->bindValue(':password_hash', $password_hash, PDO::PARAM_STR);

            return $stmt->execute();
        }

        return false;
    }

    /**
     * Send an email to the user containing the activation link
     *
     * @return void
     */
    public function sendActivationEmail()
    {
        $url = 'http://' . $_SERVER['HTTP_HOST'] . '/register/activate/' . $this->activation_token;

        $text = View::getTemplate('Home/activation_email.txt', ['url' => $url]);
        $html = View::getTemplate('Home/activation_email.html', ['url' => $url]);

        Mail::send($this->email, 'Account activation', $text, $html);
    }

    /**
     * Activate the user account with the specified activation token
     *
     * @param string $value Activation token from the URL
     *
     * @return void
     */
    public static function activate($value)
    {
        $token = new Token($value);
        $hashed_token = $token->getHash();

        $sql = 'UPDATE users
                SET is_active = 1,
                    activation_hash = null
                WHERE activation_hash = :hashed_token';

        $mySqlLink = static::connectToPdo(Config::DB_TYPE, Config::DB_HOST, Config::DB_NAME, Config::DB_USER, Config::DB_PASSWORD);
        $stmt = $mySqlLink->prepare($sql);

        $stmt->bindValue(':hashed_token', $hashed_token, PDO::PARAM_STR);

        $stmt->execute();
    }

    /**
     * Update the user's profile
     *
     * @param array $data Data from the edit profile form
     *
     * @return boolean  True if the data was updated, false otherwise
     */
    public function updateProfile($data)
    {
        $this->name = $data['name'];
        $this->email = $data['email'];

        // Only validate and update the password if a value provided
        if ($data['password'] != '') {
            $this->password = $data['password'];
        }

        $this->validate();

        if (empty($this->errors)) {

            $sql = 'UPDATE users
                    SET display_name = :name,
                        email_address = :email';

            // Add password if it's set
            if (isset($this->password)) {
                $sql .= ', password_hash = :password_hash';
            }

            $sql .= "\nWHERE id = :id";


            $mySqlLink = static::connectToPdo(Config::DB_TYPE, Config::DB_HOST, Config::DB_NAME, Config::DB_USER, Config::DB_PASSWORD);
            $stmt = $mySqlLink->prepare($sql);

            $stmt->bindValue(':name', $this->name, PDO::PARAM_STR);
            $stmt->bindValue(':email', $this->email, PDO::PARAM_STR);
            $stmt->bindValue(':id', $this->id, PDO::PARAM_INT);

            // Add password if it's set
            if (isset($this->password)) {

                $password_hash = password_hash($this->password, PASSWORD_DEFAULT);
                $stmt->bindValue(':password_hash', $password_hash, PDO::PARAM_STR);

            }

            return $stmt->execute();
        }

        return false;
    }

}