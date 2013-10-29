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
 * @version   1.1, 07-Jun-2013
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
 * @author     hArpanet.com <ci_dash@harpanet.com>
 * @link       http://harpanet.com/programming/php/codeigniter/dashboard
 */
class Dash
{
    private function __example__()
    {
        /*
         * This function is purely to show an example of a dashboard method to place
         * within your own controller.
         */

        // load third_party hArpanet dashboard library
        $this->load->add_package_path(APPPATH.'third_party/hArpanet/hDash/');
        $dash =& $this->load->library('dash');
        $this->load->remove_package_path(APPPATH.'third_party/hArpanet/hDash/');

        // configure dashboard widgets - format: type, src, title, cols, alt (for images)
        $dash->widgets = array(

                    array('type'=>'oop',     'src'=>'test_dash',         'title'=>'Test OOP Widget',    'cols'=>3),

                    // if 'title' is set to FALSE, the title block is omitted entirely
                    // note: this is an 'html' widget but is being fed content from a local method
                    array('type'=>'html',     'src'=>self::test_method(), 'title'=>false,    'cols'=>3),

                    array('type'=>'file',     'src'=>'saf_inv.htm',         'title'=>'Safety Investigation'),

                    // multi-content widget - set widget title in outer array (also note use of CI anchor to create a link)
                    array('title'=>anchor('tz', 'TARGET ZERO'),
                            // sub-content follows same array format as single content widget
                            // 'img' content can also have an 'alt' text
                            array('type'=>'img',    'src'=>'saf_tzout.gif',      'alt'=>'Action Completed'),
                            array('type'=>'file',    'src'=>'saf_tz.htm'),
                            array('type'=>'file',    'src'=>'ave_close.htm',     'title'=>'Average Time to Close')
                            ),

                    array('type'=>'file',    'src'=>'saf_meet.htm',        'title'=>'Safety Meeting'),
                    array('type'=>'file',    'src'=>'saf_acc.htm',        'title'=>'Accident Investigation'),
                    array('type'=>'file',    'src'=>'saf_hazmat.htm',     'title'=>anchor('hazmat', 'HAZMAT')),
                    array('type'=>'file',    'src'=>'saf_cont.htm',         'title'=>'Loss of Containment'),
                    array('type'=>'file',    'src'=>'saf_worksinfo.htm',    'title'=>'Works Information'),

                    // an action widget - 'clear' will generate a blank widget with a style of clear:both
                    array('type'=>'clear'),

                    // multi-content widget - width can be set using the 'cols' param in outer array
                    array('title'=>'RAG Report', 'cols' => 2,

                            array('type'=>'file',    'src'=>'saf_rag.htm'),
                            array('type'=>'img',    'src'=>'ProcSaf.gif')),

                    array('type'=>'file',    'src'=>'saf_chrom.htm',        'title'=>'Chrome checks'),
                );

        // populate the view variable
        $widgets = $dash->build('safety');

        // render the dashboard
        $this->load->view('layout_default', $widgets);

    }

    /* DEFINITIONS */

    private $_dashboard    = array();        // container for HTML dashboard of rendered widgets

    // unused:
    protected $dash_path        = '';        // filesystem path to hDash package folder - not currently used
    protected $asset_path       = 'assets';  // filesystem path to hDash assets folder - not currently used

