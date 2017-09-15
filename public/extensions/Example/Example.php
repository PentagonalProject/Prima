<?php
declare(strict_types=1);

namespace PentagonalProject\Prima\Web\Extension;

use PentagonalProject\Prima\App\Source\Extension;
use PentagonalProject\SlimService\Hook;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface;

/**
 * Class Example
 * @package PentagonalProject\Prima\Web\Extension
 */
class Example extends Extension
{
    /**
     * @var string extension name
     */
    protected $modular_name = 'Example Extension';

    /**
     * @var string module uri
     */
    protected $modular_uri = 'https://www.pentagonal.org';

    /**
     * @var string author name
     */
    protected $modular_author = 'Pentagonal';

    /**
     * @var string extension author url
     */
    protected $modular_author_uri = 'https://www.pentagonal.org';

    /**
     * @var string extension description
     */
    protected $modular_description = 'Module Description';

    /**
     * @var string extension version
     */
    protected $modular_version = '1.0';

    /**
     * {@inheritdoc}
     */
    public function init()
    {
        // do init on first activated
        /**
         * @var Hook $hook
         */
        $hook = $this->getContainer()['hook'];
        $hook->add('after.extensions.loaded', [$this, 'hook'], 10, 5);
        // end of application
        $hook->add('after.response.hook', function (ResponseInterface $response) {
            // do after end of response
        }, 10, 1);
    }

    /**
     * @param ContainerInterface $container
     * @param array $currentHookedExtension
     * @param array $invalidExtension
     * @param array $originalExtensionFromDatabase
     * @param array $currentLoadedExtensions
     */
    public function hook(
        ContainerInterface $container,
        array $currentHookedExtension,
        array $invalidExtension,
        array $originalExtensionFromDatabase,
        array $currentLoadedExtensions
    ) {
        // do process after all extensions loaded
    }
}
