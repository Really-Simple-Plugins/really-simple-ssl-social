<?php
/**
 * Plugin Name: Really Simple SSL social
 * Plugin URI: https://www.really-simple-ssl.com/pro
 * Description: Add on for Really Simple SSL
 * Version: 1.0.0
 * Text Domain: really-simple-ssl-social
 * Domain Path: /languages
 * Author: Rogier Lankhorst
 * Author URI: https://www.rogierlankhorst.com
 */

 /*  Copyright 2014  Rogier Lankhorst  (email : rogier@rogierlankhorst.com)

     This program is free software; you can redistribute it and/or modify
     it under the terms of the GNU General Public License, version 2, as
     published by the Free Software Foundation.

     This program is distributed in the hope that it will be useful,
     but WITHOUT ANY WARRANTY; without even the implied warranty of
     MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
     GNU General Public License for more details.

     You should have received a copy of the GNU General Public License
     along with this program; if not, write to the Free Software
     Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
 */

  defined('ABSPATH') or die("you do not have acces to this page!");

  if (is_admin() ) {
    require_once( dirname( __FILE__ ) .  '/class-licensing.php' );
    require_once( dirname( __FILE__ ) .  '/class-admin.php' );
    $rsssl__soc_licensing        = new rsssl_soc_licensing;
    $rsssl_soc_admin             = new rsssl_soc_admin;
  }

  require_once( dirname( __FILE__ ) .  '/class-social.php' );
  $rsssl_soc_social             = new rsssl_soc_social;
