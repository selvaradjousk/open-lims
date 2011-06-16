<?php
/**
 * @package data
 * @version 0.4.0.0
 * @author Roman Konertz
 * @copyright (c) 2008-2010 by Roman Konertz
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
 * Data Wrapper Access Class
 * @package data
 */
class Data_Wrapper_Access
{
		/**
	 * @param integer $folder_id
	 * @return array
	 */
	public static function get_images_in_folder($folder_id)
	{
		global $db;
			
		if (is_numeric($folder_id))
		{
			$return_array = array();
			
			$sql = "SELECT ".constant("FILE_TABLE").".id FROM ".constant("DATA_ENTITY_HAS_DATA_ENTITY_TABLE")." " .
					"JOIN ".constant("FOLDER_TABLE")." 			ON ".constant("DATA_ENTITY_HAS_DATA_ENTITY_TABLE").".data_entity_pid	= ".constant("FOLDER_TABLE").".data_entity_id " .
					"JOIN ".constant("FILE_TABLE")." 			ON ".constant("DATA_ENTITY_HAS_DATA_ENTITY_TABLE").".data_entity_cid	= ".constant("FILE_TABLE").".data_entity_id " .
					"JOIN ".constant("FILE_VERSION_TABLE")." 	ON ".constant("FILE_TABLE").".id 										= ".constant("FILE_VERSION_TABLE").".toid " .
						"WHERE ".constant("FILE_VERSION_TABLE").".current = 't' AND " .
								"".constant("FOLDER_TABLE").".id = ".$folder_id." AND " .
									"(LOWER(".constant("FILE_VERSION_TABLE").".file_extension) = 'jpg' OR " .
									"LOWER(".constant("FILE_VERSION_TABLE").".file_extension) = 'jpeg' OR " .
									"LOWER(".constant("FILE_VERSION_TABLE").".file_extension) = 'png' OR " .
									"LOWER(".constant("FILE_VERSION_TABLE").".file_extension) = 'bmp' OR " .
									"LOWER(".constant("FILE_VERSION_TABLE").".file_extension) = 'tiff' OR " .
									"LOWER(".constant("FILE_VERSION_TABLE").".file_extension) = 'tif' OR " .
									"LOWER(".constant("FILE_VERSION_TABLE").".file_extension) = 'gif')";				
			
			$res = $db->db_query($sql);
			while ($data = $db->db_fetch_assoc($res))
			{
				array_push($return_array, $data[id]);
			}
							
			if (count($return_array) >= 1)
			{
				return $return_array;
			}
			else
			{
				return null;
			}	
		}
		else
		{
			return null;
		}
	}

	/**
	 * NEW
	 * @param integer $folder_id
	 * @param string $name
	 * @return bool
	 */
	public static function is_file_in_folder($folder_id, $name)
	{
		global $db;
		
		if (is_numeric($folder_id) and $name)
		{
			$name = strtolower(trim($name));
			
			$sql = "SELECT ".constant("FILE_TABLE").".id FROM ".constant("DATA_ENTITY_HAS_DATA_ENTITY_TABLE")." " .
					"JOIN ".constant("FOLDER_TABLE")." 			ON ".constant("DATA_ENTITY_HAS_DATA_ENTITY_TABLE").".data_entity_pid	= ".constant("FOLDER_TABLE").".data_entity_id " .
					"JOIN ".constant("FILE_TABLE")." 			ON ".constant("DATA_ENTITY_HAS_DATA_ENTITY_TABLE").".data_entity_cid	= ".constant("FILE_TABLE").".data_entity_id " .
					"JOIN ".constant("FILE_VERSION_TABLE")." 	ON ".constant("FILE_TABLE").".id 										= ".constant("FILE_VERSION_TABLE").".toid " .
						"WHERE ".constant("FILE_VERSION_TABLE").".current = 't' AND " .
								"".constant("FOLDER_TABLE").".id = ".$folder_id." AND " .
									"LOWER(TRIM(".constant("FILE_VERSION_TABLE").".name)) = '".$name."'";	

			$res = $db->db_query($sql);
			$data = $db->db_fetch_assoc($res);
			
			if ($data[id])
			{
				return true;
			}
			else
			{
				return false;
			}
		}
		else
		{
			return true;
		}
	}
	
