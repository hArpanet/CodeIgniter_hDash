<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * hArpanet Dashboard Library (hDash) for CodeIgniter v2.1.3+
 *
 * Now (mostly) PSR-2 Compliant
 * (https://github.com/php-fig/fig-standards/blob/master/accepted/PSR-2-coding-style-guide.md)
*
 * Description:
 * This library allows easy creation of a 'dashboard' style, multi-column page
 * containing multiple boxes (widgets) of content. Widget content can come from
 * another Controller method, a flat File, or Image file.
 *
 * Install this file as application/third_party/hArpanet/hDash/hdash.php
 *
 * @author    hArpanet.com <ci_hdash@harpanet.com>
 * @copyright 2013 hArpanet dot com
 * @license   http://www.gnu.org/licenses/gpl-2.0.html (GNU General Public License)
 * @version   1.2, 02-Feb-2014
 * @link      http://harpanet.com/programming/php/codeigniter/dashboard
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation version 2.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 * THE SOFTWARE.
 **/

define('PS', '/');    // path separator

/**
 * Dashboard controller class to display multiple widgets of content
 *
 * @category   Dashboard
 * @package    CodeIgniter
 * @subpackage Library
 * @author     hArpanet.com <hdash@harpanet.com>
 * @link       http://harpanet.com/programming/php/codeigniter/dashboard
 */
class hDash
{
    /*
     * This function is purely to show an example of a dashboard method to place
     * within your own controller. Just copy the entire dash_test() method into
     * your existing controller class and call it as normal:
     *
     *     $mydash = $this->dash_test();
     *
     * @return string HTML string containing entire dashboard code
     */
    public function dash_test()
    {
        //
        // 1. LOAD THIRD_PARTY HARPANET DASHBOARD LIBRARY
        //    and assign to $hdash
        //

        $this->load->add_package_path(APPPATH.'third_party/hArpanet/hDash/');
        $this->load->library('hdash'); $hdash =& $this->hdash;
        $this->load->remove_package_path(APPPATH.'third_party/hArpanet/hDash/');

        //
        // DEFAULT PATH OF WIDGET RESOURCES IS /assets/...
        // but we can change it if required...
        //
        // $hdash->asset_path = '/somepath/assets';   // no trailing slash

        //
        // 2. CONSTRUCT DASHBOARD CONTENT ARRAY
        //    adding widgets individually for clarity
        //

        $hdash->widgets[] = array(
                                'cols'  => 3,
                                'title' => 'JUST A TITLE BAR, no content',
                                );

        $hdash->widgets[] = array(
                                'type'  => 'html',
                                'src'   => '<h4>this is widget one</h4>',
                                'title' => 'A dashboard widget'
                                );

        $hdash->widgets[] = array(
                                'cols'=>2,
                                'type'  => 'html',
                                'src'   => '<h4>widget two, no title and two columns</h4>',
                                'title' => FALSE,
                                );

        // images are located (by default) in /assets/img/hDash
        $hdash->widgets[] = array(
                                'type'  => 'img',
                                'src'   => 'hDash_buttons.gif',
                                'title' => 'A dashboard image',
                                );

        // files are located (by default) in /assets/files/hDash
        $hdash->widgets[] = array(
                                'type'  => 'file',
                                'src'   => 'readme.txt',
                                'title' => 'A file widget',
                                );

        //
        // 3. CONSTRUCT THE DASHBOARD HTML
        //

        // activate modal windows (before building dashboard)
        // $hdash->modal = true;

        // add the modal javascript (must be loaded after jQuery)
        // (will not be added if hdash->modal is false)
        $mydash = $hdash->modal_js();

        // now build the dashboard HTML
        $mydash .= $hdash->build();

        return $mydash;
    }



    /* DEFINITIONS */

    private $_dashboard    = array();        // container for HTML dashboard of rendered widgets

    // unused:
    protected $dash_path        = '';        // filesystem path to hDash package folder - not currently used

    // core resource:
    public $asset_path          = 'assets';  // filesystem path to hDash assets folder
    public $css_path            = 'css';     // folder containing dashboard css file         (appended to asset_path)
    public $js_path             = 'js';      // folder containing dashboard javascript files (appended to asset_path)
    // public resource locations:
    public $oop_path            = '';        // filesystem path to widget oop controllers
    public $img_path            = 'img';     // folder path (from webroot) to widget image files
    public $file_path           = 'files';   // filesystem path to widget html/text/etc. files for direct inclusion
    // client preferences:
    public $dash_fldr           = 'hDash';   // folder name for dashboard files
    public $widget_heading      = 'h2';      // HTML tag to use for main widget heading
    public $widget_subheading   = 'h3';      // HTML tag to use for any sub-headings within widgets
    public $cols                = 3;         // number of columns on dashboard (loads related css file)
    public $oop_alt             = false;     // FALSE = do not use alternative file paths for oop files
    public $modal               = false;     // flag use of jQuery to display modal popup windows
    public $modal_notice        = "";        // notice to display to indicate that widgets can be clicked
    // per-dashboard settings:
    public $dash                = '';        // dashboard identifier (set directly (eg. $dash->dash='') or via build($dash) method)
    public $widgets             = array();   // array of dashboard widget definitions (these are set in the initiating controller)


    /**
     * Class Construction
     *
     * @param array $config Unused
     */
    public function __construct($config=array())
    {
        // load hDash config file (if it exists)
        $CI = &get_instance();
        $CI->config->load('hDash', true, true);
        $dconf = $CI->config->item('hDash');

        /*
         * INITIALISE DASHBOARD
         */

        // default values
        // 'dash_path'         => APPPATH.'third_party'.PS.'hArpanet'.PS.'hDash',    // not currently used
        // $this->asset_path = 'assets';                               // not currently use

        $dvals = array( 'asset_path'        => 'assets',
                        'cols'              => 3,
                        'widget_heading'    => 'h2',
                        'widget_subheading' => 'h3',
                        'dash'              => '',
                        'dash_fldr'         => 'hDash',
                        'file_path'         => 'files',        // doesn't strictly need to be in a public loc
                        'css_path'          => 'css',
                        'js_path'           => 'js',
                        'img_path'          => 'img',
                        'oop_alt'           => false,
                        'oop_path'          => APPPATH.'controllers',
                        'modal'             => false,
                        'modal_notice'      => "<div class='modal_notice'>Click a widget title bar or any image to zoom.</div>",
                        );

        // set default values if not specified in config/hDash.php
        foreach ($dvals as $key => $val) {
            // if no config[] entry exists, use default path instead

            // @TODO: READ VALUES FROM $config ARRAY IF THEY HAVE BEEN PASSED

            if (! $this->$key = (is_array($dconf) && array_key_exists($key, $dconf) ? $dconf[$key] : false)) {
                $this->$key = $val;
            }
        }

        log_message('info', 'hDash Third_Party Library Initialised');

        return $this;
    }


    /**
     * Helper function to Return the path of the dashboard image folder.
     * Useful to link directly to large image files within a dashboard.
     *
     * @param string $dash (optional) Name of dashboard being built. If not specified here
     *                     then it MUST have been specified earlier using $dash->dash = '[dashboard name]'
     *
     * @return string      Filepath to dashboard image folder if $dash not empty
     *                     Filepath excluding $dash.PS if $dash is empty
     *
     * @author  hArpanet.com
     * @version 02-Feb-2014
     */
    public function dash_img_path($dash='')
    {
        // clean path vars
        self::_clean_paths();

        // set library values based on params passed
        $this->_setvars( array('dash'=>$dash) );

        return $this->_makepath($this->img_path, $this->dash, $this->dash_fldr);
    }

    /**
     * Helper function to Return the path of the dashboard image folder.
     * Useful to link directly to large image files within a dashboard.
     *
     * @param string $dash (optional) Name of dashboard being built. If not specified here
     *                     then it MUST have been specified earlier using $dash->dash = '[dashboard name]'
     *
     * @return string      Filepath to dashboard image folder if $dash not empty
     *                     Filepath excluding $dash.PS if $dash is empty
     *
     * @author  hArpanet.com
     * @version 02-Feb-2014
     */
    public function dash_file_path($dash='')
    {
        // clean path vars
        self::_clean_paths();

        // set library values based on params passed
        $this->_setvars( array('dash'=>$dash) );

        return $this->_makepath($this->file_path, $this->dash, $this->dash_fldr);
    }


    /**
     * Display the dashboard
     *
     * @param string $dash (optional) If specified, will set the name of the dashboard to display
     *
     * @return srray       Two elements ('widgets' and 'modal') containing HTML
     *
     * @author  hArpanet.com
     * @version 02-Feb-2014
     */
    public function build($dash='')
    {
        // init vars
        $widget_html = $modal_html = '';
        $css_path = $this->_makepath($this->asset_path, $this->css_path, $this->dash_fldr);

        // clean path vars
        self::_clean_paths();

        // set library values based on params passed
        $this->_setvars( array('dash'=>$dash) );

        // render all the parts
        self::_render();

        // handle modal windows if enabled
        if ($this->modal) {
            $widget_html .= $this->modal_notice;

            // add a modal container to display popups
            $modal_html = "<div id='modal-container'><div id='modal-body'></div></div>";
            // and the client-side modal css
            $modal_html .= "<link rel='stylesheet' type='text/css' href='".$css_path."hModal.css' />";
        }

        // build the widgets html
        foreach ($this->_dashboard as $widget) {
            $widget_html .= $widget;
        }

        $widget_html .= "<link rel='stylesheet' type='text/css' href='".$css_path."hDash.css' />";
        $widget_html .= "<link rel='stylesheet' type='text/css' href='".$css_path."hDash_".$this->cols."col.css' />";

        return $widget_html.$modal_html;
    }


    /**
     * Generate HTML code for including the hModal.js file
     *
     * @return string HTML code to load hModal.js
     *
     * @author  hArpanet.com
     * @version 02-Feb-2014
     */
    public function modal_js() {

        // hModal.js has a jQuery(document).ready(function() method, so we can't auto-include it as jQuery may not be loaded at the
        // point where the dashboard is displayed.

        if ($this->modal === false) return '';

        return "<script type='text/javascript' src='".$this->_makepath($this->asset_path, $this->js_path, $this->dash_fldr)."hModal.js'></script>";

    }

    /**
     * Traverse the $widgets array and build Dashboard from HTML widgets
     *
     * @return string HTML widget content
     *
     * @author  hArpanet.com
     * @version 02-Feb-2014
     */
    private function _render()
    {
        $content = '';

        if (is_array($this->widgets)) {
            // clean path vars
            self::_clean_paths();

            foreach ($this->widgets as $parts) {

                $content = '';

                $type    = (array_key_exists('type',  $parts)) ? $parts['type'] : '';
                $src     = (array_key_exists('src',   $parts)) ? $parts['src'] : '';
                $title   = (array_key_exists('title', $parts)) ? $parts['title'] : '';
                $alt     = (array_key_exists('alt',   $parts)) ? $parts['alt'] : 1;
                $cols    = (array_key_exists('cols',  $parts)) ? $parts['cols'] : 1;

                // process action commands (these don't follow the convention of standard widgets)
                switch ($type) {
                    case 'clear':
                        // push content directly into dashboard array
                        $content = "<div style='clear:both'></div>";
                        $this->_dashboard[] = $content;
                        continue 2;
                        break;
                }

                // check if individual keys supplied instead of an array of values
                if (array_key_exists('src', $parts)) {

                    // individual keys supplied so construct appropriate array
                    $parts = array(array('type'=>$type, 'src'=>$src, 'alt'=>$alt));
                }

                // process each part
                foreach ($parts as $part) {

                    if (! is_array($part)) continue;

                    // add any part-specific sub-headings as <H3>
                    $content .= (array_key_exists('title', $part)) ? "<".$this->widget_subheading.">".$part['title']."</".$this->widget_subheading.">" : '';

                    switch ($part['type']) {
                    //======================================
                        case 'oop':
                    //======================================

                            // run an external controller to produce widget contents
                            if ($this->oop_alt) {
                                // alternative location will be:
                                //     [oop_path]/[dashboard_folder]/[dashboard_name]/[controller_name].[ext]
                                // eg. [application/controllers]/[dashboard]/[safety]/[test_dash].[php]
                                $file_name = $this->_makepath($this->oop_path, $this->dash_fldr, $this->dash).$part['src'].EXT;

                            } else {

                                // normal location will be
                                //     [oop_path]/[dashboard_name]/[dashboard_folder]/[controller_name].[ext]
                                // eg. [application/controllers]/[safety]/[dashboard]/[test_dash].[php]
                                $file_name = $this->_makepath($this->oop_path, $this->dash, $this->dash_fldr).$part['src'].EXT;
                            }

                            if (file_exists($file_name)) {

                                include_once $file_name;

                                // create an instance of the controller so we can run it
                                $cname = ucfirst($part['src']);
                                $c = new $cname;

                                // always run the index() method to build content
                                $content .= $c->index();

                            } else {

                                $content .= 'WARNING: Unable to find controller: '.$file_name;
                            }

                            break;

                    //======================================
                        case 'html':
                    //======================================

                            // html or text content being directly supplied from controller
                            $content .= $part['src'];

                            break;

                    //======================================
                        case 'curl':
                    //======================================

                            // html or text content being directly supplied from controller
                            $content .= $this->_curl_response($part['src']);

                            break;

                    //======================================
                        case 'img':
                    //======================================

                            // create an <img> tag widget referencing an external image file
                            // $img_file   = (array_key_exists('src', $part)) ? $this->_makepath($this->asset_path, $this->img_path.PS.$this->dash.PS.$this->dash_fldr.PS.$part['src'] : '';
                            $img_file = (array_key_exists('src', $part)) ? $this->_makepath($this->asset_path, $this->img_path, $this->dash, $this->dash_fldr).$part['src'] : '';
                            $img_alt  = (array_key_exists('alt', $part)) ? $part['alt'] : '';

                            if (file_exists(FCPATH.$img_file)) {

                                $content .= "<img src='".$img_file."' width='100%' alt='{$img_alt}' title='{$img_alt}' class='modalview'  type='image' />";

                            } else {

                                $content = 'WARNING: Unable to find img: '.FCPATH.$img_file;
                            }

                            break;

                    //======================================
                        case 'file':
                    //======================================

                            // pull widget contents directly from an external file
                            $file_name = (array_key_exists('src', $part)) ? $this->_makepath(FCPATH.$this->asset_path, $this->file_path, $this->dash, $this->dash_fldr).$part['src'] : '';

                            if (file_exists($file_name)) {
                                $content .= file_get_contents($file_name);

                            } else {

                                $content .= 'WARNING: Unable to find file: '.$file_name;
                            }

                            break;
                    }
                }

                // add this widget to dashboard
                $this->_dashboard[] = $this->_widget($title, $content, $cols);
            }
        }

        return $content;
    }

    /**
     * Build the widget wrapper and HTML contents
     *
     * @param string $title   Main widget title <h2> (if === FALSE, title region is omitted)
     * @param string $content HTML content to place inside widget
     * @param int    $cols    Number of columns that widget should span
     *
     * @return string         HTML block
     *
     * @author  hArpanet.com
     * @version 02-Feb-2014
     */
    private function _widget($title='', $content='', $cols=1)
    {

        $widget = '<div class="widget_wrapper {width}">';

        if ($title !== false) {

            $modalview = 'modalview';

            // only allow modalview if we have content
            if (empty($content)) $modalview = '';

            $widget .= '<div class="widget_heading '.$modalview.'" type="heading">' .
                       '    <'.$this->widget_heading.'>{title}</'.$this->widget_heading.'>' .
                       '</div>';
        }

        if (!empty($content)) {

            $widget .= '<div class="widget_content">' .
                       '    {content}' .
                       '</div>';
        }

        $widget .= '</div>';

        // populate title and content
        $widget = str_replace('{title}', $title, $widget);
        $widget = str_replace('{content}', $content, $widget);

        // convert widget width to css style
        $wordnums = array('', 'one','two','three','four','five','six','seven','eight','nine');
        $widget   = str_replace('{width}', $wordnums[$cols].'col', $widget);

        return $widget;
    }


    /**
     * Use CURL to call or load a remote page
     *
     * @param string $url      URL to call
     * @param bool   $get_body Flag indicating whether to return body of page
     *                           (set this to FALSE if you just want to 'ping' a remote file)
     * @param int    $status   Response status required for success
     * @param int    $wait     Time (in seconds) to allow for response
     *
     * @return bool            TRUE if page returned a status < 400 OR == $status AND response received within $wait seconds
     *                         FALSE
     *
     * @version    02-Feb-2014
     * @see        http://www.php.net/manual/en/book.curl.php#102885
     */
    private function _curl_response($url, $get_body=true, $status=null, $wait = 5)
    {
        $time = microtime(true);
        $expire = $time + $wait;

        // @TODO Check if pcntl_fork() is available

        // NOTE: COMMENTED OUT IN CASE FORKING NOT CURRENTLY INSTALLED ON PHP SERVER
        // we fork the process so we don't have to wait for a timeout
        // $pid = pcntl_fork();
        // if ($pid == -1) {
        //     die('could not fork');
        // }
        // else if ($pid)
        // {
            // we are the parent
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_HEADER, true);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

            if ($get_body === false) {
                curl_setopt($ch, CURLOPT_NOBODY, true); // remove body content if not requested
            }

            // $result      = ($result = curl_exec($ch)) ? $result : curl_error($ch);
            $result      = curl_exec($ch);
            $httpCode    = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $header_size = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
            $header      = substr($result, 0, $header_size);
            $body        = substr($result, $header_size);
            curl_close($ch);

            // return body content if requested and a result was returned and the code is success
            if ($get_body && $result && $httpCode < 400) {
                // only return the body
                return $body;
            }

            if (! $result) {
                return FALSE;
            }

            if ($status === null) {

                if ($httpCode < 400) {
                    return true;

                } else {
                    return false;
                }

            } elseif ($status == $httpCode) {
                return true;
            }

            return false;
       //     pcntl_wait($status); //Protect against Zombie children
       // }
       // else
       // {
       //     // we are the child
       //     while(microtime(true) < $expire)
       //     {
       //         sleep(0.5);
       //     }
       //     return FALSE;
       // }
    }


    /**
     * Configure Dashboard variables
     *
     * @param array $vars Associative array of var names and values (eg. 'dash'=>'test')
     *
     * @return  void
     */
    private function _setvars($vars=false)
    {
        // set library values based on params passed
        if (is_array($vars)) {
            foreach ($vars as $key => $value) {
                $this->$key = $value;
            }
        }
    }

    /**
     * A small helper function to ensure all paths do not have trailing slashes
     *
     * @return void
     */
    private function _clean_paths()
    {
        $path_vars = array('dash_path', 'asset_path', 'css_path', 'js_path', 'dash_fldr', 'dash', 'img_path', 'file_path', 'oop_path');

        foreach ($path_vars as $var) {
            $this->$var = trim($this->$var);
        }
    }

    /**
     * A small helper function to build a path with separators from passed argument strings
     *
     * @param strimg Multiple string parameters can be passed in - do not use an array
     *
     * @return string Concatenated path
     */
    private function _makepath() {
        $params = func_get_args();
        $path = '';

        foreach ($params as $value) {
            $path .= $value;

            if (!empty($value)) $path .= PS;
        }

        return $path;
    }

}

/* End of file hdash.php */
/* Location: ./application/third_party/hArpanet/hDash/libraries/hdash.php */
