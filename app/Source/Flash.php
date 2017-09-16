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