	/**
	 * @param integer $data_entity_pid
	 * @param string $order_by
	 * @param string $order_method
	 * @param integer $start
	 * @param integer $end
	 */
	public static function list_data_entity_childs($data_entity_pid, $order_by, $order_method, $start, $end)
	{
		global $db;
		
		if (is_numeric($data_entity_pid))
		{
			if ($order_by and $order_method)
			{
				if ($order_method == "asc")
				{
					$sql_order_method = "ASC";
				}
				else
				{
					$sql_order_method = "DESC";
				}
				
				switch($order_by):
				
					case "name":
						$sql_order_by = "ORDER BY name ".$sql_order_method;
					break;
					
					case "datetime":
						$sql_order_by = "ORDER BY datetime ".$sql_order_method;
					break;
					
					case "size":
						$sql_order_by = "ORDER BY size ".$sql_order_method;
					break;
					
					case "owner":
						$sql_order_by = "ORDER BY ".constant("USER_PROFILE_TABLE").".surname ".$sql_order_method;
					break;
				
					case "type":
						if ($order_method == "asc")
						{
							$sql_order_by = "ORDER BY ".constant("VIRTUAL_FOLDER_TABLE").".id, ".constant("FOLDER_TABLE").".id, ".constant("VALUE_TABLE").".id, ".constant("FILE_TABLE").".id";
						}
						else
						{
							$sql_order_by = "ORDER BY ".constant("FILE_TABLE").".id, ".constant("VALUE_TABLE").".id, ".constant("FOLDER_TABLE").".id, ".constant("VIRTUAL_FOLDER_TABLE").".id";
						}
					break;
					
					default:
						$sql_order_by = "ORDER BY ".constant("FILE_TABLE").".id, ".constant("VALUE_TABLE").".id, ".constant("FOLDER_TABLE").".id, ".constant("VIRTUAL_FOLDER_TABLE").".id";
					break;
				
				endswitch;
			}
			
			$sql = "SELECT CONCAT( " .
								"CONCAT(".constant("FOLDER_TABLE").".name, " .
										"".constant("VIRTUAL_FOLDER_TABLE").".name ), " .
								"CONCAT(".constant("FILE_VERSION_TABLE").".name, " .
										"".constant("VALUE_TYPE_TABLE").".name) " .
									") AS name, " .
							"current_entity.datetime AS datetime, " .
							"current_entity.owner_id AS owner_id, " .
							"".constant("FILE_VERSION_TABLE").".size AS size, " .
							"".constant("DATA_ENTITY_TABLE").".permission, " .
							"".constant("DATA_ENTITY_TABLE").".automatic, " .
							"".constant("FOLDER_TABLE").".id AS folder_id, " .
							"".constant("FILE_TABLE").".id AS file_id, " .
							"".constant("VALUE_TABLE").".id AS value_id, " .
							"".constant("VIRTUAL_FOLDER_TABLE").".id AS virtual_folder_id " .
						 "FROM ".constant("DATA_ENTITY_TABLE")." " .
						"LEFT JOIN ".constant("DATA_ENTITY_HAS_DATA_ENTITY_TABLE")." 	ON ".constant("DATA_ENTITY_TABLE").".id 								= ".constant("DATA_ENTITY_HAS_DATA_ENTITY_TABLE").".data_entity_pid " .
						"JOIN ".constant("DATA_ENTITY_TABLE")." AS current_entity		ON ".constant("DATA_ENTITY_HAS_DATA_ENTITY_TABLE").".data_entity_cid	= current_entity.id " .
						"LEFT JOIN ".constant("FOLDER_TABLE")."							ON ".constant("DATA_ENTITY_HAS_DATA_ENTITY_TABLE").".data_entity_cid	= ".constant("FOLDER_TABLE").".data_entity_id " .
						"LEFT JOIN ".constant("FILE_TABLE")." 							ON ".constant("DATA_ENTITY_HAS_DATA_ENTITY_TABLE").".data_entity_cid 	= ".constant("FILE_TABLE").".data_entity_id " .
						"LEFT JOIN ".constant("FILE_VERSION_TABLE")." 					ON ".constant("FILE_TABLE").".id 										= ".constant("FILE_VERSION_TABLE").".toid " .
						"LEFT JOIN ".constant("VALUE_TABLE")." 							ON ".constant("DATA_ENTITY_HAS_DATA_ENTITY_TABLE").".data_entity_cid 	= ".constant("VALUE_TABLE").".data_entity_id " . 
						"LEFT JOIN ".constant("VALUE_TYPE_TABLE")." 					ON ".constant("VALUE_TABLE").".type_id 									= ".constant("VALUE_TYPE_TABLE").".id " .
						"LEFT JOIN ".constant("VALUE_VERSION_TABLE")." 					ON ".constant("VALUE_TABLE").".id 										= ".constant("VALUE_VERSION_TABLE").".toid " .
						"LEFT JOIN ".constant("VIRTUAL_FOLDER_TABLE")."					ON ".constant("DATA_ENTITY_HAS_DATA_ENTITY_TABLE").".data_entity_cid 	= ".constant("VIRTUAL_FOLDER_TABLE").".data_entity_id " .
						"LEFT JOIN ".constant("USER_PROFILE_TABLE")."					ON current_entity.owner_id											 	= ".constant("USER_PROFILE_TABLE").".id " .
						"WHERE " .
							"(".constant("FOLDER_TABLE").".id IS NOT NULL OR " .
							"".constant("VIRTUAL_FOLDER_TABLE")." IS NOT NULL OR " .
							"".constant("FILE_VERSION_TABLE").".current = 't' OR " .
							"".constant("VALUE_VERSION_TABLE").".current = 't') " .
						"AND " .
						"".constant("DATA_ENTITY_TABLE").".id = ".$data_entity_pid." " .
						"".$sql_order_by."";

			$return_array = array();
			
			$res = $db->db_query($sql);
			
			if (is_numeric($start) and is_numeric($end))
			{
				for ($i = 0; $i<=$end-1; $i++)
				{
					if (($data = $db->db_fetch_assoc($res)) == null)
					{
						break;
					}
					
					if ($i >= $start)
					{
						array_push($return_array, $data);
					}
				}
			}
			else
			{
				while ($data = $db->db_fetch_assoc($res))
				{
					array_push($return_array, $data);
				}
			}
			return $return_array;
		}
		else
		{
			return null;
		}
	}
	
