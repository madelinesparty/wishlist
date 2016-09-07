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
require_once 'common/dbconnect.php';
require_once 'common/login_support.php';

// Make sure someone is logged in
if (!isset($mp_current_user))
{
  // Return forbidden HTTP code
  header("HTTP/1.0 403 Forbidden");
  exit();
}

// Get parameters and check validity
$item_id = isset($_POST['item_id']) ? $_POST['item_id'] : (isset($_GET['item_id']) ? $_GET['item_id'] : null);
if (!$item_id || !is_numeric($item_id))
{
  header("HTTP/1.0 400 item_id parameter missing or malformed");
  exit();
}

try {
  // Add join record to mark the gift claim
  $stmt = $mp_db->prepare("INSERT INTO list_items_users_join (list_item_id,user_id) VALUES (:item_id,:user_id)");
  $stmt->execute(array(':item_id' => $item_id, ':user_id' => $mp_current_user['id']));
}
catch (PDOException $e)
{
  header("HTTP/1.0 400 It seems that someone claimed this gift while you were perusing the list");

  echo "Failed to insert database record\n";
  exit();
}

// Get gift details for confirmation email
$stmt = $mp_db->prepare("SELECT * FROM list_items WHERE id=:item_id LIMIT 1");
$stmt->execute(array(':item_id' => $item_id));
$item_info = $stmt->fetchAll()[0];

// Send confirmation email
$to = $mp_current_user['email'];
$from = "no_reply@madelinesparty.com";
$subject = "Thank you for your generosity";
$content = <<<CONFIRMATION_EMAIL_CONTENT
Thank you for your generosity by bringing the item listed below to
Madeline's Party.

  Category: {$item_info['category']}
  Description: {$item_info['description']}

Thanks again, and we'll see you at Madeline's Party!
CONFIRMATION_EMAIL_CONTENT;
$headers = "From: $from\r\n";

mail($to, $subject, $content, $headers);

echo "Your Claim";
?>
