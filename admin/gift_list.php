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
require_once '../common/dbconnect.php';

$results = null;
$format = $_GET['format'];
if ($format != "csv" && $format != "html" && $format != "")
{
  trigger_error("Unsupported 'format'");
}

$query_string = "SELECT users.name, users.email, list_items.category, list_items.description FROM users INNER JOIN list_items_users_join ON users.id=list_items_users_join.user_id INNER JOIN list_items ON list_items_users_join.list_item_id=list_items.id ORDER BY users.email, list_items.category, list_items.description";
$results = mysql_query($query_string, $mp_db);
if (!$results)
{
  trigger_error("Failed to get user list");
}

if ($contact_error)
{
  echo $contact_error;
}
elseif ($format == "csv")
{
  header("Content-Type: text/csv");
  header("Content-Disposition: attachment; filename=\"gift_list.csv\";" );

  while ($row = mysql_fetch_assoc($results))
  {
    echo "\"{$row['name']}\", \"{$row['email']}\", \"{$row['category']}\", \"{$row['description']}\"\r\n";
  }
  return;
}
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
  <head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
    <title>Gift Purchase List</title>
    <style type="text/css">
    </style>
  </head>
  <body>
    <h1>Contacts</h1>

    <table>
      <?php
      echo "<tr><td>Name</td><td>E-Mail</td><td>Category</td><td>Description</td></tr>\n";
      while ($row = mysql_fetch_assoc($results))
      {
        echo "<tr><td>{$row['name']}</td><td>{$row['email']}</td><td>{$row['category']}</td><td>{$row['description']}</td></tr>\n";
      }
      ?>
    </table>
  </body>
</html>