	/**
	 * @param integer $data_entity_pid
	 * @return integer
	 */
	public static function count_list_data_entity_childs($data_entity_pid)
	{
		global $db;
		
		if (is_numeric($data_entity_pid))
		{
			$sql = "SELECT  COUNT(".constant("DATA_ENTITY_TABLE").".id) AS result " .
						 "FROM ".constant("DATA_ENTITY_TABLE")." " .
						"LEFT JOIN ".constant("DATA_ENTITY_HAS_DATA_ENTITY_TABLE")." ON ".constant("DATA_ENTITY_TABLE").".id = ".constant("DATA_ENTITY_HAS_DATA_ENTITY_TABLE").".data_entity_pid " .
						"WHERE ".constant("DATA_ENTITY_TABLE").".id = ".$data_entity_pid."";

			$res = $db->db_query($sql);
			$data = $db->db_fetch_assoc($res);
	
			return $data[result];
		}
		else
		{
			return null;
		}
	}
	
	/**
	 * @param integer $folder_id
	 * @return array
	 */
	public static function list_virtual_folders_by_folder_id($folder_id)
	{
		global $db;
		
		if (is_numeric($folder_id))
		{
			$sql = "SELECT ".constant("VIRTUAL_FOLDER_TABLE").".id FROM ".constant("VIRTUAL_FOLDER_TABLE")." " .
					"JOIN ".constant("DATA_ENTITY_HAS_DATA_ENTITY_TABLE")." ON ".constant("VIRTUAL_FOLDER_TABLE").".data_entity_id 					= ".constant("DATA_ENTITY_HAS_DATA_ENTITY_TABLE").".data_entity_cid " .
					"JOIN ".constant("FOLDER_TABLE")."						ON ".constant("DATA_ENTITY_HAS_DATA_ENTITY_TABLE").".data_entity_pid 	= ".constant("FOLDER_TABLE").".data_entity_id " .
					"WHERE ".constant("FOLDER_TABLE").".id = ".$folder_id."";

			$return_array = array();
			
			$res = $db->db_query($sql);
			while($data = $db->db_fetch_assoc($res))
			{
				array_push($return_array, $data[id]);
			}

			return $return_array;
		}
		else
		{
			return null;
		}
	}
	
