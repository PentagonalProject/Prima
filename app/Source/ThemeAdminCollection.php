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
        '401.phtml'     => self::INVALID_REASON_INCOMPLETE,
        'index.phtml'   => self::INVALID_REASON_INCOMPLETE,
        'header.phtml'  => self::INVALID_REASON_INCOMPLETE,
        'footer.phtml'  => self::INVALID_REASON_INCOMPLETE,
        'profile.phtml'  => self::INVALID_REASON_INCOMPLETE,

        // action
        'login.phtml'   => self::INVALID_REASON_INCOMPLETE,
        'register.phtml'   => self::INVALID_REASON_INCOMPLETE,
        'forgot.phtml'   => self::INVALID_REASON_INCOMPLETE,

        // manage
        'manage/extensions.phtml' => self::INVALID_REASON_INCOMPLETE,
        'manage/themes.phtml'   => self::INVALID_REASON_INCOMPLETE,
        'manage/posts.phtml'    => self::INVALID_REASON_INCOMPLETE,
        'manage/settings.phtml'    => self::INVALID_REASON_INCOMPLETE,
        'manage/users.phtml'    => self::INVALID_REASON_INCOMPLETE,

        // edit
        'edit/post.phtml'    => self::INVALID_REASON_INCOMPLETE,
        'edit/user.phtml'    => self::INVALID_REASON_INCOMPLETE,
    ];
}
