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

use PentagonalProject\SlimService\PropertyHookAble;
use \PentagonalProject\SlimService\Theme as ThemeService;

/**
 * Class Theme
 * @package PentagonalProject\Prima\App\Source
 */
class Theme extends ThemeService implements \ArrayAccess
{
    const PREFIX_PROPERTY = PropertyHookAble::PREFIX;

    /**
     * @var bool
     */
    public $isLoginPage = false;

    /**
     * @var bool
     */
    public $isRegisterPage = false;

    /**
     * @var bool
     */
    public $isForgotPage = false;

    /**
     * @var array
     */
    protected $routeParams = [];

    /**
     * @return bool
     */
    public function isLoginPage() : bool
    {
        return $this->isLoginPage;
    }

    /**
     * @return bool
     */
    public function isRegisterPage() : bool
    {
        return $this->isRegisterPage;
    }

    /**
     * @return bool
     */
    public function isForgotPage() : bool
    {
        return $this->isForgotPage;
    }

    /**
     * @param array $params
     */
    public function setRouteParams(array $params)
    {
        $this->routeParams = $params;
    }

    /**
     * @return array
     */
    public function getRouteParams() : array
    {
        return $this->routeParams;
    }

    /**
     * @param mixed $offset
     *
     * @return bool
     */
    public function hasProperty(string $offset) : bool
    {
        /**
         * @var PropertyHookAble $prop
         */
        $prop = $this->getContainer()['hook.property'];
        return  $prop->has($offset);
    }

    /**
     * @param mixed $offset
     * @param mixed $value
     */
    public function setProperty(string $offset, $value)
    {
        /**
         * @var PropertyHookAble $prop
         */
        $prop = $this->getContainer()['hook.property'];
        call_user_func_array([$prop, 'set'], func_get_args());
    }

    /**
     * @param mixed $offset
     */
    public function removeProperty(string $offset)
    {
        /**
         * @var PropertyHookAble $prop
         */
        $prop = $this->getContainer()['hook.property'];
        $prop->remove($offset);
    }

    /**
     * @param string $name
     * @param null $default
     *
     * @return mixed
     */
    public function getProperty(string $name, $default = null)
    {
        /**
         * @var PropertyHookAble $prop
         */
        $prop = $this->getContainer()['hook.property'];
        return $prop->get($name, $default);
    }

    /**
     * @param string $name
     * @param null $default
     * @param array ...$params
     *
     * @return mixed
     */
    public function getOrApplyProperty(string $name, $default = null, ...$params)
    {
        /**
         * @var PropertyHookAble $prop
         */
        $prop = $this->getContainer()['hook.property'];
        return call_user_func_array([$prop, 'getOrApply'], func_get_args());
    }

    /**
     * @param mixed $offset
     *
     * @return bool
     */
    public function offsetExists($offset) : bool
    {
        return $this->hasProperty($offset);
    }

    /**
     * @param mixed $offset
     *
     * @return mixed
     */
    public function offsetGet($offset)
    {
        return $this->getProperty($offset);
    }

    /**
     * @param mixed $offset
     * @param mixed $value
     */
    public function offsetSet($offset, $value)
    {
        $this->setProperty($offset, $value);
    }

    /**
     * @param mixed $offset
     */
    public function offsetUnset($offset)
    {
        $this->removeProperty($offset);
    }
}
