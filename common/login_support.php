<?php
/* Copyright (C) 2016  Madeline's Party Inc.
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation; either version 2
 * of the License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.
 */
require_once 'dbconnect.php';

/*
 * $_SESSION['userid'] is the index of the user record of the logged in user, if
 * any.
 *
 * Logged in user "remember cookie" is set to hash of user that's logged in. If
 * the session is blank, but a cookie exists, the user is automatically logged
 * in. Cookie is cleared on logout.
 */
process_login_tokens($mp_db);

function process_login_tokens($db)
{
  global $mp_current_user;
  
  if (isset($_SESSION['userid']))
  {
    // Lookup user record and populate $mp_current_user
    $stmt = $db->prepare('SELECT id, remember, name, email FROM users WHERE id=:userid LIMIT 1');
    if (!$stmt->execute(array(':userid' => $_SESSION['userid'])) || $stmt->rowCount() == 0)
    {
      // User doesn't exist, so clear session
      do_logout();
    }
    else
    {
      $mp_current_user = $stmt->fetch();
    }
  }
  elseif (isset($_COOKIE['remember']))
  {
    // Lookup user record, populate $mp_current_user, and set session variable
    // Lookup user record and populate $mp_current_user
    $stmt = $db->prepare('SELECT id, remember, name, email FROM users WHERE remember=:remember LIMIT 1');
    if (!$stmt->execute(array(':remember' => $_COOKIE['remember'])) || $stmt->rowCount() == 0)
    {
      do_logout();
    }
    else
    {
      set_login_state($stmt->fetch());
      return;
    }
  }
}

function set_login_state($userinfo)
{
  global $mp_current_user;
  
  $_SESSION['userid'] = $userinfo['id'];
  setcookie('remember', $userinfo['remember'], time()+6*30*24*60*60, '/', $_SERVER['SERVER_NAME']);

  $mp_current_user = $userinfo;
}

function do_logout()
{
  session_destroy();
  setcookie('remember', '', time()-3600, '/', $_SERVER['SERVER_NAME']);
}

function login_display()
{
  global $mp_current_user;

  if ($mp_current_user )
  {
    echo <<<LOGGED_IN
    <div id="login_panel" class="panel panel-default">
      <div class="panel-body">
        You are logged in as {$mp_current_user['email']}. If this isn't you, <a href="session/handle_login.php">click here</a>.
      </div>
    </div>
LOGGED_IN;
  }
  else
  {
    echo <<<LOGIN_PROMPT
    <div id="login_panel" class="panel panel-default">
      <div class="panel-body">
        <form class="form-inline" action="session/handle_login.php" method="post">
          <label for="email_address">Enter your e-mail address to claim gifts on the wish list</label>
          <input name="email_address" class="form-control" type="text"/>
          <input name="login" class="btn btn-primary" value="Login" type="submit"/>
        </form>
      </div>
    </div>
LOGIN_PROMPT;
  }
}

?>
