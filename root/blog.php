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
define('IN_PHPBB', true);
$phpbb_root_path = (defined('PHPBB_ROOT_PATH')) ? PHPBB_ROOT_PATH : './';
$phpEx = substr(strrchr(__FILE__, '.'), 1);
include_once($phpbb_root_path . 'common.' . $phpEx);

// Start session management
$user->session_begin();
$auth->acl($user->data);
$user->setup('mods/blog');

if ($config['blog_disable'])
{
  trigger_error($config['blog_disable_msg']);
}     

if ( !$auth->acl_get('u_blog_view') )
{
  ($auth->acl_get('a_')) ? trigger_error('BLOG_PERM_VIEW_ADMIN') : trigger_error('BLOG_PERM_VIEW');
}

$action = request_var('action', '');
$article_id = request_var('article_id', 0);
$cat_id = request_var('cat_id', 0);
$comment_id = request_var('comment_id', 0);
$start = request_var('start', 0);
$archive = request_var('archive', '');
$search = utf8_normalize_nfc(request_var('search', '', true)); 

switch($action)
{
  case '':
  
    $pagination_url = append_sid("{$phpbb_root_path}blog.{$phpEx}");
    
    if ($search)
    {
      $searchterm = '*' . strtolower($search) . '*';
  		if ($searchterm != '**')
  		{
  			$searchterm = str_replace('*', $db->any_char , $searchterm);
  			$searchterm = str_replace('?', $db->one_char , $searchterm);
  		}
  		
  		$sql_where = ' WHERE ( LOWER(a.text) ' . $db->sql_like_expression($searchterm) . ' OR LOWER(a.title) ' . $db->sql_like_expression($searchterm) . ')';
  		$pagination_url = append_sid("{$phpbb_root_path}blog.{$phpEx}", 'search='.$search);
		}
		
		if ($archive)
    {
      $sql_where = ' WHERE a.archive_date = "' . $archive . '" ';       
      $pagination_url = append_sid("{$phpbb_root_path}blog.{$phpEx}", 'archive='.$archive);  
    }
		
    if ($cat_id)
    {
      $sql_where = ' WHERE a.cat_id = ' . $cat_id . ' ';       
      $pagination_url = append_sid("{$phpbb_root_path}blog.{$phpEx}", 'cat_id='.$cat_id);  
    }
    
  	$sql = 'SELECT a.article_id, a.cat_id, a.author_id, a.title, a.text, a.time, a.views, a.comments, a.locked, a.bbcode_bitfield, a.bbcode_uid, c.name, u.username, u.user_colour
              FROM ' . BLOG_ARTICLES_TABLE . ' a
              LEFT JOIN ' . USERS_TABLE . ' u
                ON u.user_id = a.author_id
              LEFT JOIN ' . BLOG_CATS_TABLE . ' c
                ON c.cat_id = a.cat_id
              ' . $sql_where . '
                ORDER BY a.time DESC';
  	$result = $db->sql_query_limit($sql, 10, $start);
  	    
  	while ($row = $db->sql_fetchrow($result))
  	{
      $row['text'] = trim_text($row['text'], $row['bbcode_uid'], $config['blog_max_chars'], $config['blog_max_par'], array(' ', "\n"), '...', $row['bbcode_bitfield'], true);
      
      $row['bbcode_options'] = 7;
      
      $row['text'] = generate_text_for_display($row['text'], $row['bbcode_uid'], $row['bbcode_bitfield'], $row['bbcode_options']);
      
  		$template->assign_block_vars('articles', array(
    		'CAT_NAME'        => $row['name'],
    		'U_CAT'           => append_sid("{$phpbb_root_path}blog.$phpEx", 'cat_id='.$row['cat_id']),
    		'AUTHOR'          => get_username_string('full', $row['author_id'], $row['username'], $row['user_colour']),  
    		'TITLE'           => $row['title'],
    		'TEXT'            => $row['text'],
    		'TIME'            => $user->format_date($row['time']),
    		'VIEWS'           => $row['views'],
    		'COMMENTS'        => $row['comments'],
    		'LOCK_COMMENTS'   => ($row['locked']) ? true : false,
    		'L_BLOG_COMMENTS' => ($row['comments'] == 1) ? $user->lang['BLOG_COMMENT'] : $user->lang['BLOG_COMMENTS'],
    		'U_MORE'          => append_sid("{$phpbb_root_path}blog.{$phpEx}", 'action=view&amp;article_id='.$row['article_id']),
    		'U_COMMENTS'      => append_sid("{$phpbb_root_path}blog.{$phpEx}", 'action=view&amp;article_id='.$row['article_id'].'#comments'),
  		));
  	}
  	$db->sql_freeresult($result);
  	
  	// Pagination
    $sql = 'SELECT COUNT(a.article_id) as total_articles
              FROM ' . BLOG_ARTICLES_TABLE . ' a
              ' . $sql_where;
  	$db->sql_query($sql);
    $total_articles = $db->sql_fetchfield('total_articles');

    $template->assign_vars(array(
    	'PAGINATION'     => generate_pagination($pagination_url, $total_articles, 10, $start),
    	'PAGE_NUMBER'    => on_page($total_articles, 10, $start),
    	'TOTAL_ARTICLES' => ($total_articles == 1) ? $user->lang['BLOG_LIST_ARTICLE'] : sprintf($user->lang['BLOG_LIST_ARTICLES'], $total_articles),
    ));
  	  	
    // Page title and navigation
  	$page_title = $user->lang['BLOG_BLOG'];
  	$blog_page_name = $user->lang['BLOG_BLOG'];
  
  	$template->assign_block_vars('navlinks', array(
      'FORUM_NAME'    => $user->lang['BLOG_BLOG'],
      'U_VIEW_FORUM'  => append_sid("{$phpbb_root_path}blog.{$phpEx}"),
  	));
  	
    if ($search)
    {
      $page_title .= ' &bull; ' . $user->lang['BLOG_SEARCH_FOR'].$search;
      $blog_page_name = $user->lang['BLOG_SEARCH_FOR'].$search;
    }
    
    if ($archive)
    {
      $date = explode('-', $archive);
    
      $month = return_month($date[1]);
    
      $page_title .= ' &bull; ' . $user->lang['BLOG_ARCHIVE_FOR'] . ' ' . $month . ' ' . $date[0];
      $blog_page_name = $user->lang['BLOG_ARCHIVE_FOR'] . ' ' . $month . ' ' . $date[0];
    }
  	
  	if ($cat_id)
    {  	
    	$sql = 'SELECT name
                FROM ' . BLOG_CATS_TABLE . '
                  WHERE cat_id = ' . $cat_id;
    	$db->sql_query($sql);
      $cat_name = $db->sql_fetchfield('name');
  	
      $page_title .= ' &bull; ' . $cat_name;
      $blog_page_name = $cat_name;
      
      $template->assign_block_vars('navlinks', array(
        'FORUM_NAME'    => $cat_name,
        'U_VIEW_FORUM'  => append_sid("{$phpbb_root_path}blog.{$phpEx}", 'cat_id='.$cat_id),
    	));
    }
      	
    break;
    
  case 'view':
    
    if (!$article_id)
  	{
  		trigger_error('BLOG_NO_ARTICLE_SELECTED');
  	}
  	
  	if ( !$auth->acl_get('u_blog_read') )
    {
      ($auth->acl_get('a_')) ? trigger_error('BLOG_PERM_READ_ADMIN') : trigger_error('BLOG_PERM_READ');
    }
  	
  	$sql = 'SELECT a.cat_id, a.author_id, a.title, a.text, a.time, a.views, a.locked, c.name, a.bbcode_bitfield, a.bbcode_uid, a.locked, u.username, u.user_colour
              FROM ' . BLOG_ARTICLES_TABLE . ' a
                LEFT JOIN ' . USERS_TABLE . ' u
                  ON u.user_id = a.author_id
                LEFT JOIN ' . BLOG_CATS_TABLE . ' c
                  ON c.cat_id = a.cat_id
                WHERE a.article_id = ' . $article_id;
  	$result = $db->sql_query($sql);
  	$row = $db->sql_fetchrow($result);
  
  	if (!$db->sql_affectedrows())
  	{
  		trigger_error('BLOG_ARTICLE_NOT_EXIST');
  	}
  	
  	// Update views count
  	$sql = 'UPDATE ' . BLOG_ARTICLES_TABLE . '
  						SET views = views +1
  						  WHERE article_id = ' . $article_id;
  	$db->sql_query($sql);
  	
  	$row['bbcode_options'] = 7;
      
  	$row['text'] = generate_text_for_display($row['text'], $row['bbcode_uid'], $row['bbcode_bitfield'], $row['bbcode_options']);
  	
    // Template variables
  	$template->assign_vars(array(
  		'ARTICLE_CAT_NAME'            => $row['name'],
  		'U_ARTICLE_CAT'               => append_sid("{$phpbb_root_path}blog.$phpEx", 'cat_id='.$row['cat_id']),
  		'ARTICLE_AUTHOR'              => get_username_string('full', $row['author_id'], $row['username'], $row['user_colour']),  
  		'ARTICLE_TITLE'               => $row['title'],
  		'ARTICLE_TEXT'                => $row['text'],
  		'ARTICLE_TIME'                => $user->format_date($row['time']),
  		'ARTICLE_VIEWS'               => $row['views'],
  		'ARTICLE_LOCK_COMMENTS'       => ($row['locked']) ? true : false,
  		'ARTICLE_CAN_ADD_COMMENT'     => ( $auth->acl_get('u_blog_add_comments') || $auth->acl_get('a_') ) ? true : false,
  		'U_ARTICLE_COMMENTS'          => append_sid("{$phpbb_root_path}blog.{$phpEx}", 'action=view&amp;article_id='.$article_id.'#comments'),
  		'U_ARTICLE_COMMENT'           => append_sid("{$phpbb_root_path}blog.$phpEx", 'action=add_comment&amp;article_id='.$article_id),
  		'U_ARTICLE_EDIT'              => ( $auth->acl_get('m_blog_edit_article') || $auth->acl_get('a_') || ( $auth->acl_get('u_blog_edit_own_article') && $user->data['user_id'] == $row['author_id'] ) ) ? append_sid("{$phpbb_root_path}blog.{$phpEx}", 'action=edit&amp;article_id='.$article_id) : false,
  		'U_ARTICLE_DELETE'            => ( $auth->acl_get('m_blog_delete_article') || $auth->acl_get('a_') || ( $auth->acl_get('u_blog_delete_own_article') && $user->data['user_id'] == $row['author_id'] ) ) ? append_sid("{$phpbb_root_path}blog.{$phpEx}", 'action=delete&amp;cat_id='.$row['cat_id'].'&amp;article_id='.$article_id) : false,
    ));
    
    if (!$config['blog_comments_disable'])
    {
      include_once($phpbb_root_path . 'includes/functions_display.' . $phpEx);
      
      $comment_order = ($config['blog_comments_chrono']) ? 'ASC' : 'DESC' ;
      
      // Load comments
    	$sql = 'SELECT c.comment_id, c.author_id, c.text, c.time,
                     u.username, u.user_colour, u.user_avatar, u.user_avatar_type, u.user_avatar_width, u.user_avatar_height, u.user_website,
                     u.user_regdate, u.user_allow_viewemail, u.user_email, u.user_allow_pm
                FROM ' . BLOG_COMMENTS_TABLE . ' c
                  LEFT JOIN ' . USERS_TABLE . ' u
                    ON u.user_id = c.author_id
                  WHERE c.article_id = ' . $article_id . '
                  ORDER BY c.time ' . $comment_order;
    	$result = $db->sql_query_limit($sql, 10, $start);
    	
    	while($comment = $db->sql_fetchrow($result))
    	{
        $comment['bbcode_options'] = 6;
          
    		$comment['text'] = generate_text_for_display($comment['text'], '', '', $comment['bbcode_options']);
    		
    		$template->assign_block_vars('comments', array(
      		'USERNAME'        => get_username_string('no_profile', $comment['author_id'], $comment['username'], $comment['user_colour']),
          'U_USER_PROFILE'  => get_username_string('profile', $comment['author_id'], $comment['username'], $comment['user_colour']),
          'AVATAR'          => get_user_avatar($comment['user_avatar'], $comment['user_avatar_type'], $comment['user_avatar_width'], $comment['user_avatar_height']),
          'REGDATE'         => $user->format_date($comment['user_regdate']),
          'U_EMAIL'         => ( (!empty($comment['user_allow_viewemail']) && $auth->acl_get('u_sendemail')) || $auth->acl_get('a_user') ) ? ( ($config['board_email_form'] && $config['email_enable']) ? append_sid("{$phpbb_root_path}memberlist.$phpEx", 'mode=email&amp;u=' . $comment['author_id']) : (($config['board_hide_emails'] && !$auth->acl_get('a_user')) ? '' : 'mailto:' . $comment['user_email']) ) : '',
          'U_PM'            => ($config['allow_privmsg'] && $auth->acl_get('u_sendpm') && ($comment['user_allow_pm'] || $auth->acl_gets('a_', 'm_') || $auth->acl_getf_global('m_'))) ? append_sid("{$phpbb_root_path}ucp.$phpEx", 'i=pm&amp;mode=compose&amp;u=' . $comment['author_id']) : '',
      		'U_WWW'           => $comment['user_website'],
      		'TEXT'            => $comment['text'],
      		'TIME'            => $user->format_date($comment['time']),
      		'U_DELETE'        => ( $auth->acl_get('m_blog_delete_comment') || $auth->acl_get('a_') || ( $auth->acl_get('u_blog_delete_own_comment') && $user->data['user_id'] == $comment['author_id'] ) ) ? append_sid("{$phpbb_root_path}blog.{$phpEx}", 'action=delete_comment&amp;article_id='.$article_id.'&amp;comment_id='.$comment['comment_id']) : false,
    		));
    	}
    	
    	// Pagination
    	$pagination_url = append_sid("{$phpbb_root_path}blog.$phpEx", 'action=view&amp;article_id='.$article_id);
    	
      $sql = 'SELECT COUNT(c.comment_id) as total_comments
                FROM ' . BLOG_COMMENTS_TABLE . ' c
                WHERE c.article_id = ' . $article_id;
    	$db->sql_query($sql);
      $total_comments = $db->sql_fetchfield('total_comments');
      
      $template->assign_vars(array(
      	'PAGINATION'        => generate_pagination($pagination_url, $total_comments, 10, $start),
      	'PAGE_NUMBER'       => on_page($total_comments, 10, $start),
      	'TOTAL_COMMENTS'    => ($total_comments == 1) ? $user->lang['BLOG_LIST_COMMENT'] : sprintf($user->lang['BLOG_LIST_COMMENTS'], $total_comments),
      ));
    }
  
    // Page title and navigation
  	$page_title = $user->lang['BLOG_BLOG'] . ' &bull; ' . $row['name'] .  ' &bull; ' . $row['title'];
  
  	$template->assign_block_vars('navlinks', array(
      'FORUM_NAME'    => $user->lang['BLOG_BLOG'],
      'U_VIEW_FORUM'  => append_sid("{$phpbb_root_path}blog.{$phpEx}"),
  	));
  	$template->assign_block_vars('navlinks', array(
      'FORUM_NAME'    => $row['name'],
      'U_VIEW_FORUM'  => append_sid("{$phpbb_root_path}blog.{$phpEx}", 'cat_id='.$row['cat_id']),
  	));
  	$template->assign_block_vars('navlinks', array(
      'FORUM_NAME'    => $row['title'],
      'U_VIEW_FORUM'  => append_sid("{$phpbb_root_path}blog.{$phpEx}", 'action=view&amp;article_id='.$article_id),
  	));
  	
    break;
    
  case 'add':
 
    if ($user->data['user_id'] == ANONYMOUS)
  	{
  		login_box();
  	}
  	
  	if ( !$auth->acl_get('u_blog_add_articles') && !$auth->acl_get('a_') )
    {
      trigger_error('BLOG_PERM_ADD');
    }
  	
  	$user->add_lang('posting');
  	include_once($phpbb_root_path . 'includes/functions_posting.' . $phpEx);
  	include_once($phpbb_root_path . 'includes/functions_display.' . $phpEx);
  	
  	display_custom_bbcodes();
  	
    generate_smilies('inline', 0);
  	
  	$submit = (isset($_POST['submit'])) ? true : false;
  
  	if ($submit)
  	{        
  		$title = utf8_normalize_nfc(request_var('title', '', true));
  		$text = utf8_normalize_nfc(request_var('text', '', true));
  		$cat_id = request_var('cat_id', 0);
  		$locked = request_var('locked', 0);
  		
  		$uid = $bitfield = $options = '';
  
  		$error = array();
  
  		if ( !$title || !$text || !$cat_id )
  		{
  			$error[] = $user->lang['BLOG_MISSING_ERROR'];
  		}
  
  		$template->assign_vars(array(
    		'ARTICLE_CAT_ID'      => $cat_id,
      	'ARTICLE_TITLE'       => $title,
      	'ARTICLE_TEXT'        => $text,
      	'ARTICLE_LOCKED'      => $locked,
    		'ERROR'			          => (sizeof($error)) ? implode('<br />', $error) : '',
  		));
  
  		if(!sizeof($error))
  		{
  		  generate_text_for_storage($text, $uid, $bitfield, $options, true, true, true);
  		  
  			$sql_ary = (array(
      		'cat_id'          => $cat_id,
      		'author_id'       => $user->data['user_id'],
      		'title'           => $title,
      		'text'            => $text,
      		'time'            => time(),
      		'locked'          => $locked,
      		'archive_date'    => date('Y-m'),
      		'bbcode_bitfield' => $bitfield,
      		'bbcode_uid'      => $uid,
  			));
  				
  			$sql = 'INSERT INTO ' . BLOG_ARTICLES_TABLE . $db->sql_build_array('INSERT', $sql_ary);
  			$db->sql_query($sql);
  			
  			$sql = 'SELECT article_id
                  FROM ' . BLOG_ARTICLES_TABLE . '
                    ORDER BY article_id DESC';
  			$db->sql_query_limit($sql, 1);
  			$article_id = $db->sql_fetchfield('article_id');          
        
        // Update articles count
      	$sql = 'UPDATE ' . BLOG_CATS_TABLE . '
      						SET articles = articles +1
      						  WHERE cat_id = ' . $cat_id;
      	$db->sql_query($sql);    
        
  			meta_refresh(3, append_sid("{$phpbb_root_path}blog.$phpEx", 'action=view&amp;article_id='.$article_id));
				$message = $user->lang['BLOG_ARTICLE_CREATED_SUCCESS'] . '<br /><br />' . sprintf($user->lang['BLOG_VIEW_ARTICLE'], '<a href="' . $phpbb_root_path.'blog.'.$phpEx.'?action=view&amp;article_id='.$article_id . '">', '</a>');
				trigger_error($message);
  		}
  	}
  	
  	$template->assign_vars(array(
      'U_ACTION'              => append_sid("{$phpbb_root_path}blog.{$phpEx}", 'action=add'),
      'S_SHOW_SMILEY_LINK'    => true,
      'S_SMILIES_ALLOWED'     => true,
      'S_BBCODE_ALLOWED'      => true,    
      'S_LINKS_ALLOWED'       => true,
      'S_BBCODE_IMG'          => true,
      'S_BBCODE_QUOTE'        => true,
      'U_MORE_SMILIES'        => append_sid("{$phpbb_root_path}posting.$phpEx", 'mode=smilies'),
  	));
  	
  	// Page title and navigation
  	$page_title = $user->lang['BLOG_BLOG'] . ' &bull; ' . $user->lang['BLOG_NEW_ARTICLE'];
  	$blog_page_name = $user->lang['BLOG_NEW_ARTICLE'];
  
  	$template->assign_block_vars('navlinks', array(
      'FORUM_NAME'    => $user->lang['BLOG_BLOG'],
      'U_VIEW_FORUM'  => append_sid("{$phpbb_root_path}blog.{$phpEx}"),
  	));
  	$template->assign_block_vars('navlinks', array(
      'FORUM_NAME'    => $user->lang['BLOG_NEW_ARTICLE'],
      'U_VIEW_FORUM'  => append_sid("{$phpbb_root_path}blog.{$phpEx}", 'action=add'),
  	));
	
    break;
    
  case 'edit':
  
  	if (!$article_id)
  	{
  		trigger_error('BLOG_NO_ARTICLE_SELECTED');
  	}
  	
    if ($user->data['user_id'] == ANONYMOUS)
  	{
  		login_box();
  	}
  	
  	$sql = 'SELECT cat_id, author_id, title, text, bbcode_uid, locked
              FROM ' . BLOG_ARTICLES_TABLE . '
                WHERE article_id = ' . $article_id;
  	$result = $db->sql_query($sql);
  	$row = $db->sql_fetchrow($result);
  
  	if (!$db->sql_affectedrows())
  	{
  		trigger_error('BLOG_ARTICLE_NOT_EXIST');
  	}    
    
    if ( !$auth->acl_get('m_blog_edit_article') && $auth->acl_get('a_') && !($auth->acl_get('u_blog_edit_own_article') && $user->data['user_id'] == $row['author_id'] ) )
    {
      trigger_error('BLOG_PERM_EDIT');
    }
  	
  	$user->add_lang('posting');
  	include_once($phpbb_root_path . 'includes/functions_posting.' . $phpEx);
  	include_once($phpbb_root_path . 'includes/functions_display.' . $phpEx);
  	
  	display_custom_bbcodes();
  	
    generate_smilies('inline', 0);
  
  	decode_message($row['text'], $row['bbcode_uid']);
  	
  	$template->assign_vars(array(
      'ARTICLE_CAT_ID'      => $row['cat_id'],
      'ARTICLE_TITLE'       => $row['title'],
      'ARTICLE_TEXT'        => $row['text'],
      'ARTICLE_LOCKED'      => $row['locked'],
      'U_ACTION'            => append_sid("{$phpbb_root_path}blog.{$phpEx}", 'action=edit&amp;article_id='.$article_id),
      'S_SHOW_SMILEY_LINK'  => true,
      'S_SMILIES_ALLOWED'   => true,
      'S_BBCODE_ALLOWED'    => true,    
      'S_LINKS_ALLOWED'     => true,
      'S_BBCODE_IMG'        => true,
      'S_BBCODE_QUOTE'      => true,
      'U_MORE_SMILIES'      => append_sid("{$phpbb_root_path}posting.$phpEx", 'mode=smilies'),
  	));
  
  	$submit = (isset($_POST['submit'])) ? true : false;
  
  	if ($submit)
  	{
  		$title = utf8_normalize_nfc(request_var('title', '', true));
  		$text = utf8_normalize_nfc(request_var('text', '', true));
  		$cat_id = request_var('cat_id', 0);
  		$locked = request_var('locked', 0);
  		
  		$uid = $bitfield = $options = '';
  
  		$error = array();
  
  		if ( !$title || !$text || !$cat_id )
  		{
  			$error[] = $user->lang['BLOG_MISSING_ERROR'];
  		}
  		
  		if(!sizeof($error))
  		{
  		  generate_text_for_storage($text, $uid, $bitfield, $options, true, true, true);
  		  
  			$sql_ary = (array(
    		  'cat_id'          => $cat_id,
      		'title'           => $title,
      		'text'            => $text,
      		'locked'          => $locked,
      		'bbcode_bitfield' => $bitfield,
      		'bbcode_uid'      => $uid,
  			));
  			
  			$sql = 'UPDATE ' . BLOG_ARTICLES_TABLE . '
  								SET ' . $db->sql_build_array('UPDATE', $sql_ary). '
  								WHERE article_id = ' . $article_id;
  			$db->sql_query($sql);
  			
  			meta_refresh(3, append_sid("{$phpbb_root_path}blog.$phpEx", 'action=view&amp;article_id='.$article_id));
				$message = $user->lang['BLOG_ARTICLE_EDITED_SUCCESS'] . '<br /><br />' . sprintf($user->lang['BLOG_BACK_TO_ARTICLE'], '<a href="' . $phpbb_root_path.'blog.'.$phpEx.'?action=view&amp;article_id='.$article_id . '">', '</a>');
				trigger_error($message);
  		}
  		
  		$template->assign_vars(array(
        'ARTICLE_CAT_ID'      => $row['cat_id'],
        'ARTICLE_TITLE'       => $row['title'],
        'ARTICLE_TEXT'        => $row['text'],
        'ARTICLE_LOCKED'      => $row['locked'],
    		'ERROR'					      => (sizeof($error)) ? implode('<br />', $error) : '',
  		));
  	}
  	
  	// Page title and navigation
  	$page_title = $user->lang['BLOG_BLOG'] . ' &bull; ' . $user->lang['BLOG_EDIT_ARTICLE'];
  	$blog_page_name = $user->lang['BLOG_EDIT_ARTICLE'];
  
  	$template->assign_block_vars('navlinks', array(
      'FORUM_NAME'    => $user->lang['BLOG_BLOG'],
      'U_VIEW_FORUM'  => append_sid("{$phpbb_root_path}blog.{$phpEx}"),
  	));
  	$template->assign_block_vars('navlinks', array(
      'FORUM_NAME'    => $user->lang['BLOG_EDIT_ARTICLE'],
      'U_VIEW_FORUM'  => append_sid("{$phpbb_root_path}blog.{$phpEx}", 'action=edit'),
  	));
	
    break;
      
  case 'delete':
  
  	if (!$article_id)
  	{
  		trigger_error('BLOG_NO_ARTICLE_SELECTED');
  	}
  	
  	if ($user->data['user_id'] == ANONYMOUS)
  	{
  		login_box();
  	}
  	
  	$sql = 'SELECT author_id
              FROM ' . BLOG_ARTICLES_TABLE . '
              WHERE article_id = ' . $article_id;
		$result = $db->sql_query($sql);
		$row = $db->sql_fetchrow($result);
  	
    if ( !$auth->acl_get('m_blog_delete_article') && $auth->acl_get('a_') && !($auth->acl_get('u_blog_delete_own_article') && $user->data['user_id'] == $row['author_id'] ) )
    {
      trigger_error('BLOG_PERM_DELETE');
    }
  	
  	if (confirm_box(true))
  	{
  		$sql = 'DELETE FROM ' . BLOG_ARTICLES_TABLE . '
                WHERE article_id = ' . $article_id;
  		$db->sql_query($sql);
  		
  		$sql = 'DELETE FROM ' . BLOG_COMMENTS_TABLE . '
                WHERE article_id = ' . $article_id;
  		$db->sql_query($sql);
  		
  		// Update articles count
    	$sql = 'UPDATE ' . BLOG_CATS_TABLE . '
    						SET articles = articles -1
    						  WHERE cat_id = ' . $cat_id;
    	$db->sql_query($sql);
  
  		redirect(append_sid("{$phpbb_root_path}blog.$phpEx"));
  	}
  	else
  	{
  		confirm_box(false, $user->lang['BLOG_DELETE_ARTICLE_CONFIRM']);
  	}
	
    break;
    
  case 'add_comment':
  
		$text = request_var('comment_text', '', true);
		
		if ($text && !$config['blog_comments_disable'] && $user->data['user_id'] != ANONYMOUS && ($auth->acl_get('u_blog_add_comments') || $auth->acl_get('a_')) )
  	{
      $uid = $bitfield = $options = '';
      
  		generate_text_for_storage($text, $uid, $bitfield, $options, false, true, true);
  
  		$sql_ary = (array(
  		  'article_id'       => $article_id,
  		  'author_id'        => $user->data['user_id'],
      	'time'             => time(),
  			'text'             => $text,
  		));
  
  		$sql = 'INSERT INTO ' . BLOG_COMMENTS_TABLE . ' ' . $db->sql_build_array('INSERT', $sql_ary);
  		$db->sql_query($sql);
  		
  		// Update comments count
    	$sql = 'UPDATE ' . BLOG_ARTICLES_TABLE . '
    						SET comments = comments +1
    						  WHERE article_id = ' . $article_id;
    	$db->sql_query($sql);
		}

    redirect(append_sid("{$phpbb_root_path}blog.$phpEx", 'action=view&amp;article_id='.$article_id));

    break;

	case 'delete_comment':
	
    if (!$comment_id)
  	{
  		trigger_error('BLOG_NO_COMMENT_SELECTED');
  	}
  	
  	if ($user->data['user_id'] == ANONYMOUS)
  	{
  		login_box();
  	}
	
    $sql = 'SELECT author_id
              FROM ' . BLOG_COMMENTS_TABLE . '
                WHERE comment_id = ' . $comment_id;
		$db->sql_query($sql);
		$author_id = $db->sql_fetchfield('author_id');
	
    if ( !$auth->acl_get('m_blog_delete_comment') && !$auth->acl_get('a_') && !($auth->acl_get('u_blog_delete_own_comment') && $user->data['user_id'] == $author_id ) )
    {
      trigger_error('BLOG_PERM_DELETE_COMMENT');
    }
    
		if (confirm_box(true))
		{
    	$sql = 'DELETE
					      FROM ' . BLOG_COMMENTS_TABLE . '
                WHERE comment_id = ' . $comment_id;
			$db->sql_query($sql);
			
			// Update comments count
    	$sql = 'UPDATE ' . BLOG_ARTICLES_TABLE . '
    						SET comments = comments -1
    						  WHERE article_id = ' . $article_id;
    	$db->sql_query($sql);
    	
    	redirect(append_sid("{$phpbb_root_path}blog.$phpEx", 'action=view&amp;article_id='.$article_id));
		}
		else
		{
			confirm_box(false, $user->lang['BLOG_DELETE_COMMENT_CONFIRM']);
		}
		
    break;	
    
  default:
    redirect(append_sid("{$phpbb_root_path}blog.$phpEx"));
}

