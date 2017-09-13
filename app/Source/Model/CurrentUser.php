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
        $this->sessionPrefix = $cookieSelector;
    }

    /**
     * @param string $userName
     * @param bool $remember
     * @return bool
     */
    public function setAuthCookie(string $userName, $remember = false)
    {
        $user = new UserDB($this->db);
        $user = $user->getUserByUserName($userName);
        if (!$user) {
            return false;
        }
        $expiration = time() + (($remember ? 14 : 2 ) * 3600 * 24);
        $expire = $remember ? $expiration + (12 * 3600) : 0;

        $auth = new Auth($user);
        $token = $auth->create($expiration);
        $cookieAuth  = $this->generateAuthCookie(
            $user,
            $expiration,
            'auth',
            $token
        );
        $cookieLogin  = $this->generateAuthCookie(
            $user,
            $expiration,
            'logged',
            $token
        );
        $this->cookie->setCookie(
            $this->getAuthCookieName(),
            $cookieAuth,
            $expire
        );
        $this->cookie->setCookie(
            $this->getLoggedCookieName(),
            $cookieLogin,
            $expire
        );

        // override
        if (!$this->isLogin()) {
            $this->auth = $auth;
        }

        return true;
    }

    /**
     * @return string
     */
    public function getAuthCookieName() : string
    {
        return $this->sessionPrefix . 'auth'. $this->suffixHash;
    }

    /**
     * @return string
     */
    public function getLoggedCookieName() : string
    {
        return $this->sessionPrefix . 'logged'. $this->suffixHash;
    }

    /**
     * @return CurrentUser
     */
    final public function init() : CurrentUser
    {
        $this->initAuth();
        return $this;
    }

    final private function initAuth()
    {
        if ($this->checkCookie('auth')) {
            $auth = $this->checkCookie('logged');
            if ($auth) {
                $this->auth = $auth;
            }
        }
    }

    private function generateAuthCookie(User $user, $expiration, $scheme = 'auth', $token = '')
    {
        $salt = $scheme === 'auth' ? $this->saltKey : $this->securityKey;
        $username = $user[UserDB::COLUMN_USERNAME];
        $fragmentPassword = substr($user[UserDB::COLUMN_PASSWORD], 8, 4);
        $key = $this->makeHash("{$username}|{$fragmentPassword}|{$expiration}|{$token}", $salt);
        $hash = hash_hmac('sha256', "{$username}|{$expiration}|{$token}", $key);
        return $username . '|' . $expiration . '|' . $token . '|' . $hash;
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
            case 'logged':
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
        return $this->auth instanceof Auth;
    }

    /**
     * @return null|User
     */
    final public function getUser()
    {
        if ($this->isLogin()) {
            return $this->auth->getCurrentUser();
        }
        return null;
    }
}