	/**
	 * NEW
	 * @param integer $folder_id
	 * @param string $search_string
	 * @param string $order_by
	 * @param string $order_method
	 * @param integer $start
	 * @param integer $end
	 * @return array
	 */
	public static function list_search_ffv($folder_id, $search_string, $order_by, $order_method, $start, $end)
	{
		global $db;

		if (is_numeric($folder_id) and $search_string)
		{
			$search_string = str_replace("*", "%", $search_string);
			$search_string = strtolower(trim($search_string));
			
			if ($order_by and $order_method)
			{
				if ($order_method == "asc")
				{
					$sql_order_method = "ASC";
				}
				else
				{
					$sql_order_method = "DESC";
				}
				
				switch($order_by):
				
					case "name":
						$sql_order_by = "ORDER BY name ".$sql_order_method;
					break;
					
					case "datetime":
						$sql_order_by = "ORDER BY datetime ".$sql_order_method;
					break;
					
					case "size":
						$sql_order_by = "ORDER BY ".constant("FILE_VERSION_TABLE").".size ".$sql_order_method;
					break;
					
					case "owner":
						$sql_order_by = "ORDER BY ".constant("USER_PROFILE_TABLE").".surname ".$sql_order_method;
					break;
				
					default:
						$sql_order_by = "ORDER BY datetime";
					break;
				
				endswitch;
			}
			else
			{
				$sql_order_by = "ORDER BY datetime";
			}
			
			$sql = "SELECT ".constant("DATA_ENTITY_TABLE").".id AS id, " .
						"CONCAT(".constant("FILE_VERSION_TABLE").".name, ".constant("VALUE_TYPE_TABLE").".name) AS name, " .
						"".constant("DATA_ENTITY_TABLE").".datetime AS datetime, " .
						"".constant("DATA_ENTITY_TABLE").".owner_id AS owner, " .
						"".constant("FILE_TABLE").".id AS file_id, " .
						"".constant("VALUE_TABLE").".id AS value_id " .
						"FROM ".constant("DATA_ENTITY_TABLE")." " .
						"LEFT JOIN ".constant("FILE_TABLE")." 			ON ".constant("DATA_ENTITY_TABLE").".id 		= ".constant("FILE_TABLE").".data_entity_id " .
						"LEFT JOIN ".constant("FILE_VERSION_TABLE")."	ON ".constant("FILE_TABLE").".id 				= ".constant("FILE_VERSION_TABLE").".toid " .
						"LEFT JOIN ".constant("VALUE_TABLE")." 			ON ".constant("DATA_ENTITY_TABLE").".id 		= ".constant("VALUE_TABLE").".data_entity_id " .
						"LEFT JOIN ".constant("VALUE_VERSION_TABLE")." 	ON ".constant("VALUE_TABLE").".id 				= ".constant("VALUE_VERSION_TABLE").".toid " .
						"LEFT JOIN ".constant("VALUE_TYPE_TABLE")." 	ON ".constant("VALUE_TABLE").".type_id 			= ".constant("VALUE_TYPE_TABLE").".id " .
						"LEFT JOIN ".constant("USER_PROFILE_TABLE")." 	ON ".constant("DATA_ENTITY_TABLE").".owner_id 	= ".constant("USER_PROFILE_TABLE").".id " .					
						"WHERE (".constant("DATA_ENTITY_TABLE").".id IN (" .
									"SELECT ".constant("DATA_ENTITY_HAS_DATA_ENTITY_TABLE").".data_entity_cid " .
									"FROM ".constant("DATA_ENTITY_HAS_DATA_ENTITY_TABLE")." " .
									"WHERE ".constant("DATA_ENTITY_HAS_DATA_ENTITY_TABLE").".data_entity_pid = ( " .
												"SELECT ".constant("FOLDER_TABLE").".data_entity_id " .
												"FROM ".constant("FOLDER_TABLE")." " .
												"WHERE ".constant("FOLDER_TABLE").".id = ".$folder_id." ". 
												") " .
									") OR ".
									"".constant("DATA_ENTITY_TABLE").".id IN (" .
									"SELECT ".constant("DATA_ENTITY_HAS_DATA_ENTITY_TABLE").".data_entity_cid " .
									"FROM ".constant("DATA_ENTITY_HAS_DATA_ENTITY_TABLE")." " .
									"WHERE ".constant("DATA_ENTITY_HAS_DATA_ENTITY_TABLE").".data_entity_pid IN ( " .
												"SELECT * FROM search_get_sub_folders(" .
															"(SELECT ".constant("FOLDER_TABLE").".data_entity_id " .
															"FROM ".constant("FOLDER_TABLE")." " .
															"WHERE ".constant("FOLDER_TABLE").".id = ".$folder_id.") ". 
													")" .
												")" .
									")" .
								") " .
								"AND (".constant("FILE_VERSION_TABLE").".internal_revision = ( " .
											"SELECT max(internal_revision) " .
											"FROM ".constant("FILE_VERSION_TABLE")." " .
											"WHERE toid = ".constant("FILE_TABLE").".id " .
											") OR ".constant("VALUE_VERSION_TABLE").".internal_revision =  (" .
											"SELECT max(internal_revision) " .
											"FROM ".constant("VALUE_VERSION_TABLE")." " .
											"WHERE toid = ".constant("VALUE_TABLE").".id " .
											")" .
										") ".
								"AND (".constant("FILE_VERSION_TABLE").".name LIKE '".$search_string."' OR ".constant("VALUE_TYPE_TABLE").".name LIKE '".$search_string."') " .
								"".$sql_order_by."";
						
			$return_array = array();
			
			$res = $db->db_query($sql);
			
			if (is_numeric($start) and is_numeric($end))
			{
				for ($i = 0; $i<=$end-1; $i++)
				{
					if (($data = $db->db_fetch_assoc($res)) == null)
					{
						break;
					}
					
					if ($i >= $start)
					{
						array_push($return_array, $data);
					}
				}
			}
			else
			{
				while ($data = $db->db_fetch_assoc($res))
				{
					array_push($return_array, $data);
				}
			}
			return $return_array;
		}
	}
	
