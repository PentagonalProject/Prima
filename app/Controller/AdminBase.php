<?php
namespace PentagonalProject\Prima\App\Controller;

use PentagonalProject\Prima\App\Source\Model\BaseController;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Slim\Http\Response;

/**
 * Class AdminBase
 * @package PentagonalProject\Prima\App\Controller
 */
class AdminBase extends BaseController
{
    const THEME_CONTAINER =  'theme.admin';

    const PREFIX_NAME   = 'admin';
    const GROUP_PATTERN = '/manage';
    const LOGIN_PATH    = '/login';
    const LOGOUT_PATH   = '/logout';

    /**
     * Initial
     */
    protected function init()
    {
        // theme admin
    }

    /**
     * @param ResponseInterface $response
     *
     * @return ResponseInterface
     */
    protected function redirectLogin(ResponseInterface $response) : ResponseInterface
    {
        /**
         * @var Response $response
         */
        return $response->withRedirect(self::GROUP_PATTERN . self::LOGIN_PATH);
    }

    /**
     * @param ServerRequestInterface $request
     * @param ResponseInterface $response
     *
     * @return ResponseInterface
     */
    public function indexController(
        ServerRequestInterface $request,
        ResponseInterface $response
    ) : ResponseInterface {
        if (!$this->isLogin()) {
            return $this->redirectLogin($response);
        }

        return $this->render($request, $response, 'index', 'Dashboard');
    }

    /**
     * @param ServerRequestInterface $request
     * @param ResponseInterface $response
     *
     * @return ResponseInterface|static
     */
    public function loginController(
        ServerRequestInterface $request,
        ResponseInterface $response
    ) : ResponseInterface {
        /**
         * @var Response $response
         */
        if ($this->isLogin()) {
            return $response->withRedirect(self::GROUP_PATTERN, 302);
        }

        return $this->render(
            $request,
            $response,
            'login',
            'Login To Member Area'
        );
    }
}
