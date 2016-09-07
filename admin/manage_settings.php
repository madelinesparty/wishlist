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
require_once '../common/settings.php';

if (array_key_exists('set', $_POST))
{
  $set_name = trim($_POST['name']);
  $set_value = $_POST['value'];

  if ($set_name && $set_name != "")
  {
    if (!setting_set($set_name,$set_value,true))
    {
      $setting_error = "Could not set value '{$set_name}'";
    }
    else
    {
      $setting_error = "Updated value '{$set_name}'";
    }
  }
  else
  {
    $setting_error = "Setting must have a non-empty name";
  }
}

$all_settings = setting_get_all();
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
  <head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
    <title>Manage Settings</title>
    <style type="text/css">
.error {
  color: red;
}
form span.setting_name {
  width: 300px;
}
    </style>
  </head>
  <body>
    <h1>Manage Settings</h1>

    <?php
    if (isset($setting_error))
    {
      echo "<p><span class=\"error\">{$setting_error}</span></p>\n";
    }

    foreach ($all_settings as $name => $value)
    {
      echo <<<SETTING_FORM
    <form method="post">
      <span class="setting_name">{$name}</span>
      <input type="hidden" name="name" maxlength=50 size=50 value="{$name}"/>
      <input type="text" name="value" maxlength=250 size=50 value="{$value}"/>
      <input type="submit" name="set" value="Update"/>
    </form>
SETTING_FORM;
    }
    ?>
    <b>Add New Setting</b><br/>
    <form method="post">
      <label for="name">Name</label>
      <input type="text" name="name" maxlength=50 size=50/>
      <label for="value">Value</label>
      <input type="text" name="value" maxlength=250 size=50"/>
      <input type="submit" name="set" value="Add"/>
    </form>
  </body>
</html>
