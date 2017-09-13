<?php
namespace PentagonalProject\Prima\App\Source\Model\Token;

use PentagonalProject\Prima\App\Source\Model\User;

/**
 * Class Auth
 * @package PentagonalProject\Prima\App\Source\Model\Token
 */
class Auth
{
    /**
     * @var
     */
    protected $currentUser;

    /**
     * @var array
     */
    protected $sessions;

    /**
     * Auth constructor.
     *
     * @param User $user
     */
    public function __construct(User $user)
    {
        $this->currentUser = $user;
    }

    /**
     * @return User
     */
    public function getCurrentUser() : User
    {
        return $this->currentUser;
    }

    /**
     * @return array
     */
    protected function getSessions() : array
    {
        if (!isset($this->sessions)) {
            $meta = $this->currentUser->createObjectMeta();
            $this->sessions = $meta
                ->getUserMetaValue('session_login', $this->currentUser);
            if (!is_array($this->sessions)) {
                $this->sessions = [];
            }
        }

        $sessions = array_map([$this, 'prepareSession'], $this->sessions);
        return array_filter($sessions, [$this, 'isStillValid']);
    }

    /**
     * @param mixed $session
     *
     * @return array
     */
    protected function prepareSession($session) : array
    {
        if (is_int($session)) {
            return [
                'expiration' => $session
            ];
        }

        return $session;
    }

    /**
     * @param array $session
     *
     * @return bool
     */
    protected function isStillValid(array $session) : bool
    {
        return is_array($session) && isset($session['expiration'])
            && $session['expiration'] >= time();
    }

    /**
     * @param string $name
     *
     * @return mixed|null
     */
    protected function getSession(string $name)
    {
        $session = $this->getSessions();
        return isset($session[$name]) ? $session[$name] : null;
    }

    /**
     * @param string $verifier
     * @param null|array $session
     */
    protected function updateSession(string $verifier, $session = null)
    {
        $sessions = $this->getSessions();
        if ($session) {
            $sessions[$verifier] = $session;
        } else {
            unset($sessions[$verifier]);
        }

        $this->updateSessions($sessions);
    }

    /**
     * @param array $sessions
     */
    protected function updateSessions($sessions)
    {
        $meta = $this->currentUser->createObjectMeta();
        if (!empty($sessions)) {
            $this->sessions = $sessions;
            $meta->update('session_login', $sessions, $this->currentUser);
        } else {
            $this->sessions = [];
            $meta->delete('session_login', $this->currentUser);
        }
    }

    /**
     * @param string $verifier
     */
    protected function destroyOtherSession(string $verifier)
    {
        $session = $this->getSession($verifier);
        $this->updateSessions([$verifier => $session]);
    }

    /**
     * Destroy All Session
     */
    public function destroyAllSession()
    {
        $this->updateSessions([]);
    }

    /**
     * @param string $token
     *
     * @return string
     */
    public function hash(string $token) : string
    {
        return hash('sha256', $token);
    }

    /**
     * @param string $token
     *
     * @return mixed|null
     */
    final public function get(string $token)
    {
        $verifier = $this->hash($token);
        return $this->getSession($verifier);
    }

    /**
     * @param string $token
     *
     * @return bool
     */
    final public function verify(string $token) : bool
    {
        $verifier = $this->hash($token);
        return (bool) $this->getSession($verifier);
    }

    /**
     * @param string $token
     * @param array $session
     */
    final public function update(string $token, array $session)
    {
        $verifier = $this->hash($token);
        $this->updateSession($verifier, $session);
    }

    /**
     * @param int $expiration
     *
     * @return string
     */
    final public function create(int $expiration)
    {
        $session['expiration'] = $expiration;
        // IP address.
        if (!empty($_SERVER['REMOTE_ADDR'])) {
            $session['ip'] = $_SERVER['REMOTE_ADDR'];
        }
        // User-agent.
        if (! empty($_SERVER['HTTP_USER_AGENT'])) {
            $session['ua'] = $_SERVER['HTTP_USER_AGENT'];
        }

        // Timestamp
        $session['login'] = time();
        $token = $this->generateRandom();
        $this->update($token, $session);
        return $token;
    }

    /**
     * @return array
     */
    final public function getAll() : array
    {
        return $this->getSessions();
    }

    /**
     * @return string
     */
    private function generateRandom() : string
    {
        $chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
        $random = '';
        for ($i = 0; $i < 43; $i++) {
            $random .= substr($chars, rand(0, strlen($chars) - 1), 1);
        }

        return $random;
    }
}
