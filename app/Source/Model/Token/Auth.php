<?php
/**
 * MIT License
 *
 * Copyright (c) 2017, Pentagonal
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in all
 * copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NON INFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE
 * SOFTWARE.
 */

declare(strict_types=1);

namespace PentagonalProject\Prima\App\Source\Model\Token;

use PentagonalProject\Prima\App\Source\Model\User;

/**
 * Class Auth
 * @package PentagonalProject\Prima\App\Source\Model\Token
 */
class Auth
{
    const AN_HOUR = 3600;

    /**
     * @var User
     */
    protected $user;

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
        $this->user = $user;
    }

    /**
     * @return User
     */
    public function getUser() : User
    {
        return $this->user;
    }

    /**
     * @return array
     */
    protected function getSessions() : array
    {
        if (!isset($this->sessions)) {
            $meta = $this->user->createObjectMeta();
            $this->sessions = $meta
                ->getUserMetaValue('session_login', $this->user);
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
     * @param string $token
     *
     * @return mixed|null
     */
    public function keep(string $token)
    {
        $session = $this->get($token);
        if ($session && $this->isStillValid($session)) {
            // check if it almost expired
            if (($session['expiration'] + (self::AN_HOUR * 2)) <= time()) {
                $session['expiration'] += self::AN_HOUR;
                $this->update($token, $session);
            }
        }

        return $session;
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
        $meta = $this->user->createObjectMeta();
        if (!empty($sessions)) {
            $this->sessions = $sessions;
            $meta->update('session_login', $sessions, $this->user);
        } else {
            $this->sessions = [];
            $meta->delete('session_login', $this->user);
        }
    }

    /**
     * @param string $verifier
     */
    public function destroyOtherSession(string $verifier)
    {
        $session = $this->getSession($verifier);
        $this->updateSessions([$verifier => $session]);
    }

    /**
     * @param string $verifier
     */
    public function destroyCurrentSession(string $verifier)
    {
        $session = $this->getSessions();
        if (isset($session[$verifier])) {
            unset($session[$verifier]);
            $this->updateSessions($session);
        }
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
        $token = $this->generateRandom43();
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
    private function generateRandom43() : string
    {
        $chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
        $random = '';
        for ($i = 0; $i < 43; $i++) {
            $random .= substr($chars, rand(0, strlen($chars) - 1), 1);
        }

        return $random;
    }
}
