<?php
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
     * @var array
     */
    protected $routeParams = [];

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
