<?php
/**
 * @package base
 * @version 0.4.0.0
 * @author Roman Konertz <konertz@open-lims.org>
 * @copyright (c) 2008-2011 by Roman Konertz
 * @license GPLv3
 * 
 * This file is part of Open-LIMS
 * Available at http://www.open-lims.org
 * 
 * This program is free software;
 * you can redistribute it and/or modify it under the terms of the GNU General Public License as published by the Free Software Foundation;
 * version 3 of the License.
 * 
 * This program is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY;
 * without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. 
 * See the GNU General Public License for more details.
 * 
 * You should have received a copy of the GNU General Public License along with this program;
 * if not, see <http://www.gnu.org/licenses/>.
 */

/**
 * Base Include Admin IO Class
 * @package base
 */
class AdminBaseIncludeIO
{
	public static function home()
	{		
		$list = new List_IO("BaseAdminIncludeHome" ,"/core/modules/base/admin/admin_base_include.ajax.php", "list_includes", "count_includes", "0", "BaseAdminIncludeAjax");

		$list->add_row("Name","name",true,null);
		$list->add_row("Folder","folder",true,null);
		$list->add_row("Event Listeners","eventlisteners",true,null);
		
		$template = new Template("template/base/admin/base_include/list.html");	
		
		$template->set_var("list", $list->get_list());
		
		$template->output();
	}
	
	public static function handler()
	{
		switch($_GET[action]):		
			default:
				self::home();
			break;
		endswitch;
	}
}