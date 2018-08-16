<?php
/*
Plugin Name: RDDG Breadcrumbs
Plugin URI: https://pb-86.github.io/RDDG-breadcrumbs/
Description: Simple and lightweight plugin for theme developers that provide easy to use function for displaying breadcrumbs.
Version: 1.0
Author: Przemek Bąchorek
Author URI: https://reddog.systems
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html
Text Domain: rddgbc
Domain Path: /languages

{Plugin Name} is free software: you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation, either version 2 of the License, or
any later version.

{Plugin Name} is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with {Plugin Name}. If not, see {URI to Plugin License}.
*/

$position = 1;

/**
 * This is main method of the plugin
 * @return void|null
 */
function rddgbc() {
  $tag_templates = array(
    'opening_tag'   => '<nav class="rddgbc" aria-label="breadcrumb"><ol class="rddgbc__list" itemscope itemtype="http://schema.org/BreadcrumbList">',
    'closing_tag'   => '</ol></nav>',
    'list_opening'  => '<li class="rddgbc__item" itemscope itemprop="itemListElement" itemtype="http://schema.org/ListItem">',
    'list_current'  => '<li class="rddgbc__item rddgbc__item--active" aria-current="page" itemscope itemprop="itemListElement" itemtype="http://schema.org/ListItem">',
    'list_closing'  => '</li>',
    'link_class'    => 'rddgbc__link'
  );

  if( !is_front_page() ) {
    echo $tag_templates['opening_tag'];
    rddgbc_the_home( $tag_templates );

    if( is_singular() )
      rddgbc_the_singular( $tag_templates );
    elseif( is_archive() )
      rddgbc_the_archive( $tag_templates );
    elseif( is_search() )
      rddgbc_the_search( $tag_templates );
    elseif( is_404() )
      rddgbc_the_404( $tag_templates );

    echo $tag_templates['closing_tag'];
  }
}

/**
 * This method prints link to the home page.
 * @param  array $tag_templates
 * @return void
 */
function rddgbc_the_home( $tag_templates ) {
  extract( $tag_templates );
  $url      = esc_url( home_url( '/' ) );
  $title    = esc_html__( 'Home page', 'rddgbc' );
  $position = rddgbc_get_position();
  $html     = "{$list_opening}<a class=\"{$link_class}\" href=\"{$url}\" itemprop=\"item\" itemtype=\"http://schema.org/Thing\"><span itemprop=\"name\">{$title}</span></a>{$position}{$list_closing}";
  echo $html;
}

/**
 * This method prints crumb with title of 404 error page.
 * @param  array $tag_templates
 * @return void
 */
function rddgbc_the_404( $tag_templates ) {
  extract( $tag_templates );
  $url      = get_permalink();
  $title    = esc_html__( '404', 'rddgbc' );
  $position = rddgbc_get_position();
  $html     = "{$list_current}<a href=\"{$url}\" itemprop=\"item\" itemtype=\"http://schema.org/Thing\"><span itemprop=\"name\">{$title}</span></a>{$position}{$list_closing}";
  echo $html;
}

/**
 * This method prints crumb with title of search page
 * @param  array $tag_templates
 * @return void
 */
function rddgbc_the_search( $tag_templates ) {
  extract( $tag_templates );
  $url      = get_search_link();
  $title    = esc_html__( 'Search result for: ' . get_search_query(), 'rddgbc' );
  $position = rddgbc_get_position();
  $html     = "{$list_current}<a href=\"{$url}\" itemprop=\"item\" itemtype=\"http://schema.org/Thing\"><span itemprop=\"name\">{$title}</span></a>{$position}{$list_closing}";
  echo $html;
}

/**
 * This method prints current category and its ancestors.
 * @return void
 */
