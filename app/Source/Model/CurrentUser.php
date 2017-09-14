<?php
namespace PentagonalProject\Prima\App\Source\Model;

use PentagonalProject\Prima\App\Source\CookieSession;
use PentagonalProject\Prima\App\Source\Model\Token\Auth;
use PentagonalProject\Prima\App\Source\Model\Database\User as UserDB;
use PentagonalProject\SlimService\Database;

/**
 * Class CurrentUser
 * @package PentagonalProject\Prima\App\Source\Model
 */
class CurrentUser
{
    const AUTH_NAME = 'auth';
    const LOGGED_NAME = 'logged';

    /**
     * @var string
     */
    protected $sessionPrefix = 'prima_';

    /**
     * @var Database
     */
    protected $db;

    /**
     * @var Auth
     */
    private $auth;

    /**
     * @var CookieSession
     */
    protected $cookie;

    /**
     * @var string
     */
    protected $suffixHash;

    /**
     * @var string
     */
    private $securityKey;

    /**
     * @var string
     */
    private $saltKey;

    /**
     * @var string
     */
    private $token;

    /**
     * CurrentUser constructor.
     *
     * @param Database $database
     * @param CookieSession $cookie
     * @param string $suffixHash
     * @param $securityKey
     * @param $saltKey
     */
    public function __construct(
        Database $database,
        CookieSession $cookie,
        string $suffixHash,
        $securityKey,
        $saltKey
    ) {
        $this->db = $database;
        $this->cookie = $cookie;
        $this->suffixHash = $suffixHash;
        $this->securityKey = $securityKey;
        $this->saltKey = $saltKey;
    }

    /**
     * @param string $cookieSelector
     */
    public function setSessionPrefix(string $cookieSelector)
    {
        $this->sessionPrefix = preg_replace('/^[a-z0-9\_\-]/i', '_', $cookieSelector);
    }

    /**
     * Set Auth Cookie
     *
     * @param string $userName
     * @param bool $remember
     * @return bool
     * @throws \RuntimeException
     */
    public function setAuthCookie(string $userName, $remember = false) : bool
    {
        if ($this->isLogin()) {
            throw new \RuntimeException(
                'User has logged!',
                E_NOTICE
            );
        }

        $user = new UserDB($this->db);
        $user = $user->getUserByUserName($userName);
        if (!$user) {
            return false;
        }
        $expiration = time() + (($remember ? 14 : 2 ) * 3600 * 24);
        $expire = $remember ? $expiration + (12 * 3600) : 0;

        $this->auth = new Auth($user);
        $this->token = $this->auth->create($expiration);

        $cookieAuth  = $this->generateAuthCookie($user, $expiration, self::AUTH_NAME, $this->token);
        $cookieLogin  = $this->generateAuthCookie($user, $expiration, self::LOGGED_NAME, $this->token);
        $this->setCookieToResponse($cookieAuth, $cookieLogin, $expire);

        return true;
    }

    /**
     * Keep The Session
     */
    final public function keepSession()
    {
        if ($this->isLogin()) {
            $oldSession = $this->auth->get($this->token);
            $session   = $this->auth->keep($this->token);
            if ($oldSession['expiration'] <> $session['expiration']) {
                $expiration = $session['expiration'];
                $cookieAuth  = $this->generateAuthCookie(
                    $this->auth->getUser(),
                    $expiration,
                    self::AUTH_NAME,
                    $this->token
                );
                $cookieLogin  = $this->generateAuthCookie(
                    $this->auth->getUser(),
                    $expiration,
                    self::LOGGED_NAME,
                    $this->token
                );
                $this->setCookieToResponse($cookieAuth, $cookieLogin);
            }
        }
    }

    /**
     * @return CurrentUser
     */
    final public function init() : CurrentUser
    {
        if ($this->initAuth()) {
            // keep the session
            $this->keepSession();
        }

        return $this;
    }

    /**
     * @param User $user
     * @param int $expiration
     * @param string $scheme
     * @param string $token
     *
     * @return string
     */
    private function generateAuthCookie(User $user, $expiration, $scheme = self::AUTH_NAME, $token = '') : string
    {
        $salt = $scheme === self::AUTH_NAME ? $this->saltKey : $this->securityKey;
        $username = $user[UserDB::COLUMN_USERNAME];
        $fragmentPassword = substr($user[UserDB::COLUMN_PASSWORD], 8, 4);
        $key = $this->makeHash("{$username}|{$fragmentPassword}|{$expiration}|{$token}", $salt);
        $hash = hash_hmac('sha256', "{$username}|{$expiration}|{$token}", $key);
        return $username . '|' . $expiration . '|' . $token . '|' . $hash;
    }