$template->assign_vars(array(
  'S_ACTION'           => $action,
	'U_NEW_ARTICLE'      => ( $auth->acl_get('u_blog_add_articles') || $auth->acl_get('a_') ) ? append_sid("{$phpbb_root_path}blog.{$phpEx}", 'action=add') : '',
	'S_DISABLE_COMMENTS' => ($config['blog_comments_disable']) ? true : false,
	'BLOG_PAGE_NAME'     => $blog_page_name,
));

load_categories();

load_archive();

page_header($page_title);

$template->set_filenames(array(
	'body' => 'blog_body.html',
));

page_footer();

function load_categories()
{
	global $db, $template, $phpEx, $phpbb_root_path;

	$sql = 'SELECT cat_id, name, articles
            FROM ' . BLOG_CATS_TABLE . '
              ORDER BY left_id ASC';
	$result = $db->sql_query($sql);

	while ($row = $db->sql_fetchrow($result))
	{
		$template->assign_block_vars('cats', array(
		  'ID'          => $row['cat_id'],
  		'NAME'        => $row['name'],
  		'ARTICLES'    => $row['articles'],
  		'U_CAT'       => append_sid("{$phpbb_root_path}blog.$phpEx", 'cat_id='.$row['cat_id']),
		));
	}
}

function load_archive()
{
	global $db, $template, $phpEx, $phpbb_root_path;

	$sql = 'SELECT DISTINCT archive_date
            FROM ' . BLOG_ARTICLES_TABLE . '
              ORDER BY archive_date DESC';
	$result = $db->sql_query($sql);

	while ($row = $db->sql_fetchrow($result))
	{
    $date = explode('-', $row['archive_date']);
    
    $month = return_month($date[1]);
    
		$template->assign_block_vars('archive', array(
  		'DATE'        => $month . ' ' . $date[0],
  		'U_ARCHIVE'   => append_sid("{$phpbb_root_path}blog.$phpEx", 'archive='.$row['archive_date']),
		));
	}
}

