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

require_once "common/config.php";
require_once "common/dbconnect.php";
require_once "common/login_support.php";
require_once "common/misc.php";
require_once "common/wish_list_tables.php";

$page_url = url_no_params($_SERVER['REQUEST_URI']);
$display_mode = isset($_GET['mode']) ? $_GET['mode'] : "category";

$mp_page_name = "Wish List";
$mp_include_javascript = array('jquery.min.js', 'list.js');
include "common/common_top.php"
?>

<!-- Jumbotron -->
<div class="page-header">
  <h1>Madeline's Party</h1>
  <h2>Celebrating 20 Years of Service</h2>
</div>

<?php login_display($mp_db) ?>

<div class="row">
  <div class="col-md-4 col-md-offset-4">
    <a role="button" class="btn gift-btn btn-lg btn-block" href="<?php echo $page_url ?>?mode=<?php echo ($display_mode == "category") ? "age_group" : "category"; ?>">
      <span class="glyphicon glyphicon-tasks" aria-hidden="true"></span> Show by <?php echo ($display_mode == "category") ? "Age Group" : "Category"; ?>
    </a>
  </div>
</div>

<?php tables_for_mode($mp_db, $display_mode, $mp_current_user) ?>

    
<?php
include "common/common_bottom.php";
?>
