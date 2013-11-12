<?php
/**
*
* @package phpBB Blog MOD
* @author Kamahl www.phpbb3hacks.com
* @version 1.0.0
* @copyright (c) 2011 Kamahl www.phpbb3hacks.com
*
*/

if (!defined('IN_PHPBB'))
{
	exit;
}

if (empty($lang) || !is_array($lang))
{
	$lang = array();
}

$lang = array_merge($lang, array(
  'ACP_BLOG'    => 'Blog',
  'ACP_BLOG_SETTINGS_TITLE'    => 'Settings',
  'ACP_BLOG_DISABLE'    => 'Disable blog',
  'ACP_BLOG_DISABLE_MSG'    => 'Disable message',
  'ACP_BLOG_DISABLE_MSG_EXPLAIN'    => 'This message will be displayed if Blog is disabled',
  'ACP_BLOG_MAX_CHARS'    => 'Maximum chars',
  'ACP_BLOG_MAX_CHARS_EXPLAIN'    => 'Set max chars to display on articles listing',
  'ACP_BLOG_MAX_PAR'    => 'Maximum paragraphs',
  'ACP_BLOG_MAX_PAR_EXPLAIN'    => 'Set max paragraphs to display on articles listing',
  'ACP_BLOG_COMMENTS_DISABLE'    => 'Disable comments',
  'ACP_BLOG_COMMENTS_CHRONO'    => 'Oldest comments first',
  
  'ACP_BLOG_CATS_TITLE'    => 'Categories',
  'ACP_BLOG_NO_CAT'    => 'No category',
  'ACP_BLOG_DELETE_CAT_CONFIRM'    => 'Are you sure you want to delete this category?',
  'ACP_BLOG_CREATE_CAT'    => 'Create category',
  'ACP_BLOG_EDIT_CAT'   => 'Edit category',
  'ACP_BLOG_DELETE_CAT'   => 'Delete category',
  'ACP_BLOG_CAT_NAME'    => 'Category name',
  'ACP_BLOG_CAT_OPTIONS'    => 'Options',
  'ACP_BLOG_DELETE_ARTICLES'    => 'Delete articles',
  'ACP_BLOG_MOVE_ARTICLES'    => 'Move articles',
));

?>