function return_month($date)
{
  global $user;
  
  switch($date)
  {
    case '01':
      $month = $user->lang['datetime']['January'];
      break;
    case '02':
      $month = $user->lang['datetime']['February'];
      break; 
    case '03':
      $month = $user->lang['datetime']['March'];
      break; 
    case '04':
      $month = $user->lang['datetime']['April'];
      break; 
    case '05':
      $month = $user->lang['datetime']['May'];
      break; 
    case '06':
      $month = $user->lang['datetime']['June'];
      break;
    case '07':
      $month = $user->lang['datetime']['July'];
      break;
    case '08':
      $month = $user->lang['datetime']['August'];
      break; 
    case '09':
      $month = $user->lang['datetime']['September'];
      break; 
    case '10':
      $month = $user->lang['datetime']['October'];
      break; 
    case '11':
      $month = $user->lang['datetime']['November'];
      break; 
    case '12':
      $month = $user->lang['datetime']['December'];
      break;
    default:
      $month = '';
  }
  
  return $month;
}

/**
 * BBCode-safe truncating of text
 *
 * Originally from {@link http://www.phpbb.com/community/viewtopic.php?f=71&t=670335}
 * slightly modified to trim at either the first found end line or space by EXreaction.
 *
 * Modified by Chris Smith to trim to a specified number of paragraphs and/or a maximum
 * number of characters, and provide configurable stopping positions. Made some performance
 * improvements as well.
 *
 * Just like phpBB3 this function doesn't support embedding BBCodes in BBCode parameters
 * either except for [quote].
 *
 * @author fberci (http://www.phpbb.com/community/memberlist.php?mode=viewprofile&u=158767)
 * @author EXreaction (http://www.phpbb.com/community/memberlist.php?mode=viewprofile&u=202401)
 * @author Chris Smith <toonarmy@phpbb.com> (http://www.phpbb.com/community/memberlist.php?mode=viewprofile&u=108642)
 * @param string   $text         Text containing BBCode tags to be truncated
 * @param string   $uid         BBCode uid
 * @param int   $max_length      Text length limit
 * @param int   $max_paragraphs   Maximum number of paragraphs permitted
 * @param array   $stops         Characters to stop max length search at
 * @param string   $replacement   Replacment suffix for the removed text
 * @param string   $bitfield      BBCode bitfield (optional)
 * @param bool   $enable_bbcode   Whether BBCode is enabled (true by default)
 * @return string Resulting trimmed text
 */
