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
* @ignore
*/
define('UMIL_AUTO', true);
define('IN_PHPBB', true);

$phpbb_root_path = (defined('PHPBB_ROOT_PATH')) ? PHPBB_ROOT_PATH : './';
$phpEx = substr(strrchr(__FILE__, '.'), 1);

include($phpbb_root_path . 'common.' . $phpEx);

$user->session_begin();
$auth->acl($user->data);
$user->setup();
$user->add_lang('mods/blog');

if (!file_exists($phpbb_root_path . 'umil/umil_auto.' . $phpEx))
{
	trigger_error('Please download the latest UMIL (Unified MOD Install Library) from: <a href="http://www.phpbb.com/mods/umil/">phpBB.com/mods/umil</a>', E_USER_ERROR);
}

// The name of the mod to be displayed during installation.
$mod_name = 'Blog MOD';

/*
* The name of the config variable which will hold the currently installed version
* You do not need to set this yourself, UMIL will handle setting and updating the version itself.
*/
$version_config_name = 'blog_version';

/*
* The language file which will be included when installing
* Language entries that should exist in the language file for UMIL (replace $mod_name with the mod's name you set to $mod_name above)
* $mod_name
* 'INSTALL_' . $mod_name
* 'INSTALL_' . $mod_name . '_CONFIRM'
* 'UPDATE_' . $mod_name
* 'UPDATE_' . $mod_name . '_CONFIRM'
* 'UNINSTALL_' . $mod_name
* 'UNINSTALL_' . $mod_name . '_CONFIRM'
*/

/*
* The array of versions and actions within each.
* You do not need to order it a specific way (it will be sorted automatically), however, you must enter every version, even if no actions are done for it.
*
* You must use correct version numbering.  Unless you know exactly what you can use, only use X.X.X (replacing X with an integer).
* The version numbering must otherwise be compatible with the version_compare function - http://php.net/manual/en/function.version-compare.php
*/
$versions = array(
	'1.0.0' => array(
		'table_add' => array(
			array(BLOG_ARTICLES_TABLE, array(
				'COLUMNS'			=> array(
					'article_id'				=> array('UINT', NULL, 'auto_increment'),
					'cat_id'		=> array('UINT', 0),
					'author_id'		=> array('UINT', 0),
					'title'	=> array('VCHAR', ''),
					'text'	=> array('TEXT', ''),
					'time'			=> array('INT:11', 0),
					'archive_date'		=> array('VCHAR', ''),
					'views'			=> array('UINT', 0),
					'comments'			=> array('UINT', 0),
					'bbcode_bitfield'		=> array('VCHAR', ''),
					'bbcode_uid'			=> array('VCHAR:8', ''),
					'locked'			=> array('TINT:1', 0),
				),
				'PRIMARY_KEY' => array('article_id'),
			)),

			array(BLOG_CATS_TABLE, array(
				'COLUMNS'	=> array(
					'cat_id'		=> array('UINT', NULL, 'auto_increment'),
					'name'		=> array('VCHAR', ''),
					'articles'		=> array('UINT', 0),
					'left_id'		=> array('UINT', 0),
					'right_id'		=> array('UINT', 0),
				),
				'PRIMARY_KEY' => array('cat_id'),
			)),

			array(BLOG_COMMENTS_TABLE, array(
				'COLUMNS'	=> array(
					'comment_id'			=> array('UINT', NULL, 'auto_increment'),
					'article_id'		=> array('UINT', '0'),
					'author_id'		=> array('UINT', '0'),
					'text'	=> array('TEXT', ''),
					'time'			=> array('INT:11', 0),
				),
				'PRIMARY_KEY' => array('comment_id'),
			)),
		),

		'config_add' => array(
			array('blog_max_chars', '1000', '0'),
			array('blog_max_par', '0', '0'),
			array('blog_disable', '0', '0'),
			array('blog_disable_msg', 'Blog is disabled', '0'),
			array('blog_comments_disable', '0', '0'),
			array('blog_comments_chrono', '0', '0'),
		),
		
		'permission_add' => array(
      array('u_blog_view', 1),
			array('u_blog_read', 1),
			array('u_blog_add_articles', 1),
			array('u_blog_add_comments', 1),
			array('u_blog_edit_own_article', 1),
			array('u_blog_delete_own_article', 1),
			array('u_blog_delete_own_comment', 1),
			array('m_blog_edit_article', 1),
			array('m_blog_delete_article', 1),
			array('m_blog_delete_comment', 1),
		),
				
		'module_add' => array(
			array('acp', 'ACP_CAT_DOT_MODS', 'ACP_BLOG'),
			array('acp', 'ACP_BLOG', array(
				'module_basename'  => 'blog',
        'modes'   => array('settings'),
      )),
      array('acp', 'ACP_BLOG', array(
				'module_basename'  => 'blog',
        'modes'   => array('cats'),
      )),
		),
		
		'cache_purge' => array(
			'template',
			'theme',
			'cache',
		),
	),
	
);

// Include the UMIF Auto file and everything else will be handled automatically.
include($phpbb_root_path . 'umil/umil_auto.' . $phpEx);

?>