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
session_start();

require_once '../common/dbconnect.php';
require_once '../common/login_support.php';
require_once '../common/misc.php';

// Posts are treated as logins, gets as logouts
if (isset($_POST['email_address']))
{
  $email = trim($_POST['email_address']);
  if ($email == "")
  {
    redirect_to($_SERVER['HTTP_REFERER']);
    exit();
  }
  else
  {
    $stmt = $mp_db->prepare("SELECT id, remember FROM users WHERE email=:email LIMIT 1");
    if ($stmt->execute(array(':email' => $email)) && $stmt->rowCount() > 0)
    {
      $userinfo = $stmt->fetchAll();
      set_login_state($userinfo[0]);
      redirect_to($_SERVER['HTTP_REFERER']);
      exit();
    }
    else
    {
      $url_email = urlencode($email);
      $_SESSION['return_to'] = url_no_params($_SERVER['HTTP_REFERER']);
      redirect_to("signup.php?email={$url_email}");
      exit();
    }
  }
}
else
{
  do_logout();
  redirect_to($_SERVER['HTTP_REFERER']);
  exit;
}

?>
