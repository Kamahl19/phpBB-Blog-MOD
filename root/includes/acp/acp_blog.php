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

class acp_blog
{
  var $u_action;
  var $new_config;
    
	function main($id, $mode)
  {
    global $user, $template, $db, $config, $phpbb_root_path;
    
    $this->tpl_name = 'acp_blog';

    switch($mode)
    {
      case 'settings':
      
        $this->page_title = 'ACP_BLOG_SETTINGS_TITLE';
        
        $submit	= (isset($_POST['submit'])) ? true : false;
    
        if ($submit)
    		{
    			set_config('blog_disable', request_var('blog_disable', 0));
    			set_config('blog_disable_msg', utf8_normalize_nfc(request_var('blog_disable_msg', '', true)));
    			set_config('blog_max_chars', request_var('blog_max_chars', 0));
    			set_config('blog_max_par', request_var('blog_max_par', 0));
    			set_config('blog_comments_disable', request_var('blog_comments_disable', 0));
    			set_config('blog_comments_chrono', request_var('blog_comments_chrono', 0));
    
     			trigger_error($user->lang['CONFIG_UPDATED'] . adm_back_link($this->u_action));
    		}
    
    		$template->assign_vars(array(
    			'U_ACTION'               => $this->u_action,
    			'BLOG_DISABLE'           => $config['blog_disable'],
    			'BLOG_DISABLE_MSG'       => $config['blog_disable_msg'],
    			'BLOG_MAX_CHARS'         => $config['blog_max_chars'],
    			'BLOG_MAX_PAR'           => $config['blog_max_par'],
    			'BLOG_COMMENTS_DISABLE'  => $config['blog_comments_disable'],
    			'BLOG_COMMENTS_CHRONO'   => $config['blog_comments_chrono'],
    			'S_BLOG_SETTINGS' => true,
    		));
    		
    		break;
    		
    	case 'cats':
    	
        $this->page_title = 'ACP_BLOG_CATS_TITLE';

        $action	= request_var('action', '');
        $cat_id	= request_var('cat_id', 0);       
				$name = request_var('name', '', true);
				$move_cat_id	= request_var('move_cat_id', 0);
				$delete_articles	= request_var('delete_articles', 0);

		    switch ($action)
				{
					case 'add':
					
						$sql = 'SELECT MAX(right_id) AS right_id
											FROM ' . BLOG_CATS_TABLE;
						$result = $db->sql_query($sql);
						$row = $db->sql_fetchrow($result);
						$db->sql_freeresult($result);

						$sql_ary = (array(
    					'name'       		=> $name,
    					'left_id'       => $row['right_id'] + 1,
    					'right_id'      => $row['right_id'] + 2,
    				));

						$sql = 'INSERT INTO ' . BLOG_CATS_TABLE . $db->sql_build_array('INSERT', $sql_ary);
						$db->sql_query($sql);
						
						redirect($this->u_action);
						
            break;

					case 'edit':  
					
  					$sql = 'SELECT name
    								  FROM ' . BLOG_CATS_TABLE . '
                        WHERE cat_id = ' . $cat_id;
    				$db->sql_query($sql);
    				$cat_name = $db->sql_fetchfield('name');
    
            $submit	= (isset($_POST['submit'])) ? true : false;  
					 
            if ($submit)
      		  {
  						$sql = 'UPDATE ' . BLOG_CATS_TABLE . '
                        SET name = "' . $name . '"
                          WHERE cat_id = ' . $cat_id;
  						$db->sql_query($sql);  
  						
  						redirect($this->u_action);
  					}
  					
  					$template->assign_vars(array(
    					'U_EDIT_CAT'         => $this->u_action . '&amp;action=edit&amp;cat_id='.$cat_id,
    					'BLOG_CAT_NAME'      => $cat_name,
    					'S_BLOG_CATS_EDIT'   => true,
    				));
						
            break;

					case 'delete':
			
    				$sql =  'SELECT COUNT(article_id) as articles
											FROM ' . BLOG_ARTICLES_TABLE . '
												WHERE cat_id = ' . $cat_id;
						$db->sql_query($sql);
						$articles = $db->sql_fetchfield('articles');
						
						if ($articles == 0)
						{
              if (confirm_box(true))
      				{
         				$sql = 'DELETE FROM ' . BLOG_CATS_TABLE . '
  												WHERE cat_id = ' . $cat_id;
  							$db->sql_query($sql);
      					
      					redirect($this->u_action);
              }
      				else
      				{
      					confirm_box(false, $user->lang['ACP_BLOG_DELETE_CAT_CONFIRM']);
      				}
            }
            else
            {
              $submit	= (isset($_POST['submit'])) ? true : false;  
  					 
              if ($submit)
        		  {
    						if ( $delete_articles || !$move_cat_id)
  							{
                  $sql =  'SELECT article_id
  												  FROM ' . BLOG_ARTICLES_TABLE . '
  														WHERE cat_id = ' . $cat_id;
  								$result = $db->sql_query($sql);
  
  								while($row = $db->sql_fetchrow($result))
  								{
  									$sql =  'DELETE FROM  ' . BLOG_COMMENTS_TABLE . '
  													   WHERE article_id = ' . $row['article_id'];
  									$db->sql_query($sql);
  	              }
  
                  $sql = 'DELETE FROM ' . BLOG_ARTICLES_TABLE . '
  													WHERE cat_id = ' . $cat_id;
  								$db->sql_query($sql);  
  							}
  							else if ($move_cat_id)
  							{
  							  $sql = 'UPDATE ' . BLOG_ARTICLES_TABLE . '
  												  SET cat_id = ' . $move_cat_id . '
  													  WHERE cat_id = ' . $cat_id;
  								$db->sql_query($sql);
  								
  								$sql = 'UPDATE ' . BLOG_CATS_TABLE . '
  												  SET articles = articles + ' . $articles . '
  													  WHERE cat_id = ' . $move_cat_id;
  								$db->sql_query($sql);  
  							}
  
  							$sql = 'DELETE FROM ' . BLOG_CATS_TABLE . '
  												WHERE cat_id = ' . $cat_id;
  							$db->sql_query($sql);
  							
    						redirect($this->u_action);
    					}
    					
    					$template->assign_vars(array(
      					'U_DELETE_CAT'         => $this->u_action . '&amp;action=delete&amp;cat_id='.$cat_id,
      					'DEL_CAT_ID'           => $cat_id,
      					'S_BLOG_CATS_DELETE'   => true,
      				));
      			}
						
            break;
            
					case 'move_up':
					case 'move_down':
					
						if (!$cat_id)
						{
							trigger_error($user->lang['ACP_BLOG_NO_CAT'] . adm_back_link($this->u_action), E_USER_WARNING);
						}

						$sql = 'SELECT *
										  FROM ' . BLOG_CATS_TABLE . "
										    WHERE cat_id = " . $cat_id;
						$result = $db->sql_query($sql);
						$row = $db->sql_fetchrow($result);

						if (!$row)
						{
							trigger_error($user->lang['ACP_BLOG_NO_CAT'] . adm_back_link($this->u_action), E_USER_WARNING);
						}

						move_category($row, $action);
						
						redirect($this->u_action);
						
            break;
				}

				$sql = 'SELECT cat_id, name
								FROM ' . BLOG_CATS_TABLE . '
									ORDER BY left_id ASC';
				$result	= $db->sql_query($sql);

				while($row = $db->sql_fetchrow($result))
				{
					$template->assign_block_vars('cats',array(
						'ID'			=> $row['cat_id'],
            'NAME'			=> $row['name'],
						'EDIT'  	=> $this->u_action . '&amp;action=edit&amp;cat_id=' . $row['cat_id'],
						'DELETE' 	=> $this->u_action . '&amp;action=delete&amp;cat_id=' . $row['cat_id'],
						'U_MOVE_UP'		=> $this->u_action . '&amp;action=move_up&amp;cat_id=' . $row['cat_id'],
						'U_MOVE_DOWN'	=> $this->u_action . '&amp;action=move_down&amp;cat_id=' . $row['cat_id'],
					));
				}

				$template->assign_vars(array(
					'U_ADD_CAT'        => $this->u_action . '&amp;action=add',
					'S_BLOG_CATS'      => true,
				));

      break;
  	}
	}
}