    // core resource:
    public $css_path            = 'css';     // filesystem path to dashboard css file
    public $js_path             = 'js';      // filesystem path to javascript files
    // client preferences:
    public $dash_fldr           = '';        // folder name for dashboard files
    public $widget_heading      = 'h2';      // HTML tag to use for main widget heading
    public $widget_subheading   = 'h3';      // HTML tag to use for an sub-headings within widgets
    public $cols                = 3;         // number of columns on dashboard (loads related css file)
    public $oop_alt             = false;     // FALSE = do not use alternative file paths for oop files
    // public resource locations:
    public $oop_path            = '';        // filesystem path to dashboard widget controllers
    public $img_path            = '';        // folder path (from webroot) to widget image files
    public $file_path           = '';        // filesystem path to widget html/text/etc. files for direct inclusion
    // per-dashboard settings:
    public $dash                = '';        // dashboard identifier (set directly (eg. $dash->dash='') or via build($dash) method)
    public $widgets             = array();   // array of dashboard widget definitions (these are set by the initiating controller)


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
        $dvals = array( 'dash_path'         => APPPATH.'third_party'.PS.'hArpanet'.PS.'hDash',    // not currently used
                        'asset_path'        => site_url().'assets',                               // not currently used
                        'css_path'          => 'assets'.PS.'css',
                        'js_path'           => 'assets'.PS.'js',
                        'dash_fldr'         => 'hDash',
                        'widget_heading'    => 'h2',
                        'widget_subheading' => 'h3',
                        'cols'              => 3,
                        'oop_alt'           => false,
                        'oop_path'          => APPPATH.'controllers',
                        'img_path'          => 'assets'.PS.'img',
                        'file_path'         => 'assets'.PS.'files',        // doesn't strictly need to be in a public loc
                        'dash'              => 'dashboard'
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
     * @version 15-Mar-2013
     */
    public function dash_img_path($dash='')
    {
        // clean path vars
        self::_clean_paths();

        // set library values based on params passed
        $this->_setvars($dash);

        return $this->img_path.PS.(($this->dash)?$this->dash.PS:'').$this->dash_fldr.PS;
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
     * @version 15-Mar-2013
     */
    public function dash_file_path($dash='')
    {
        // clean path vars
        self::_clean_paths();

        // set library values based on params passed
        $this->_setvars($dash);

        return $this->file_path.PS.(($this->dash)?$this->dash.PS:'').$this->dash_fldr.PS;
    }


    /**
     * Display the dashboard
     *
     * @param string $dash    (optional) If specified, will set the name of the dashboard to display
     * @param bool   $oop_alt Flag indicating use of alternative oop folder structure
     * @param string $title   Alternative title HTML to use in place of default
     *
     * @return string         HTML content for widget
     * @author  hArpanet.com
     * @version 07-May-2013
     */
    public function build($dash='', $oop_alt=false, $title='')
    {
        // clean path vars
        self::_clean_paths();

        // set library values based on params passed
        $this->_setvars($dash, $oop_alt);

        // render all the parts
        self::_render();

        $widget_html = ($title=='') ? "<div class='modal_notice'>Click a widget title bar or any image to zoom.</div>" : $title;

        // build the widgets html
        foreach ($this->_dashboard as $widget) {
            $widget_html .= $widget;
        }

        // finally, add a modal container to display popups
        $widget_html .= "<div id='modal-container'><div id='modal-body'></div></div>";

        // add the client-side modal js/css
        $widget_html .= "<script type='text/javascript' src='".site_url().$this->js_path.PS.'hDash'.PS."hModal.js'></script>";
        $widget_html .= "<link rel='stylesheet' type='text/css' href='".site_url().$this->css_path.PS.'hDash'.PS."hDash.css' />";
        $widget_html .= "<link rel='stylesheet' type='text/css' href='".site_url().$this->css_path.PS.'hDash'.PS."hDash_".$this->cols."col.css' />";
        $widget_html .= "<link rel='stylesheet' type='text/css' href='".site_url().$this->css_path.PS.'hDash'.PS."hModal.css' />";

        return $widget_html;
    }


    /**
     * Traverse the $widgets array and build Dashboard from HTML widgets
     *
     * @return string HTML widget content
     *
     * @author    hArpanet.com
     * @version    14-Mar-2013
     */
    private function _render()
    {
        $content = '';

        if (is_array($this->widgets)) {
            // clean path vars
            self::_clean_paths();

            foreach ($this->widgets as $parts) {

                $content     = '';

                $type        = (array_key_exists('type',  $parts)) ? $parts['type'] : '';
                $src        = (array_key_exists('src',   $parts)) ? $parts['src'] : '';
                $title        = (array_key_exists('title', $parts)) ? $parts['title'] : '';
                $alt        = (array_key_exists('alt',   $parts)) ? $parts['alt'] : 1;
                $cols        = (array_key_exists('cols',  $parts)) ? $parts['cols'] : 1;

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
                                $file_name = $this->oop_path.PS.$this->dash_fldr.PS.$this->dash.PS.$part['src'].EXT;

                            } else {

                                // normal location will be
                                //     [oop_path]/[dashboard_name]/[dashboard_folder]/[controller_name].[ext]
                                // eg. [application/controllers]/[safety]/[dashboard]/[test_dash].[php]
                                $file_name = $this->oop_path.PS.$this->dash.PS.$this->dash_fldr.PS.$part['src'].EXT;
                            }

                            if (file_exists($file_name)) {

                                include_once $file_name;

                                // create an instance of the controller so we can run it
                                $cname = ucfirst($part['src']);
                                $c = new $cname;

                                // always run the index() method to build content
                                $content     .= $c->index();

                            } else {

                                $content .= 'WARNING: Unable to find controller: '.$file_name;
                            }
                            break;

                    //======================================
                        case 'html':
                    //======================================

                            // html or text content being directly supplied from controller
                            $content     .= $part['src'];
                            break;

                    //======================================
                        case 'curl':
                    //======================================

                            // html or text content being directly supplied from controller
                            $content     .= $this->_curl_response($part['src']);

                            break;

                    //======================================
                        case 'img':
                    //======================================

                            // create an <img> tag widget referencing an external image file
                            $img_file     = (array_key_exists('src', $part)) ? $this->img_path.PS.$this->dash.PS.$this->dash_fldr.PS.$part['src'] : '';
                            $img_alt    = (array_key_exists('alt', $part)) ? $part['alt'] : '';

                            if (file_exists(FCPATH.$img_file)) {

                                $content     .= "<img src='".site_url().$img_file."' width='100%' alt='{$img_alt}' title='{$img_alt}' class='modalview'  type='image' />";

                            } else {

                                $content = 'WARNING: Unable to find img: '.FCPATH.$img_file;
                            }
                            break;

                    //======================================
                        case 'file':
                    //======================================

                            // pull widget contents directly from an external file
                            $file_name     = (array_key_exists('src', $part)) ? FCPATH.$this->file_path.PS.$this->dash.PS.$this->dash_fldr.PS.$part['src'] : '';

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

    private function _widget($title='', $content='', $cols=1)
    /**
     * Build the widget wrapper and HTML contents
     * @author    hArpanet.com
     * @version    7-Mar-2013
     *
     * @param    string    $title        Main widget title <h2> (if === FALSE, title region is omitted)
     * @param    string    $content    HTML content to place inside widget
     * @param    int        $cols        Number of columns that widget should span
     * @return    string                HTML block
     */
    {

        $widget = '<div class="widget_wrapper {width}">';

        if ( $title !== FALSE )
        {
            // ignore heading block if title is specifically set to FALSE
            $widget .=     '<div class="widget_heading modalview" type="heading">' .
                        '    <'.$this->widget_heading.'>{title}</'.$this->widget_heading.'>' .
                        '</div>';
        }

        if ( $content !== '' )
        {
            $widget .=    '<div class="widget_content">' .
                        '    {content}' .
                        '</div>';
        }

        $widget .= '</div>';

        // populate title and content
        $widget = str_replace('{title}', $title, $widget);
        $widget = str_replace('{content}', $content, $widget);

        // convert widget width to css style
        $wordnums     = array('', 'one','two','three','four','five','six','seven','eight','nine');
        $widget     = str_replace('{width}', $wordnums[$cols].'col', $widget);

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
     * @version    11-Sept-2012
     * @see        http://www.php.net/manual/en/book.curl.php#102885
     */
    private function _curl_response($url, $get_body = true, $status = null, $wait = 5)
    {
        $time = microtime(true);
        $expire = $time + $wait;

        // NOTE: FORKING NOT CURRENTLY INSTALLED ON TROSTRE PHP SERVER, SO COMMENTED OUT
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
     * Configure Dashboard and Location values
     *
     * @param string $dash    Name of dashboard
     * @param bool   $oop_alt Flag indicating if we should use alternative path for oop controllers
     *
     * @return  void
     */
    private function _setvars($dash='', $oop_alt='')
    {
        // set library values based on params passed
        $this->dash    = ($dash)    ? $dash    : $this->dash;
        $this->oop_alt = ($oop_alt) ? $oop_alt : $this->oop_alt;
    }

    /**
     * A small helper function to ensure all paths do not have trailing slashes
     *
     * @return void
     */
    private function _clean_paths()
    {
        $path_vars = array('dash_path','asset_path','css_path','js_path','dash_fldr','dash','img_path','file_path','oop_path');

        foreach ($path_vars as $var) {
            $this->$var = trim($this->$var);
        }
    }
}
