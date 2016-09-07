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

// If mode is "GET", then display the form
// If mode is "POST", then parse the uploaded CSV file and replace contents of
// the database with the values
//
// CSV format
// Each line has one of "family", "person", or "item" to indicate the data type.
// "person" lines represent members of a family.
// "item" lines represent wish list items for the family member.
// A "family" line must appear before any "member" lines.
// A "person" line must appear before any "item" lines.
// Lines with a "#" as the first character of the data type are ignored
//
// Line formats
// "family", family_name
// "person", age, gender ("m", "f", or "g" for group), name
// "item", category, description
require_once '../common/dbconnect.php';

$data_posted = false;
$upload_error = null;

if (isset($_POST["Upload"]))
{
  $data_posted = true;
  $replace_data = isset($_POST['replace']) && ($_POST['replace'] == "true");
  $upload_error = format_error(process_post($mp_db, $replace_data));
}
$page_url = $_SERVER['REQUEST_URI'];

function process_post($db, $replace_data)
{
  // Check to make sure a file was uploaded
  if ($_FILES['listfile'] && $_FILES['listfile']['tmp_name'])
  {
    $filename = $_FILES['listfile']['tmp_name'];
    try
    {
      // Start transaction
      if (!$db->beginTransaction())
      {
        return 'Couldn\'t begin database transaction';
      }

      // Delete existing records
      if ($replace_data)
      {
        if ($db->exec('DELETE FROM families')===FALSE)
        {
          throw new Exception('Couldn\'t delete existing list items');
        }
      }

      import_wish_list_csv($filename, $db);

      // commit transaction
      if (!$db->commit())
      {
        throw new Exception('Couldn\'t commit to database');
      }
    }
    catch (Exception $e)
    {
      // rollback transaction
      $db->rollback();
      $upload_error = $e->getMessage();
    }
  }
  else
  {
    $upload_error = 'No file uploaded';
  }

  return $upload_error;
}

function import_wish_list_csv($filename, $db)
{
  $in = fopen($filename, "r");
  if (!$in) { throw new Exception( 'Can\'t open file' ); }

  $current_family = NULL;
  $current_member = NULL;
  
  $insert_family_stmt = $db->prepare('INSERT INTO families (name) VALUES (:name)');
  $insert_person_stmt = $db->prepare('INSERT INTO family_members (family_id,age,gender,name) VALUES (:family,:age,:gender,:name)');
  $insert_item_stmt = $db->prepare('INSERT INTO list_items (family_member_id,category,description) VALUES (:family_member_id,:category,:description)');

  while ($line = fgetcsv($in))
  {
    $fields = get_wish_list_fields($line);

    // Skip empty and comment lines
    if (count($fields) == 0 || substr($fields[0], 0, 1) == "#") { continue; }

    // Act on the keyword
    switch ($fields[0])
    {
      case "family":
        if (count($fields) != 2)
        {
          throw new Exception( 'Incorrect field count for "family" keyword: '.htmlentities($line));
        }
        $name = $fields[1];

        if (!$insert_family_stmt->execute(array(':name' => $name)))
        {
          throw new Exception('Couldn\'t insert family');
        }
        $current_family = $db->lastInsertId();
        $current_member = NULL;

        break;

      case "person":
        if (count($fields) != 4)
        {
          throw new Exception('Incorrect field count for "person" keyword: '.htmlentities($line));
        }
        $age = $fields[1];
        if (!is_numeric($age))
        { 
          throw new Exception('Age must be numeric');
        }
        $gender = $fields[2];
        if ($gender != 'm' && $gender != 'f' && $gender != 'g')
        {
          throw new Exception('Gender must be \'m\', \'f\' or \'g\'');
        }
        $name = $fields[3];

        if ($current_family == NULL)
        {
          throw new Exception("No family for member '{$name}'");
        }

        if (!$insert_person_stmt->execute(array(':family' => $current_family,
                                                ':age' => $age,
                                                ':gender' => $gender,
                                                ':name' => $name)))
        {
          throw new Exception('Couldn\'t insert family member');
        }
        $current_member = $db->lastInsertId();
        
        break;

      case "item":
        if (count($fields) != 3)
        {
          throw new Exception('Incorrect field count for "item" keyword: '.htmlentities($line));
        }
        $category = $fields[1];
        $description = $fields[2];

        if ($current_member == NULL)
        {
          throw new Exception("No family member for item '{$description}'");
        }
        if ($current_family == NULL)
        {
          throw new Exception( "No family for item '{$description}'" );
        }

        if (!$insert_item_stmt->execute(array(':family_member_id' => $current_member,
                                              ':category' => $category,
                                              ':description' => $description)))
        {
          throw new Exception('Couldn\'t insert wish list item');
        }

        break;

      default:
        throw new Exception("Unknown keyword ".htmlentities($fields[0])." found");
    }

  }

  fclose($in);
}

function get_wish_list_fields($line)
{
//  foreach ( str_getcsv( $line ) as $field )
  $out_fields = null;
  foreach ($line as $field)
  {
    $value = htmlentities(trim($field));
    if ($value != "")
    {
      $out_fields[] = $value;
    }
  }
  return $out_fields;
}

function format_error( $err )
{
  return ( $err ) ? "<p>{$err}</p><br/>" : NULL;
}

?>
<html>
  <head>
    <title>Wish List Upload</title>
  </head>
  <body>
    <h1>Upload New Wish List Data</h1>
    
<?php
if ( $data_posted && !$upload_error )
{
  echo <<<ENDPOST
    <p>Wish list data successfully posted.</p>
ENDPOST;
}
else
{
  echo <<<FORMDISPLAY
    $upload_error
    <form enctype="multipart/form-data" action="{$page_url}" method="POST">
      <label for="listfile">List File</label>
      <input name="listfile" type="file"/><br/>
      <label for="replace">Replace Data</label>
      <input name="replace" type="checkbox" value="true"/><br>
      <input name="Upload" type="submit"/>
    </form>
FORMDISPLAY;
}
?>

  </body>
</html>