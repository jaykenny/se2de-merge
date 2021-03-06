<?php
/***************************************************************************
 *  You can find the license in the docs directory
 *
 *  Unicode Reminder メモ
 *
 *  Display some status information about the server and Opencaching
 ***************************************************************************/

	require('./lib2/web.inc.php');
	require_once('./lib2/logic/user.class.php');
	$tpl->name = 'remindemail';
	$tpl->menuitem = MNU_LOGIN_REMINDEMAIL;

	$username = isset($_REQUEST['username']) ? $_REQUEST['username'] : '';

	if (isset($_REQUEST['ok']))
	{
		if ($username == '')
		{
			$tpl->assign('errorUsernameInvalid', true);
		}
		else
		{
			$user = user::fromUsername($username);

			if ($user !== null)
			{
				if ($user->remindEMail())
					$tpl->assign('remindMailSent', true);
				else
					$tpl->assign('errorUnkown', true);
			}
			else
				$tpl->assign('errorUsernameNotExist', true);
		}
	}

	$tpl->assign('username', $username);
	$tpl->display();
?>