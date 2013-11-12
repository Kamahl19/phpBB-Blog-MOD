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
class acp_blog_info
{
  function module()
  {  
    return array(
      'filename'    => 'acp_blog',
      'title'        => 'ACP_BLOG',
      'version'    => '1.0.0',
      'modes'        => array(
      	'settings'        => array('title' => 'ACP_BLOG_SETTINGS_TITLE', 'auth' => 'acl_a_board', 'cat' => array('ACP_CAT_DOT_MODS')),
      	'cats'        => array('title' => 'ACP_BLOG_CATS_TITLE', 'auth' => 'acl_a_board', 'cat' => array('ACP_CAT_DOT_MODS')),
      ),
    );
  }

  function install()
  {
  }

  function uninstall()
  {
  }
}
?>