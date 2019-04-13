<?php

namespace Core;

use Core;

class FrontController
{
    static public $dir = '';

    /**
     * function of parse url
     * @param string $route
     * @return false|string
     */
    static public function init(string $route)
    {
        self::$dir = \Core::$ROOT . '/modules/';
        if (empty($route)) {
            $_GET['_module'] = 'main';
            $_GET['_page'] = 'main';
            if (is_file(self::$dir . '/main/sitemap/sitemap.php')) {
                \Core::$SITEMAP = require self::$dir . '/main/sitemap/sitemap.php';
            }
        } else {
            $i = 0;
            $temp = explode('/', $route);

            if (isset($temp[$i]) && isset(\Core::$SECTIONS[$temp[$i]])) {
                self::$dir .= $temp[$i] . '/';
                $_GET['_section'] = $temp[$i++];
            }

            if (empty($temp[$i])) $temp[$i] = 'main';
            if (is_file(self::$dir . $temp[$i] . '/sitemap/sitemap.php')) {
                \Core::$SITEMAP[$temp[$i]] = require self::$dir . $temp[$i] . '/sitemap/sitemap.php';
            }

            if (!isset($temp[$i])) {
                $_GET['_module'] = 'main';
                $_GET['_page'] = 'main';
            } elseif (in_array($temp[$i], \Core::$SITEMAP['single'])) {
                $_GET['_module'] = 'static';
                $_GET['_page'] = $temp[$i++];
            } else {
                $temp[$i] = (string)$temp[$i];
                if (!isset(\Core::$SITEMAP[$temp[$i]]) || !preg_match('#^[a-z0-9_-]+$#ius', $temp[$i])) {
                    self::set404();
                } else {
                    $_GET['_module'] = $temp[$i++];
                    if (isset(\Core::$SITEMAP[$_GET['_module']][$temp[$i]])) {
                        $_GET['_page'] = (string)$temp[$i++];
                    } else {
                        $key = key(\Core::$SITEMAP[$_GET['_module']]);
                        $_GET['_page'] = ($key || $key === 0 ? $key : 'main');
                    }
                }

                page404:
                if (isset(\Core::$SITEMAP[$_GET['_module']][$_GET['_page']]) && is_array(\Core::$SITEMAP[$_GET['_module']][$_GET['_page']])) {
                    foreach (Core::$SITEMAP[$_GET['_module']][$_GET['_page']] as $k => $v) {
                        if (!isset($temp[$i])) {
                            if (!empty($v['req'])) {
                                self::set404();
                                goto page404;
                            } elseif (isset($v['default'])) {
                                $_GET[$k] = $v['default'];
                            }
                        } elseif ($k === '...') {
                            $tmp_key = 1;
                            do {
                                $_GET['key' . $tmp_key++] = $temp[$i];
                            } while (isset($temp[++$i]));
                            unset($tmp_key);
                            break;
                        } else {
                            if (!empty($v['req']) && empty($temp[$i])) {
                                self::set404();
                                goto page404;
                            }
                            if (!isset($v['type'])) {
                                $temp[$i] = (string)$temp[$i];
                            } else {
                                if ($v['type'] == 'string') $temp[$i] = (string)$temp[$i];
                                elseif ($v['type'] == 'int') $temp[$i] = (int)$temp[$i];
                                elseif ($v['type'] == 'array') $temp[$i] = (array)$temp[$i];
                                elseif ($v['type'] == 'boolean') $temp[$i] = (boolean)$temp[$i];
                                else {
                                    self::set404();
                                    goto page404;
                                }
                            }
                            if (isset($v['rules']) && !preg_match('#^' . $v['rules'] . '$#ius', $temp[$i])) {
                                self::set404();
                                goto page404;
                            }
                            $_GET[$k] = $temp[$i];
                        }
                        ++$i;
                    }
                }
            }
            if (isset($temp[$i])) {
                self::set404();
            }
            unset($temp, $key);
        }
        return self::getContentPage();
    }

    /**
     * set route for page 404
     */
    static protected function set404()
    {
        self::$dir = \Core::$ROOT . '/modules/';
        $_GET['_module'] = 'static';
        $_GET['_page'] = '404';
    }

    /**
     * get the full content of the page by route
     * @return false|string
     */
    static protected function getContentPage()
    {
        self::$dir .= $_GET['_module'];
        ob_start();
        if (is_file(self::$dir . '/controller/controller.php')) {

            require self::$dir . '/controller/controller.php';

        } else {

            if (is_file(self::$dir . '/_allpages.php')) {
                require self::$dir . '/_allpages.php';
            }

            require self::$dir . '/' . $_GET['_page'] . '.php';
            self::$dir .= '/view';

            if (is_file(self::$dir . '/_before.tpl')) require self::$dir . '/_before.tpl';

            if (is_file(self::$dir . '/css/' . $_GET['_page'] . '.min.css')) {
                \Core::addCss(self::$dir . '/css/' . $_GET['_page'] . '.min.css');
            } elseif (is_file(self::$dir . '/css/' . $_GET['_page'] . '.css')) {
                \Core::addCss(self::$dir . '/css/' . $_GET['_page'] . '.css');
            }

            if (is_file(self::$dir . '/js/' . $_GET['_page'] . '.js')) {
                \Core::addJs(self::$dir . '/js/' . $_GET['_page'] . '.js');
            }

            if (is_file(self::$dir . '/' . $_GET['_page'] . '.tpl')) {
                require self::$dir . '/' . $_GET['_page'] . '.tpl';
            }

            if (is_file(self::$dir . '/_after.tpl')) require self::$dir . '/_after.tpl';
        }
        return ob_get_clean();
    }
}