    /**
     * @param string $cookieAuth
     * @param string $cookieLogin
     * @param int|null $expiration
     */
    private function setCookieToResponse(string $cookieAuth, string $cookieLogin, int $expiration = null)
    {
        $this->cookie->setCookie($this->getAuthCookieName(), $cookieAuth, $expiration);
        $this->cookie->setCookie($this->getLoggedCookieName(), $cookieLogin, $expiration);
    }

    /**
     * Init check auth
     * @return bool
     */
    final private function initAuth()
    {
        if ($this->checkCookie(self::AUTH_NAME) && $this->checkCookie(self::LOGGED_NAME)) {
            return true;
        }

        $this->token = null;
        $this->auth = null;
        return false;
    }

    /**
     * @param string $scheme
     *
     * @return bool|Auth
     */
    private function checkCookie(string $scheme)
    {
        if (! ($cookie_elements = $this->parseCookie($scheme))) {
            return false;
        }

        $username = $cookie_elements['username'];
        $hmac     = $cookie_elements['hmac'];
        $token    = $cookie_elements['token'];
        $expired  = $expiration = $cookie_elements['expiration'];
        if ($expired < time()) {
            return false;
        }
        $salt = $scheme === 'auth' ? $this->saltKey : $this->securityKey;
        $userDb = new UserDB($this->db);
        $user = $userDb->getUserByUserName($username);
        if (!$user) {
            return false;
        }

        $fragmentPassword = substr($user[UserDB::COLUMN_PASSWORD], 8, 4);
        $key = $this->makeHash("{$username}|{$fragmentPassword}|{$expiration}|{$token}", $salt);
        $hash = hash_hmac('sha256', "{$username}|{$expiration}|{$token}", $key);
        if (! hash_equals($hash, $hmac)) {
            return false;
        }

        $manager = new Auth($user);
        if (!$manager->verify($token)) {
            return false;
        }

        $this->auth = $manager;
        $this->token = $token;
        return $manager;
    }

    /**
     * @param string $cookieValue
     * @param string $key
     *
     * @return string
     */
    private function makeHash(string $cookieValue, string $key) : string
    {
        return hash_hmac('md5', $cookieValue, $key);
    }

    /**
     * @param string $scheme
     *
     * @return array|bool
     */
    private function parseCookie(string $scheme)
    {
        switch ($scheme) {
            case self::LOGGED_NAME:
                $cookie = $this->cookie->get($this->getLoggedCookieName());
                break;
            default:
                $cookie = $this->cookie->get($this->getAuthCookieName());
        }

        if (!is_string($cookie)) {
            return false;
        }
        $cookie_elements = explode('|', $cookie);
        if (count($cookie_elements) !== 4) {
            return false;
        }

        list($username, $expiration, $token, $hmac) = $cookie_elements;

        return compact('username', 'expiration', 'token', 'hmac', 'scheme');
    }

    /**
     * @return bool
     */
    final public function isLogin() : bool
    {
        return $this->token && $this->auth;
    }

    /**
     * @return string
     */
    public function getSessionPrefix() : string
    {
        return $this->sessionPrefix;
    }

    /**
     * @return string
     */
    public function getAuthCookieName() : string
    {
        return $this->sessionPrefix . self::AUTH_NAME . $this->suffixHash;
    }

    /**
     * @return string
     */
    public function getLoggedCookieName() : string
    {
        return $this->sessionPrefix . self::LOGGED_NAME . $this->suffixHash;
    }

    /**
     * @return string|null
     */
    public function getToken()
    {
        return $this->token;
    }

    /**
     * @return Auth|null
     */
    public function getAuth()
    {
        return $this->auth;
    }

    /**
     * @return null|User
     */
    final public function getUser()
    {
        return $this->auth ? $this->auth->getUser() : null;
    }

    /**
     * @return bool
     */
    final public function destroy() : bool
    {
        if ($this->auth) {
            $this->auth->destroyCurrentSession($this->auth->hash($this->token));
            // reset
            $this->token = null;
            $this->auth = null;
            return true;
        }

        return false;
    }

    /**
     * @return bool
     */
    final public function destroyOtherSession() : bool
    {
        if ($this->isLogin()) {
            $this->auth->destroyOtherSession($this->auth->hash($this->token));
            return true;
        }

        return false;
    }

    /**
     * @return bool
     */
    final public function destroyAll() : bool
    {
        if ($this->auth) {
            $this->auth->destroyAllSession();
            // reset
            $this->token = null;
            $this->auth = null;
            return true;
        }

        return false;
    }
}
