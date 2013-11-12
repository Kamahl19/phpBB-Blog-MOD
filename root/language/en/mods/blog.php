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
  // General
  'BLOG_BLOG'    => 'Blog',
  'BLOG_CATEGORIES' => 'Categories',
  'BLOG_ARCHIVE'    => 'Archives',
  
  // Articles listing
  'BLOG_AUTHOR'   => 'Author',
  'BLOG_CATEGORY'   => 'Category',
  'BLOG_TIME'    => 'Posted on',
  'BLOG_VIEWED'    => 'Viewed',
  'BLOG_COMMENT'    => 'comment',
  'BLOG_COMMENTS'    => 'comments',
  'BLOG_READ_MORE'    => 'Read more »',
  'BLOG_EDIT_ARTICLE'    => 'Edit article',
  'BLOG_DELETE_ARTICLE'    => 'Delete',
  'BLOG_NEW_ARTICLE'    => 'New article',
  'BLOG_LIST_ARTICLE'    => '1 article',
  'BLOG_LIST_ARTICLES'    => '%s articles',
  'BLOG_COMMENTS_OFF'   => 'Comments Off',
  'BLOG_SEARCH_FOR'   => 'Search for ',
  'BLOG_ARCHIVE_FOR'    => 'Archive for ',
  
  // View article
  'BLOG_LIST_COMMENT'    => '1 comment',
  'BLOG_LIST_COMMENTS'    => '%s comments',
  'BLOG_POST_COMMENT'    => 'Add a comment',
  'BLOG_GUEST_COMMENT'    => '<a href="./ucp.php?mode=register">Register</a> or <a href="./ucp.php?mode=login">log in</a> to add coments',
  
  // Create article
  'BLOG_TITLE'    => 'Title',
  'BLOG_TEXT'    => 'Text',
  'BLOG_DISABLE_COMMENTS'    => 'Disable comments',
  'BLOG_VIEW_ARTICLE' => '%sView your submitted article%s',
  'BLOG_BACK_TO_ARTICLE' => '%sBack to the article%s',
  
  // Confirm & Success & Errors
  'BLOG_DELETE_ARTICLE_CONFIRM'    => 'Are you sure you want to delete this article?',
  'BLOG_DELETE_COMMENT_CONFIRM'    => 'Are you sure you want to delete this comment?',
  'BLOG_ARTICLE_EDITED_SUCCESS'    => 'This article has been edited successfully.',
  'BLOG_ARTICLE_CREATED_SUCCESS'    => 'This article has been created successfully.',
  'BLOG_MISSING_ERROR'    => 'You have not filled in all fields',
  'BLOG_NO_ARTICLE_SELECTED'    => 'You have not selected any article',
  'BLOG_NO_COMMENT_SELECTED'    => 'You have not selected any comment',
  'BLOG_ARTICLE_NOT_EXIST'    => 'This article does not exist',
  'BLOG_NO_ARTICLES'    => 'No articles',
  
  'BLOG_PERM_VIEW'    => 'You are not allowed to view blog',
  'BLOG_PERM_VIEW_ADMIN'  =>  'You are not allowed to view blog. You have to set Blog permissions for users and moderators via ACP - Permissions',
  'BLOG_PERM_READ'    => 'You are not allowed to read articles',
  'BLOG_PERM_READ_ADMIN'    => 'You are not allowed to read articles. You have to set Blog permissions for users and moderators via ACP - Permissions',
  'BLOG_PERM_ADD'    => 'You are not allowed to add articles',
  'BLOG_PERM_EDIT'    => 'You are not allowed to edit articles',
  'BLOG_PERM_DELETE'    => 'You are not allowed to delete articles',
  'BLOG_PERM_ADD_COMMENT'    => 'You are not allowed to add comments',
  'BLOG_PERM_DELETE_COMMENT'    => 'You are not allowed to delete comments',
));

?>