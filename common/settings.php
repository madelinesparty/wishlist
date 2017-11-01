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

function setting_get($name, $default = "")
{
  global $mp_db;

  $stmt = $mp_db->prepare("SELECT * FROM settings WHERE name = :name LIMIT 1");
  $result = $stmt->execute(array(':name' => strtoupper($name)));
  if ( $result && $stmt->rowCount() > 0)
  {
    $entries = $stmt->fetchAll();
    return $entries[0]['value'];
  }
  return $default;
}

function setting_get_all()
{
  global $mp_db;

  $rv = array();

  $stmt = $mp_db->query("SELECT * FROM settings");
  foreach($stmt as $row)
  {
    $rv[$row['name']] = $row['value'];
  }

  return $rv;
}

function setting_exists($name)
{
  return (setting_get($name, null) != null);
}

function setting_set($name, $value, $allow_update = false)
{
  global $mp_db;

  $value = htmlentities(stripslashes($value), ENT_QUOTES|ENT_HTML5, "UTF-8");

  if (setting_exists($name))
  {
    if ($allow_update)
    {
      $stmt = $mp_db->prepare("UPDATE settings SET value=:value WHERE name=:name");
    }
    else
    {
      return false;
    }
  }
  else
  {
    $stmt = $mp_db->prepare("INSERT INTO settings (name,value) VALUES (:name,:value)");
  }

  if (!$stmt->execute(array(':name' => strtoupper($name), ':value' => $value)))
  {
    return false;
  }

  return true;
}
?>
