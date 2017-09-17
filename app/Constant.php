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


/*! ------------------------------------------
 * BASE
 * -------------------------------------------
 */
define('WEB_DIR', dirname($_SERVER['SCRIPT_FILENAME']));
define('APP_DIR', __DIR__);

/*! ------------------------------------------
 * BEFORE
 * -------------------------------------------
 */

// loaded
define('HOOK_BEFORE_LOAD_DB', 'before.db.loaded');
define('HOOK_BEFORE_LOAD_COOKIE', 'before.cookie.loaded');
define('HOOK_BEFORE_LOAD_EXTENSION', 'before.extension.loaded');
define('HOOK_BEFORE_LOAD_SESSION', 'before.session.loaded');

# Theme
define('HOOK_BEFORE_LOAD_THEMES', 'before.themes.loaded');
define('HOOK_BEFORE_LOAD_THEME', 'before.theme.loaded');
define('HOOK_BEFORE_LOAD_THEMES_ADMIN', 'before.themes.admin.loaded');
define('HOOK_BEFORE_LOAD_THEME_ADMIN', 'before.theme.admin.loaded');
define('HOOK_BEFORE_LOAD_TEMPLATE', 'before.template.loaded');
define('HOOK_BEFORE_LOAD_TEMPLATE_ADMIN', 'before.template.admin.loaded');
define('HOOK_BEFORE_LOAD_CURRENT_USER', 'before.current.user.loaded');

# active
define('HOOK_BEFORE_ACTIVE_EXTENSIONS', 'before.extensions.active');

# misc
define('HOOK_BEFORE_RESPONSE_HOOK', 'before.response.hook');


/*! ------------------------------------------
 * AFTER
 * -------------------------------------------
 */

// loaded
define('HOOK_AFTER_LOAD_DB', 'after.db.loaded');
define('HOOK_AFTER_LOAD_COOKIE', 'after.cookie.loaded');
define('HOOK_AFTER_LOAD_EXTENSION', 'after.extension.loaded');
define('HOOK_AFTER_LOAD_SESSION', 'after.session.loaded');

# Theme
define('HOOK_AFTER_LOAD_THEMES', 'after.themes.loaded');
define('HOOK_AFTER_LOAD_THEME', 'after.theme.loaded');
define('HOOK_AFTER_LOAD_THEMES_ADMIN', 'after.themes.admin.loaded');
define('HOOK_AFTER_LOAD_THEME_ADMIN', 'after.theme.admin.loaded');
define('HOOK_AFTER_LOAD_CURRENT_USER', 'after.current.user.loaded');

# active
define('HOOK_AFTER_ACTIVE_EXTENSIONS', 'after.extensions.active');

# misc
define('HOOK_AFTER_RESPONSE_HOOK', 'after.response.hook');
define('HOOK_AFTER_RESPONSE_BUFFER', 'after.response.buffer');


/*! ------------------------------------------
 * DIRECTORY
 * -------------------------------------------
 */

define('HOOK_DIRECTORY_EXTENSIONS', 'dir.extension');
define('HOOK_DIRECTORY_THEMES', 'dir.themes');
define('HOOK_DIRECTORY_THEMES_ADMIN', 'dir.themes.admin');


/*! ------------------------------------------
 * ACTIVE
 * -------------------------------------------
 */

define('HOOK_ACTIVE_THEME', 'active.theme');
define('HOOK_ACTIVE_THEME_ADMIN', 'active.theme.admin');
define('HOOK_ACTIVE_EXTENSIONS', 'active.extensions');


/*! ------------------------------------------
 * DEFAULT
 * -------------------------------------------
 */

define('HOOK_DEFAULT_TITLE', 'default.title');
define('HOOK_DEFAULT_TITLE_LOGIN', 'default.title.login');
define('HOOK_DEFAULT_TITLE_ADMIN', 'default.title.admin');
define('HOOK_DEFAULT_TITLE_REGISTER', 'default.title.register');
define('HOOK_DEFAULT_TITLE_FORGOT', 'default.title.forgot');


/*! ------------------------------------------
 * DEFAULT
 * -------------------------------------------
 */

define('HOOK_ALLOW_REGISTER', 'allow.register');
define('HOOK_ALLOW_FORGOT', 'allow.forgot');


/*! ------------------------------------------
 * MISC
 * -------------------------------------------
 */

define('HOOK_INIT_MIDDLEWARE', 'init.middleware');
define('HOOK_LAST_MIDDLEWARE', 'last.middleware');
define('HOOK_GROUP_ROUTE_MIDDLEWARE', 'route.group.middleware');
define('PREFIX_SESSION_COOKIE', 'prefix.session.cookie');
define('HOOK_RESPONSE', 'response');