	/**
	 * NEW
	 * @param integer $folder_id
	 * @param string $search_string
	 * @return integer
	 */
	public static function count_search_ffv($folder_id, $search_string)
	{
		global $db;
		
		if (is_numeric($folder_id) and $search_string)
		{
			$search_string = str_replace("*", "%", $search_string);
			$search_string = strtolower(trim($search_string));
			
			$sql = "SELECT COUNT(".constant("DATA_ENTITY_TABLE").".id) AS result " .
						"FROM ".constant("DATA_ENTITY_TABLE")." " .
						"LEFT JOIN ".constant("FILE_TABLE")." 			ON ".constant("DATA_ENTITY_TABLE").".id 		= ".constant("FILE_TABLE").".data_entity_id " .
						"LEFT JOIN ".constant("FILE_VERSION_TABLE")."	ON ".constant("FILE_TABLE").".id 				= ".constant("FILE_VERSION_TABLE").".toid " .
						"LEFT JOIN ".constant("VALUE_TABLE")." 			ON ".constant("DATA_ENTITY_TABLE").".id 		= ".constant("VALUE_TABLE").".data_entity_id " .
						"LEFT JOIN ".constant("VALUE_VERSION_TABLE")." 	ON ".constant("VALUE_TABLE").".id 				= ".constant("VALUE_VERSION_TABLE").".toid " .
						"LEFT JOIN ".constant("VALUE_TYPE_TABLE")." 	ON ".constant("VALUE_TABLE").".type_id 			= ".constant("VALUE_TYPE_TABLE").".id " .			
						"WHERE (".constant("DATA_ENTITY_TABLE").".id IN (" .
									"SELECT ".constant("DATA_ENTITY_HAS_DATA_ENTITY_TABLE").".data_entity_cid " .
									"FROM ".constant("DATA_ENTITY_HAS_DATA_ENTITY_TABLE")." " .
									"WHERE ".constant("DATA_ENTITY_HAS_DATA_ENTITY_TABLE").".data_entity_pid = ( " .
												"SELECT ".constant("FOLDER_TABLE").".data_entity_id " .
												"FROM ".constant("FOLDER_TABLE")." " .
												"WHERE ".constant("FOLDER_TABLE").".id = ".$folder_id." ". 
												") " .
									") OR ".
									"".constant("DATA_ENTITY_TABLE").".id IN (" .
									"SELECT ".constant("DATA_ENTITY_HAS_DATA_ENTITY_TABLE").".data_entity_cid " .
									"FROM ".constant("DATA_ENTITY_HAS_DATA_ENTITY_TABLE")." " .
									"WHERE ".constant("DATA_ENTITY_HAS_DATA_ENTITY_TABLE").".data_entity_pid IN ( " .
												"SELECT * FROM search_get_sub_folders(" .
															"(SELECT ".constant("FOLDER_TABLE").".data_entity_id " .
															"FROM ".constant("FOLDER_TABLE")." " .
															"WHERE ".constant("FOLDER_TABLE").".id = ".$folder_id.") ". 
													")" .
												")" .
									")" .
								") " .
								"AND (".constant("FILE_VERSION_TABLE").".internal_revision = ( " .
											"SELECT max(internal_revision) " .
											"FROM ".constant("FILE_VERSION_TABLE")." " .
											"WHERE toid = ".constant("FILE_TABLE").".id " .
											") OR ".constant("VALUE_VERSION_TABLE").".internal_revision =  (" .
											"SELECT max(internal_revision) " .
											"FROM ".constant("VALUE_VERSION_TABLE")." " .
											"WHERE toid = ".constant("VALUE_TABLE").".id " .
											")" .
										") ".
								"AND (".constant("FILE_VERSION_TABLE").".name LIKE '".$search_string."' OR ".constant("VALUE_TYPE_TABLE").".name LIKE '".$search_string."') " .
								"".$sql_order_by."";
			
			$res = $db->db_query($sql);
			$data = $db->db_fetch_assoc($res);
	
			return $data[result];
		}
	}
	
