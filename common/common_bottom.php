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
?>
      <footer class="site-footer" role="contentinfo">
			  <div class="site-info">
					Copyright© 2017 <em>Madeline's Party</em>, a 501(c)3 non-profit organization
			  </div><!-- .site-info -->
		  </footer>
    </div> <!-- container -->
    <?php
    if (isset($mp_include_javascript))
    {
      foreach ($mp_include_javascript as $script)
      {
        echo '<script type="text/javascript" src="'.$mp_root_path.'/js/'.$script.'"></script>';
      }
    } ?>

  </body>
</html>
