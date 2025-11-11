<?php
/*
Plugin Name: Jin defer asset
Author: webfood
Plugin URI: https://webfood.info/
Description: Jin defer asset
Version: 0.2
Author URI: https://webfood.info/
Text Domain: Jin defer asset
Domain Path: /languages

License:
 Released under the GPL license
  http://www.gnu.org/copyleft/gpl.html

  Copyright 2025 (email : webfood.info@gmail.com)

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation; either version 2 of the License, or
    (at your option) any later version.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/


//wp_headに追加
function add_preload() {
  echo '<link rel="preload" href="/wp-content/themes/jin/font/jin-icons/fonts/jin-icons.ttf?c16tcv" as="font" type="font/ttf" crossorigin>'."\n";
}
add_action('wp_head', 'add_preload');

function dequeue_plugins_style() {
    wp_dequeue_style('wp-block-library');
}
add_action( 'wp_enqueue_scripts', 'dequeue_plugins_style', 9999);

if(!(is_admin())) {
  function add_noscript_to_jin( $tag, $handle ) {
    $targets = [ 'theme-style', 'fontawesome-style','swiper-style', 'crayon' ];

  	if (is_mobile() && (is_single() || is_page())){
  		array_unshift($targets, 'parent-style');
  	}

    if ( !in_array( $handle , $targets, true ) ) {
        return $tag;
    }
    $tag = str_replace( '<link', '<noscript class="deferred-jin"><link', $tag );
    return str_replace( '/>', '/></noscript>', $tag );
  }
  add_filter( 'style_loader_tag', 'add_noscript_to_jin', 10, 2 );

  function jin_script() {
		if (is_mobile() && (is_single() || is_page())){
			$target_id = "jin-inline-css";
		}else{
			$target_id = "parent-style-css";
		}
    echo <<< EOM
<script>
 var loadDeferredStylesJin = function() {
   var addStylesNodes = document.getElementsByClassName("deferred-jin");
   var target = document.getElementById("{$target_id}");
   var place = target.nextElementSibling;

   addStylesNodes = Array.prototype.slice.call(addStylesNodes);
   addStylesNodes.forEach(function(elm) {
		 var parent = document.createElement("div");
		 parent.innerHTML = elm.textContent;
		 place.insertAdjacentElement('beforebegin', parent.firstChild );
   });
	 addStylesNodes.forEach(function(elm) {elm.parentElement.removeChild(elm);});
 };
 var raf = window.requestAnimationFrame || window.mozRequestAnimationFrame ||
     window.webkitRequestAnimationFrame || window.msRequestAnimationFrame;
 if (raf) raf(function() { window.setTimeout(loadDeferredStylesJin, 0); });
 else window.addEventListener('load', loadDeferredStylesJin);
</script>
EOM;
  }
  add_action( 'shutdown', 'jin_script' );
}

function output_inline_style() {
	wp_register_style( 'jin', false );
	wp_enqueue_style( 'jin' , 0);

	if (is_mobile() && (is_single() || is_page())){
    $css .= file_get_contents( plugin_dir_path( __FILE__ ).'inline.css');
		$css .= "
		.my-profile{
		  padding-bottom: 105px !important;
		}";
  }

	wp_add_inline_style( 'jin', $css );
}
add_action( 'wp_enqueue_scripts', 'output_inline_style', -99);

function my_remove_enqueue_style() {
    wp_dequeue_style('swiper-style');
    wp_dequeue_script('cps-swiper');
    wp_dequeue_style('crayon');
}
add_action( 'wp_enqueue_scripts', 'my_remove_enqueue_style', 11);

function crayon_enqueue_styles() {
  wp_enqueue_style('crayon');
}
add_action( 'wp_enqueue_scripts', 'crayon_enqueue_styles', 12 );

function my_deregister_scripts(){
  wp_deregister_script( 'wp-embed' );
}
add_action( 'wp_footer', 'my_deregister_scripts' );

if(!(is_admin())) {
 function add_async_to_enqueue_script($tag, $handle, $src) {
  if(FALSE === strpos($src, '.js')) return $tag;
  if(preg_match('/ async| defer/', $tag) === 1) return $tag;
  return str_replace( '"></script>', '" defer></script>', $tag );
 }

 add_filter('script_loader_tag', 'add_async_to_enqueue_script', 10, 3);
}

remove_action('wp_head', 'wp_shortlink_wp_head', 10, 0);
