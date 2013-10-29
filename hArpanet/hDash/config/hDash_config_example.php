<?php (defined('BASEPATH')) OR exit('No direct script access allowed');

/**
 * hArpanet Dashboard Library (hDash) Example Configuration file
 *
 * Description:
 * If required, rename/copy this file to config.php to ensure it is not overwritten
 * by future hDash updates. Then specify below the settings for the various
 * dashboard elements.
 *
 * @copyright	Copyright (c) 2013 hArpanet.com
 * @version 	1.0, 11-Mar-2013
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in
 * all copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 * THE SOFTWARE.
 **/

//
// ALL ELEMENTS ARE NAMESPACED BY hDASH TO PREVENT CLASHES
//

// dash_path and asset_path not actually used anywhere, but available in case.
$config['dash_path'] 	= APPPATH.'third_party/hArpanet/hDash';
$config['asset_path']	= FCPATH.'assets';

// hDash FOLDER
// ------------
// Specify the name containing dashboard files and resources.
$config['dash_fldr'] = 'hDash';

// hDash CORE RESOURCES LOCATION
// -----------------------------
// These need to be changed to the publicly accessible path (outside of APPPATH)
// where you placed the core asset files.
//
// NOTE: the hDash.css file uses an image background for widget headings. The
//		 css file expects the image to be located relative to the css file at:
//		 ../../img/hDash/hDash_widget_h2.gif
//
$config['css_path']	= 'assets/css';	// eg. makes: [www.website.com/]assets/css[/hDash/]hDash.css

// hDash CLIENT PREFERENCES
// ------------------------
$config['dash_fldr']			= 'hDash';		// folder name containing widget sources
$config['cols']					= 3;			// number of columns on dashboard (loads related css file)
$config['oop_alt']				= FALSE;		// FALSE = do not use alternative file paths for oop files
$config['widget_heading'] 		= 'h2';			// HTML tag to use for main widget heading
$config['widget_subheading'] 	= 'h3';			// HTML tag to use for an sub-headings within widgets

// hDash WIDGET CONTENT LOCATIONS
// ------------------------------
// eg. if you are creating a 'news' dashboard (ie. ['dash'] = 'news'), the following locations apply:
//     img files: 		will be located at: ['img_path']/[news]/['dash_fldr']/[file_name.ext]
//     html/txt files: 	will be located at: ['file_path']/[news]/['dash_fldr']/[file_name.ext]
//     oop controllers: will be located at: ['oop_path']/[news]/['dash_fldr']/[controller_name].php
//
//	   OOP NOTE: If ['oop_alt'] = TRUE, an alternate path structure will be used...
//     oop controllers: will be located at: ['oop_path']/['dash_fldr']/[news]/[controller_name].php
//
// DASHBOARD NAME:
// This indicates a folder name that will be appended automatically to each of the
// paths specified below. This value is normally specified by your Controller each
// time a dashboard is generated in order to keep files for each dashboard in their own folders.
//   eg. $dash->build('dashname');
// It can also be set explicitly if required.
//   eg. $dash->dash = 'dashname';
// If set explicitly, the dashboard can be built without specifying a name.
//   eg. $dash->build();
//
$config['dash'] = 'dashboard';
//
// CONTROLLERS:
// Put the widget Controllers anywhere that works for you.
// The widget Controllers themselves must be placed within a subfolder of
// 'oop_path' named after the dashboard ['dash'] and dashboard folder ['dash_fldr'].
// eg. if you are creating a 'news' dashboard, the widget Controller
//      will be located at: ['oop_path'][/news][/dash_fldr][/controller_name].php
// 		NOTE: the dashboard name (eg. news) and dashboard folder (eg. hDash) will be
//			  appended automatically by hDash at runtime.
// 		NOTE: see OOP NOTE above in regard to alternate folder structure.
//
$config['oop_path']	= APPPATH.'controllers';	// eg. makes: application/controllers[/dash][/dash_fldr][/filename]
//
// IMAGES:
// site_url() will be prepended automatically as well as appending the dashboard name and folder.
//
$config['img_path']	 = 'assets/img';	// eg. makes: [www.website.com/]assets/img[/dash][/dash_fldr][/filename]
//
// FLAT FILES:
// Specify a filesystem path to flat files (.html, .txt, etc.) as they get fopen()'d by hDash
// and their content is pasted into the widget.
// CI FCPATH will be prepended automatically as well as appending the dashboard name and folder.
//
$config['file_path'] = 'assets/files';	// eg. makes: [/home/intranet/]assets/files[/dash][/dash_fldr][/filename]