	/**
	 * NEW
	 * @param string $order_by
	 * @param string $order_method
	 * @param integer $start
	 * @param integer $end
	 * @return array
	 */
	public static function list_value_templates($order_by, $order_method, $start, $end)
	{
		global $db;
			
		if ($order_by and $order_method)
		{
			if ($order_method == "asc")
			{
				$sql_order_method = "ASC";
			}
			else
			{
				$sql_order_method = "DESC";
			}
			
			switch($order_by):
			
				case "id":
					$sql_order_by = "ORDER BY id ".$sql_order_method;
				break;
			
				case "name":
					$sql_order_by = "ORDER BY name ".$sql_order_method;
				break;
			
				case "file":
					$sql_order_by = "ORDER BY file ".$sql_order_method;
				break;
							
				default:
					$sql_order_by = "ORDER BY id ".$sql_order_method;
				break;
			
			endswitch;	
		}
		else
		{
			$sql_order_by = "ORDER BY id";
		}
			
		$sql = "SELECT ".constant("VALUE_TYPE_TABLE").".id AS id, " .
					"".constant("VALUE_TYPE_TABLE").".name AS name," .
					"".constant("FILE_VERSION_TABLE").".name AS file " .
					"FROM ".constant("VALUE_TYPE_TABLE")." " .
					"LEFT JOIN ".constant("OLVDL_TEMPLATE_TABLE")." ON ".constant("VALUE_TYPE_TABLE").".template_id 		= ".constant("OLVDL_TEMPLATE_TABLE").".id " .
					"LEFT JOIN ".constant("DATA_ENTITY_TABLE")." 	ON ".constant("OLVDL_TEMPLATE_TABLE").".data_entity_id 	= ".constant("DATA_ENTITY_TABLE").".id " .
					"LEFT JOIN ".constant("FILE_TABLE")." 			ON ".constant("DATA_ENTITY_TABLE").".id 				= ".constant("FILE_TABLE").".data_entity_id " .
					"LEFT JOIN ".constant("FILE_VERSION_TABLE")." 	ON ".constant("FILE_TABLE").".id 						= ".constant("FILE_VERSION_TABLE").".toid " .					
					"WHERE ".constant("FILE_VERSION_TABLE").".internal_revision = 1 " .
					"".$sql_order_by."";
		
		$return_array = array();
		
		$res = $db->db_query($sql);
		
		if (is_numeric($start) and is_numeric($end))
		{
			for ($i = 0; $i<=$end-1; $i++)
			{
				if (($data = $db->db_fetch_assoc($res)) == null)
				{
					break;
				}
				
				if ($i >= $start)
				{
					array_push($return_array, $data);
				}
			}
		}
		else
		{
			while ($data = $db->db_fetch_assoc($res))
			{
				array_push($return_array, $data);
			}
		}
		return $return_array;
	}

	/**
	 * NEW
	 * @return array
	 */
	public static function count_list_value_templates()
	{
		global $db;
						
		$sql = "SELECT COUNT(".constant("VALUE_TYPE_TABLE").".id) AS result " .
					"FROM ".constant("VALUE_TYPE_TABLE")."";
		
		$res = $db->db_query($sql);
		$data = $db->db_fetch_assoc($res);

		return $data[result];
	}	
	
}

?>