function rddgbc_the_archive( $tag_templates ) {
  extract( $tag_templates );
  $current_category_id = get_query_var('cat');

  $category_ancestors = array_reverse( get_ancestors( $current_category_id, 'category' ) );
  if( $category_ancestors ) {
    foreach( $category_ancestors as $category_ancestor_id ) {
      $category_ancestor_position = rddgbc_get_position();
      $category_ancestor_url      = get_category_link( $category_ancestor_id );
      $category_ancestor_title    = get_cat_name( $category_ancestor_id );
      $category_ancestor_html     = "{$list_opening}<a class=\"{$link_class}\" href=\"{$category_ancestor_url}\" itemprop=\"item\" itemtype=\"http://schema.org/Thing\"><span itemprop=\"name\">{$category_ancestor_title}</span></a>{$category_ancestor_position}{$list_closing}";
      echo $category_ancestor_html;
    }
  }

  $current_category_position  = rddgbc_get_position();
  $current_category_url = get_category_link( $current_category_id );
  $current_category_title     = get_cat_name( $current_category_id );
  $current_category_html  = "{$list_current}<a href=\"{$current_category_url}\" itemprop=\"item\" itemtype=\"http://schema.org/Thing\"><span itemprop=\"name\">{$current_category_title}</span></a>{$current_category_position}{$list_closing}";
  echo $current_category_html;
}

/**
 * This method prints page ancestors or post category hierarchy depending of
 * type of the singular and the title of current post or page.
 * @param  array $tag_templates
 * @return void
 */
function rddgbc_the_singular( $tag_templates ) {
  extract( $tag_templates );

  if( is_page() )
    rddgbc_the_page_ancestors( $tag_templates );
  elseif ( is_single() )
    rddgbc_the_categories( $tag_templates );

  $url      = get_permalink();
  $title    = get_the_title();
  $position = rddgbc_get_position();
  $html     = "{$list_current}<a href=\"{$url}\" itemprop=\"item\" itemtype=\"http://schema.org/Thing\"><span itemprop=\"name\">{$title}</span></a>{$position}{$list_closing}";
  echo $html;
}

/**
 * This method prints all the ancestors for the current page.
 * @param  array $tag_templates
 * @return void
 */
function rddgbc_the_page_ancestors( $tag_templates ) {
  extract( $tag_templates );
  $ancestors = array_reverse( get_ancestors( get_the_ID(), 'page' ) );

  if( $ancestors ) {
    foreach( $ancestors as $ancestor_id ) {
      $position       = rddgbc_get_position();
      $ancestor_url   = get_page_link( $ancestor_id );
      $ancestor_title = get_the_title( $ancestor_id );
      $ancestor_html  = "{$list_opening}<a class=\"{$link_class}\" href=\"{$ancestor_url}\" itemprop=\"item\" itemtype=\"http://schema.org/Thing\"><span itemprop=\"name\">{$ancestor_title}</span></a>{$position}{$list_closing}";
      echo $ancestor_html;
    }
  }
}

/**
 * This method prints main category and its ancestors for the current post.
 * @param  array $tag_templates
 * @return void
 */
function rddgbc_the_categories( $tag_templates ) {
  extract( $tag_templates );
  $categories = wp_get_post_categories( get_the_ID() );
  $main_category_id = $categories[0];

  $category_ancestors = get_ancestors( $main_category_id, 'category' );
  if( $category_ancestors ) {
    foreach( $category_ancestors as $category_ancestor_id ) {
      $position                 = rddgbc_get_position();
      $category_ancestor_url    = get_category_link( $category_ancestor_id );
      $category_ancestor_title  = get_cat_name( $category_ancestor_id );
      $category_ancestor_html   = "{$list_opening}<a class=\"{$link_class}\" href=\"{$category_ancestor_url}\" itemprop=\"item\" itemtype=\"http://schema.org/Thing\"><span itemprop=\"name\">{$category_ancestor_title}</span></a>{$position}{$list_closing}";
      echo $category_ancestor_html;
    }
  }

  $main_category_position = rddgbc_get_position();
  $main_category_url      = get_category_link( $main_category_id );
  $main_category_title    = get_cat_name( $main_category_id );
  $main_category_html     = "{$list_opening}<a class=\"{$link_class}\" href=\"{$main_category_url}\" itemprop=\"item\" itemtype=\"http://schema.org/Thing\"><span itemprop=\"name\">{$main_category_title}</span></a>{$main_category_position}{$list_closing}";
  echo $main_category_html;
}

/**
 * This method gets current position value, returns formated string and
 * increments position value
 * @return string
 */
function rddgbc_get_position() {
  $position_counter = $GLOBALS['position'];
  $position_html    = "<meta itemprop=\"position\" content=\"{$position_counter}\">";
  $GLOBALS['position']++;
  return $position_html;
}
