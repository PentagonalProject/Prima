<?php
namespace PentagonalProject\Prima\App\Controller;

use PentagonalProject\Prima\App\Source\Model\BaseController;
use PentagonalProject\Prima\App\Source\Model\Option;
use PentagonalProject\SlimService\Hook;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

/**
 * Class PublicBase
 * @package PentagonalProject\Prima\App\Controller
 */
class PublicBase extends BaseController
{
    const THEME_CONTAINER =  'theme';
    const PREFIX_NAME = 'public';
    const GROUP_PATTERN = '';

    /**
     * Initial
     */
    protected function init()
    {
        // init
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
        /**
         * @var Hook $hook
         * @var Option $option
         */
        $hook = $this->container['hook'];
        $option = $this->container['option'];
        $defaultTitle = 'Welcome To Our Site';
        $title = $option->getOrUpdate('site.title', $defaultTitle);
        if (!is_string($title)) {
            $title = $defaultTitle;
            $option->update('site.title', $title);
        }

        $title = $hook->apply('default.title', $title);
        return $this->render($request, $response, 'index', $title);
    }
}
