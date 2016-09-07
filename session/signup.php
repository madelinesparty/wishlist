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
require_once '../common/rfc3696.php';
require_once '../common/settings.php';

$signup_name = "";
$error_email = null;
$error_email_confirm = null;
$error_secret_word = null;
$error_create = null;

if (isset($_POST['signup']))
{
  $signup_email = isset($_POST['email']) ? $_POST['email'] : "";
  $signup_email_confirm = isset($_POST['email_confirm']) ? $_POST['email_confirm'] : "";
  $signup_name = htmlentities($_POST['name'] ? $_POST['name'] : "");
  $signup_secret_word = $_POST['secret_word'] ? $_POST['secret_word'] : "";

  $expected_secret_word = setting_get('SIGNUP_SECRET_WORD');

  if (!is_rfc3696_valid_email_address($signup_email))
  {
    $signup_email = htmlentities($signup_email);
    $error_email = "E-mail is invalid format";
  }
  if ($signup_email != $signup_email_confirm)
  {
    $signup_email = htmlentities($signup_email);
    $error_email_confirm = "The e-mail address and confirmation address do not match";
  }
  if ($signup_secret_word != $expected_secret_word)
  {
    $error_secret_word = "I think you might have mis-typed the invitation code.";
  }
  if (!$error_email && !$error_email_confirm && !$error_secret_word)
  {
    // Check to make sure a user with the same address doesn't already exist
    $stmt = $mp_db->prepare("SELECT id, remember FROM users WHERE email=:email LIMIT 1");
    if ($stmt->execute(array(':email' => $signup_email)) && $stmt->rowCount() >= 1)
    {
      // NOTE: This case should never happen under normal circumstances
      $signup_email = htmlentities($signup_email);
      $error_email = "This e-mail address is already in use!";
    }
    else
    {
      // Create a new random remember token for this user
      $remember_token = gen_remember_token();
      
      // Add a record for the new user
      $stmt = $mp_db->prepare("INSERT INTO users (remember,name,email) VALUES (:remember_token,:name,:email)");
      if (!$stmt->execute(array(':remember_token' => $remember_token,
                                ':name' => $signup_name,
                                ':email' => $signup_email)))
      {
        $error_create = "Couldn't register you for some reason. Please try again later.";
      }
      else
      {
        $userid = $mp_db->lastInsertId();
        echo $userid;

        // Set the user session and remember cookie
        set_login_state(array('id'=>$userid,'remember'=>$remember_token));
        //$_SESSION['userid'] = $userid;
        //setcookie('remember', $remember_token, time()+6*30*24*60*60, '/', $_SERVER['SERVER_NAME']);

        // Send a confirmation e-mail
        send_confirmation_email($signup_email);

        // Redirect to where we started
        redirect_to($_SESSION['return_to']);
        $_SESSION['return_to'] = NULL;
        exit;
      }

    }
  }
}
else
{
  $signup_email = htmlentities($_GET['email']);
}

function gen_remember_token()
{
    $length = 16;
    $characters = '0123456789abcdefghijklmnopqrstuvwxyz';
    $string = "";
    for ($p = 0; $p < $length; $p++)
    {
        $string .= $characters[mt_rand(0, strlen($characters))];
    }
    return $string;
}

function send_confirmation_email($to)
{
  $from = "no_reply@madelinesparty.com";
  $subject = "Thank you for participating in Madeline's Party";
  $content = <<<CONFIRMATION_EMAIL_CONTENT
Thank you for participating in Madeline's Party. Each gift that you
purchase helps a family in need.

You can now view and claim gifts that you have purchased from the
wish list from any browser, including your mobile phone, by going to
http://madelinesparty.com/list.php and entering the e-mail address.
where this confirmation was received.
CONFIRMATION_EMAIL_CONTENT;
  $headers = "From: $from\r\n";

  mail($to, $subject, $content, $headers);
}

$mp_root_path = "../";
$mp_page_name = "Sign Up";
include "../common/common_top.php";
?>
<div id="center">
  <div id="main">

    <h1>Sign Up</h1>
    <p>It doesn't look like you've been here before. We need a few quick pieces
    of information to get started. <i>If you mis-typed your e-mail address, hit the back button.</i></p>
    <?php if ($error_create) echo "<span class=\"error\">{$error_create}</span>"; ?>
    
    <form class="signup" action="<?php echo $_SERVER['REQUEST_URI'] ?>" method="POST">
      <fieldset>
        <label for="email">E-mail Address</label>
        <input name="email" id="email" type="input" value="<?php echo $signup_email; ?>" size="50"/>
        <br/>
        <?php if ($error_email) echo "<span class=\"error\">{$error_email}</span><br/>"; ?>

        <label for="email_confirm">Confirm E-mail Address</label>
        <input name="email_confirm" id="email_confirm" type="input" size="50"/>
        <br/>
        <?php if ($error_email_confirm) echo "<span class=\"error\">{$error_email_confirm}</span><br/>"; ?>
      </fieldset>

      <fieldset>
        <label for="name">Your Name (optional)</label>
        <input name="name" id="name" type="input" value="<?php echo $signup_name; ?>" size="50"/>
        <br/>
      </fieldset>

      <fieldset>
        <label for="secret_word">Invite Code</label>
        <input name="secret_word" id="secret_word" type="input" size="50"/><br/>
        <span class="form_hint">Hint: The invite code was included in your invitation e-mail or letter</span>
        <br/>
        <?php if ($error_secret_word) echo "<span class=\"error\">{$error_secret_word}</span><br/>"; ?>
      </fieldset>

      <input name="signup" value="Sign Up!" type="submit"/>
    </form>

    <p style="clear:both" />
  </div>
</div>

<?php
include "../common/common_bottom.php";
?>
