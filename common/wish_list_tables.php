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

function tables_for_mode($db, $mode = "category", $for_user = NULL, $print = false )
{
  try
  {
    $filter = ( $print ) ? "WHERE list_items_users_join.user_id IS NULL" : "";
    switch ( $mode )
    {
      case "category":
        tables_for_category($db, $for_user, $filter);
        break;

      case "age_group":
        tables_for_age_group($db, $for_user, $filter);
        break;

      default:
        echo "<p>Invalid display mode</p>";
    }
  }
  catch ( Exception $e )
  {
    echo "<p>Hmmm. We seem to be having a technical problem. {$e->getMessage()}</p>";
  }
}

function tables_for_category( $db, $for_user = NULL, $filter = "" )
{
  $current_category = NULL;
  $rowtype = false;

  $qr = get_rows($db, "ORDER BY list_items.category,family_members.age,family_members.gender", $filter);
  if (!$qr || $qr->rowCount() == 0)
  {
    no_wish_list_copy();
    return;
  }

  foreach ($qr as $row)
  {
    # Close out a table if we have one going
    if ( $row['category'] != $current_category )
    {
      if ( $current_category )
      {
        echo "</tbody>\n";
        echo "</table>\n";
      }
      $current_category = $row['category'];
      $rowtype = false;
      
      # Start the table and emit the header row
      echo '<table class="wish-list table table-condensed" cellspacing="0">'."\n";
      echo '<thead><tr><th colspan="3">'.$row["category"].'</th></tr></thead>'."\n";
      echo "<tbody>\n";
    }

    render_row( $rowtype, $row, $for_user );

    $rowtype = !$rowtype;
  }
  echo "</table>\n";
}

function tables_for_age_group( $db, $for_user = NULL, $filter = "" )
{
  $__age_groups = array('max_age' => 1, 'name' => 'Infants (0 to 1yr)', 'next' =>
                  array('max_age' => 12, 'name' => 'Children (2 to 12yr)', 'next' =>
                  array('max_age' => 17, 'name' => 'Youth (13 to 17yr)', 'next' =>
                  array('max_age' => 99, 'name' => 'Adults (18+ yr)', 'next' =>
                  array('max_age' => 999, 'name' => 'Whole Family')))));

  $current_age_group = NULL;
  $rowtype = false;

  $qr = get_rows($db, "ORDER BY family_members.age,family_members.gender", $filter);
  if (!$qr || $qr->rowCount() == 0)
  {
    no_wish_list_copy();
    return;
  }

  foreach ($qr as $row)
  {
    # Close out a table if we have one going
    if ( !$current_age_group || $row['age'] > $current_age_group['max_age'] )
    {
      if ( $current_age_group )
      {
        echo "</tbody>\n";
        echo "</table>\n";
      }
      else
      {
        $current_age_group = $__age_groups;
      }
      while ( $row['age'] > $current_age_group['max_age'] )
      {
        $current_age_group = $current_age_group['next'];
      }
      $rowtype = false;

      # Start the table and emit the header row
      echo '<table class="wish-list table table-condensed" cellspacing="0">';
      echo '<thead><tr><th colspan="3">'.$current_age_group["name"].'</th></tr></thead>'."\n";
      echo "<tbody>\n";
    }

    render_row( $rowtype, $row, $for_user );

    $rowtype = !$rowtype;
  }
  echo "</table>\n";
}

function get_rows($db, $order = "", $filter = "")
{
  $select_string = "SELECT family_members.age," .
                          "family_members.gender," .
                          "list_items.id," .
                          "list_items.description," .
                          "list_items.category, " .
                          "list_items_users_join.user_id AS claimed_by " .
                   "FROM family_members " .
                   "INNER JOIN list_items ON list_items.family_member_id=family_members.id " .
                   "LEFT JOIN list_items_users_join ON list_items_users_join.list_item_id=list_items.id " .
                   $filter . " " . $order;
  $qr = $db->query($select_string);
  return $qr;
}

function render_row( $even_row, $row, $for_user = NULL )
{
  $__even_odd = array( false => "odd", true => "even" );
  $__boy_girl = array( "m" => "boy", "f" => "girl" );
  $__man_woman = array( "m" => "man", "f" => "woman" );

  $gift_id = $row['id'];
  $age = $row['age'];
  $claimed_by = $row['claimed_by'];

  $claimed_class = isset($claimed_by) ? " claimed" : "";
  $claim_type_class = (isset($for_user) && $claimed_by == $for_user['id']) ? " my-claim" : "";

  echo '<tr class="'.$claimed_class.$claim_type_class.'" id="gift_'.$gift_id.'">';
  if ($row['gender'] == 'g')
  {
    $age_gender = "Family";
  }
  elseif ( $age == 0 )
  {
    $age_gender = "Infant {$__boy_girl[$row['gender']]}";
  }
  elseif ( $age >= 18 )
  {
    $age_gender = "Adult {$__man_woman[$row['gender']]}";
  }
  else
  {
    $age_gender = "{$age}yr {$__boy_girl[$row['gender']]}";
  }
  if (isset($claimed_by)) {
    $claimed_text = $claimed_by == $for_user['id'] ? "Your Claim" : "Claimed";
    $button_class = "btn-default";
    $button_disabled = ' disabled="disabled"';
  }
  else
  {
    $claimed_text = "Claim";
    $button_class = "gift-btn";
    $button_disabled = '';
  }
  echo '<td class="col-sm-1">';
  if ($for_user)
  {
    echo '<button class="btn '.$button_class.' btn-sm btn-block"'.$button_disabled.' type="button" id="gift_'.$gift_id.'_claim" data-gift-id="'.$gift_id.'">'.$claimed_text.'</button>';
  }
  elseif ($claimed_by)
  {
    echo 'Claimed';
  }
  echo '</td>';
  echo '<td class="age_gender col-sm-3">'.$age_gender.'</td>';
  echo '<td class="description col-sm-8">'.$row['description'].'</td>';
  echo "</tr>\n";
}

function no_wish_list_copy()
{
  echo <<<NO_WISH_LIST
    <h3>Coming Soon</h3>
    <p>Please visit again later. We are busy gathering a new list for the
      upcoming season.
    </p>
NO_WISH_LIST;
}

?>
