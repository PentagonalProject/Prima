<?php
namespace PentagonalProject\Prima\App\Source;

use PentagonalProject\SlimService\ThemeCollection;

/**
 * Class ThemeAdminCollection
 * @package PentagonalProject\Prima\App\Source
 */
class ThemeAdminCollection extends ThemeCollection
{
    /**
     * @var array
     */
    protected $mustBeExists = [
        self::FILE_INFO => self::INVALID_REASON_INFO_NOT_EXISTS,
        '401.phtml' => self::INVALID_REASON_INCOMPLETE,
        'index.phtml' => self::INVALID_REASON_INCOMPLETE,
        'login.phtml' => self::INVALID_REASON_INCOMPLETE,
        'users.phtml' => self::INVALID_REASON_INCOMPLETE,
        'profile.phtml' => self::INVALID_REASON_INCOMPLETE,
        'header.phtml' => self::INVALID_REASON_INCOMPLETE,
        'footer.phtml' => self::INVALID_REASON_INCOMPLETE,
    ];
}