function move_category($cat_row, $action = 'move_up')
{
	global $db;

	/**
	* Fetch all the siblings between the module's current spot
	* and where we want to move it to. If there are less than $steps
	* siblings between the current spot and the target then the
	* module will move as far as possible
	*/
	$sql = 'SELECT *
						FROM ' . BLOG_CATS_TABLE . '
						  WHERE ' . (($action == 'move_up') ? "right_id < {$cat_row['right_id']} ORDER BY right_id DESC" : "left_id > {$cat_row['left_id']} ORDER BY left_id ASC");
	$result = $db->sql_query_limit($sql, 1);

	$target = array();
	while ($row = $db->sql_fetchrow($result))
	{
		$target = $row;
	}
	$db->sql_freeresult($result);

	if (!sizeof($target))
	{
		return false;
	}

	/**
	* $left_id and $right_id define the scope of the nodes that are affected by the move.
	* $diff_up and $diff_down are the values to substract or add to each node's left_id
	* and right_id in order to move them up or down.
	* $move_up_left and $move_up_right define the scope of the nodes that are moving
	* up. Other nodes in the scope of ($left_id, $right_id) are considered to move down.
	*/
	if ($action == 'move_up')
	{
		$left_id = $target['left_id'];
		$right_id = $cat_row['right_id'];

		$diff_up = $cat_row['left_id'] - $target['left_id'];
		$diff_down = $cat_row['right_id'] + 1 - $cat_row['left_id'];

		$move_up_left = $cat_row['left_id'];
		$move_up_right = $cat_row['right_id'];
	}
	else
	{
		$left_id = $cat_row['left_id'];
		$right_id = $target['right_id'];

		$diff_up = $cat_row['right_id'] + 1 - $cat_row['left_id'];
		$diff_down = $target['right_id'] - $cat_row['right_id'];

		$move_up_left = $cat_row['right_id'] + 1;
		$move_up_right = $target['right_id'];
	}

	// Now do the dirty job
	$sql = 'UPDATE ' . BLOG_CATS_TABLE . "
						SET left_id = left_id + CASE
							WHEN left_id BETWEEN {$move_up_left} AND {$move_up_right} THEN -{$diff_up}
							ELSE {$diff_down}
						END,
						right_id = right_id + CASE
							WHEN right_id BETWEEN {$move_up_left} AND {$move_up_right} THEN -{$diff_up}
							ELSE {$diff_down}
						END
						WHERE
							left_id BETWEEN {$left_id} AND {$right_id}
							AND right_id BETWEEN {$left_id} AND {$right_id}";
	$db->sql_query($sql);
}	

?>