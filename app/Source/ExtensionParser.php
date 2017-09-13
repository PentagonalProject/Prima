<?php
namespace PentagonalProject\Prima\App\Source;

use PentagonalProject\SlimService\ModularParser;

/**
 * Class ExtensionParser
 * @package PentagonalProject\Prima\App\Source
 */
class ExtensionParser extends ModularParser
{
    /**
     * @var string
     */
    protected $modularClass = Extension::class;

    /**
     * @var string
     */
    protected $name = 'extension';
}
