<?php
namespace PentagonalProject\Prima\App\Source;

use Slim\Http\Cookies;

/**
 * Class CookieSession
 * @package PentagonalProject\Prima\App\Source
 *
 * @method void set(string $name, string|array $value)
 * @method mixed get(string $name, $default = null)
 * @method void setDefaults(array $settings)
 * @method string[] toHeaders()
 * // static method
 * @method static array parseHeader(string $header)
 */
class CookieSession
{
    /**
     * @var Cookies
     */
    protected $cookies;

    /**
     * CookieSession constructor.
     *
     * @param Cookies $cookies
     */
    public function __construct(Cookies $cookies)
    {
        $this->cookies = $cookies;
    }

    /**
     * Set Cookie
     *
     * @param string $name
     * @param string $value
     * @param int $expires
     * @param string $path
     * @param null $domain
     * @param bool $secure
     * @param bool $httpOnly
     * @param bool $hostOnly
     */
    public function setCookie(
        string $name,
        string $value,
        $expires = 0,
        $path = null,
        $domain = null,
        $secure = false,
        $httpOnly = false,
        $hostOnly = false
    ) {
        $this->cookies->set(
            $name,
            [
                'value' => $value,
                'expires' => $expires,
                'path' => $path,
                'domain' => $domain,
                'secure' => $secure,
                'httponly' => $httpOnly,
                'hostonly' => $hostOnly,
            ]
        );
    }

    /**
     * Magic Method
     *
     * @param string $name
     * @param array $arguments
     *
     * @return mixed
     */
    public function __call(string $name, array $arguments)
    {
        return call_user_func_array([$this->cookies, $name], $arguments);
    }

    /**
     * Magic Method
     * @param string $name
     * @param array $arguments
     *
     * @return mixed
     */
    public static function __callStatic(string $name, array $arguments)
    {
        return call_user_func_array([Cookies::class, $name], $arguments);
    }
}
