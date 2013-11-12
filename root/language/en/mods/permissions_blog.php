<?php
/**
*
* @package phpBB Blog MOD
* @author Kamahl www.phpbb3hacks.com
* @version 1.0.0
* @copyright (c) 2011 Kamahl www.phpbb3hacks.com
*
*/
/**
* DO NOT CHANGE
*/
if (!defined('IN_PHPBB'))
{
    exit;
}

if (empty($lang) || !is_array($lang))
{
    $lang = array();
}

// DEVELOPERS PLEASE NOTE
//
// All language files should use UTF-8 as their encoding and the files must not contain a BOM.
//
// Placeholders can now contain order information, e.g. instead of
// 'Page %s of %s' you can (and should) write 'Page %1$s of %2$s', this allows
// translators to re-order the output of data while ensuring it remains correct
//
// You do not need this where single placeholders are used, e.g. 'Message %d' is fine
// equally where a string contains only two placeholders which are used to wrap text
// in a url you again do not need to specify an order e.g., 'Click %sHERE%s' is fine

$lang = array_merge($lang, array(
  'acl_u_blog_view'                 => array('lang' => 'Can view Blog', 'cat'	=> 'blog'),
  'acl_u_blog_read'                 => array('lang' => 'Can read articles', 'cat'	=> 'blog'),
  'acl_u_blog_add_articles'         => array('lang' => 'Can add articles', 'cat'	=> 'blog'),
  'acl_u_blog_add_comments'         => array('lang' => 'Can add comments', 'cat'	=> 'blog'),
  'acl_u_blog_edit_own_article'	    => array('lang' => 'Can edit own articles', 'cat'	=> 'blog'),
  'acl_u_blog_delete_own_article'	  => array('lang' => 'Can delete own articles', 'cat'	=> 'blog'),
  'acl_u_blog_delete_own_comment'	  => array('lang' => 'Can delete own comments', 'cat'	=> 'blog'),
  
  'acl_m_blog_edit_article'	        => array('lang' => 'Can edit articles', 'cat'	=> 'blog'),
  'acl_m_blog_delete_article'	      => array('lang' => 'Can delete articles', 'cat'	=> 'blog'),
  'acl_m_blog_delete_comment'	      => array('lang' => 'Can delete comments', 'cat'	=> 'blog'),
));

$lang['permission_cat']['blog'] = 'Blog';

?>