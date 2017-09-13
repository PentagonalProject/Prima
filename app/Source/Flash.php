<?php
namespace PentagonalProject\Prima\App\Source;

use PentagonalProject\SlimService\Session;

/**
 * Class Flash
 * @package PentagonalProject\Prima\App\Source
 */
class Flash
{
    /**
     * @var Session
     */
    protected $session;

    /**
     * Flash constructor.
     *
     * @param Session $session
     */
    public function __construct(Session $session)
    {
        $this->session = $session;
    }

    /**
     * Set Flash
     *
     * @param string $name
     * @param mixed  $value
     */
    public function set($name, $value)
    {
        $this->session->setFlash($name, $value);
    }

    /**
     * Get Flash
     *
     * @param string $name
     * @param mixed  $default
     * @return mixed
     */
    public function get($name, $default = null)
    {
        return $this->session->getFlash($name, $default);
    }

    /**
     * Check Flash Existences
     *
     * @param string $name
     * @return bool
     */
    public function has($name)
    {
        return $this->session->getFlash($name, null) !== null && $this->session->getFlash($name, true) !== true;
    }

    /**
     * Keeping The Flash
     */
    public function keep()
    {
        $this->session->keepFlash();
    }

    /**
     * @param string $name
     * @param null $default
     * @return mixed
     */
    public function next($name, $default = null)
    {
        return $this->session->getFlashNext($name, $default);
    }

    /**
     * @param string $name
     * @param mixed $value
     */
    public function now($name, $value)
    {
        return $this->session->flashNow($name, $value);
    }

    /**
     * Clear Flash
     */
    public function clear()
    {
        $this->session->clearFlash();
    }

    /**
     * Clear Flash
     */
    public function clearNow()
    {
        $this->session->clearFlashNow();
    }
}