function trim_text($text, $uid, $max_length, $max_paragraphs = 0, $stops = array(' ', "\n"), $replacement = '…', $bitfield = '', $enable_bbcode = true)
{
	$orig_text = $text;

	if ($enable_bbcode)
	{
		static $custom_bbcodes = array();

		// Get all custom bbcodes
		if (empty($custom_bbcodes))
		{
			global $db;

			$sql = 'SELECT bbcode_id, bbcode_tag, second_pass_match
            FROM ' . BBCODES_TABLE;
			$result = $db->sql_query($sql, 3600);

			while ($row = $db->sql_fetchrow($result))
			{
				// There can be problems only with tags having an argument
				if (substr($row['bbcode_tag'], -1, 1) == '=')
				{
					$custom_bbcodes[$row['bbcode_id']] = array('[' . $row['bbcode_tag'], ':' . $uid . ']', str_replace('$uid', $uid, $row['second_pass_match']));
				}
			}
			$db->sql_freeresult($result);
		}
	}

	$trimmed = false;

	// Paragraph trimming
	if ($max_paragraphs && $max_paragraphs < preg_match_all('#\n\s*\n#m', $text, $matches))
	{
		$find = $matches[0][$max_paragraphs - 1];
		// Grab all the matches preceeding the paragraph to trim at, finds
		// those that match the trim marker, sum them to skip over them.
		$skip = sizeof(array_intersect(array_slice($matches[0], 0, $max_paragraphs - 1), array($find)));
		$pos = 0;

		do
		{
			$pos = utf8_strpos($text, $find, $pos + 1);
			$skip--;
		} while ($skip >= 0);

		$text = utf8_substr($text, 0, $pos);

		$trimmed = true;
	}

	// First truncate the text
	if ($max_length && utf8_strlen($text) > $max_length)
	{
		$pos = 0;
		$length = 0;

		if (!is_array($stops[0]))
		{
			$stops = array($stops);
		}

		foreach ($stops as $stop_group)
		{
			if (!is_array($stop_group))
			{
				continue;
			}

			foreach ($stop_group as $k => $v)
			{
				$find = (is_string($v)) ? $v : $k;
				$include = is_bool($v) && $v;

				if (($_pos = utf8_strpos(utf8_substr($text, $max_length), $find)) !== false)
				{
					if ($_pos < $pos || !$pos)
					{
						// This is a better find, it cuts the text shorter
						$pos = $_pos;
						$length = $include ? utf8_strlen($find) : 0;
					}
				}
			}

			if ($pos)
			{
				// Include the length of the search string if requested
				$max_length += $pos + $length;
				break;
			}
		}

		// Trim off spaces, this will miss UTF8 spacers :(
		$text = rtrim(utf8_substr($text, 0, $max_length));

		$trimmed = true;
	}

	// No BBCode or no trimming return
	if (!$enable_bbcode || !$trimmed)
	{
		return $text . ($trimmed ? $replacement : '');
	}

	// Some tags may contain spaces inside the tags themselves.
	// If there is any tag that had been started but not ended
	// cut the string off before it begins.
	$unsafe_tags = array(
		array('<', '>'),
		array('[quote=&quot;', "&quot;:$uid]"), // 3rd parameter true here too for now
		);

	// If bitfield is given only check for those tags that are surely existing in the text
	if (!empty($bitfield))
	{
		// Get all used tags
		$bitfield = new bitfield($bitfield);

		// isset() provides better performance
		$bbcodes_set = array_flip($bitfield->get_all_set());

		// Add custom BBCodes having a parameter and being used
		// to the array of potential tags that can be cut apart.
		foreach ($custom_bbcodes as $bbcode_id => $bbcode_tag)
		{
			if (isset($bbcodes_set[$bbcode_id]))
			{
				$unsafe_tags[] = $bbcode_tag;
			}
		}
	}
	// Else do the check for all possible tags
	else
	{
		$unsafe_tags = array_merge($unsafe_tags, $custom_bbcodes);
	}

	foreach ($unsafe_tags as $tag)
	{
		// Ooops, we are in the middle of an opening BBCode or HTML tag,
		// truncate the string before the opening tag
		if (($start_pos = strrpos($text, $tag[0])) > strrpos($text, $tag[1]))
		{
			// Wait, is this really an opening tag or does it just look like one?
			$match = array();
			if (isset($tag[2]) && preg_match($tag[2], substr($orig_text, $start_pos), $match, PREG_OFFSET_CAPTURE) != 0 && $match[0][1] === 0)
			{
				$text = rtrim(substr($text, 0, $start_pos));
			}
		}
	}

	$text = $text . $replacement;

	// Get all of the BBCodes the text contains.
	// If it does not contain any than just skip this step.
	// Preg expression is borrowed from strip_bbcode()
	if (preg_match_all("#\[(\/?)([a-z0-9_\*\+\-]+)(?:=(&quot;.*&quot;|[^\]]*))?(?::[a-z])?(?:\:$uid)\]#", $text, $matches, PREG_PATTERN_ORDER) != 0)
	{
		$open_tags = array();

		for ($i = 0, $size = sizeof($matches[0]); $i < $size; ++$i)
		{
			$bbcode_name =& $matches[2][$i];
			$opening = ($matches[1][$i] == '/') ? false : true;

			// If a new BBCode is opened add it to the array of open BBCodes
			if ($opening)
			{
				$open_tags[] = array(
					'name'	 => $bbcode_name,
					'plus'	 => ($opening && $bbcode_name == 'list' && !empty($matches[3][$i])) ? ':o' : '',
				);
			}
			// If a BBCode is closed remove it from the array of open BBCodes.
			// As always only the last opened open tag can be closed,
			// so we only need to remove the last element of the array.
			else
			{
				array_pop($open_tags);
			}
		}

		// Sort open BBCode tags so the most recently opened will be the first (because it has to be closed first)
		krsort($open_tags);

		// Close remaining open BBCode tags
		foreach ($open_tags as $tag)
		{
			$text .= '[/' . $tag['name'] . $tag['plus'] . ':' . $uid . ']';
		}
	}

	return $text;
}

?>