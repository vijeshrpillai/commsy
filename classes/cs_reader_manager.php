<?PHP
// $Id$
//
// Release $Name$
//
// Copyright (c)2002-2003 Matthias Finck, Dirk Fust, Oliver Hankel, Iver Jackewitz, Michael Janneck,
// Martti Jeenicke, Detlev Krause, Irina L. Marinescu, Timo Nolte, Bernd Pape,
// Edouard Simon, Monique Strauss, José Manuel González Vázquez
//
//    This file is part of CommSy.
//
//    CommSy is free software; you can redistribute it and/or modify
//    it under the terms of the GNU General Public License as published by
//    the Free Software Foundation; either version 2 of the License, or
//    (at your option) any later version.
//
//    CommSy is distributed in the hope that it will be useful,
//    but WITHOUT ANY WARRANTY; without even the implied warranty of
//    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
//    GNU General Public License for more details.
//
//    You have received a copy of the GNU General Public License
//    along with CommSy.

/** class for database connection to the database table "reader"
 * this class implements a database manager for the table "reader". Read items
 */
class cs_reader_manager {

  /**
   * object cs_user_item - containing the current user
   */
  var $_current_user = NULL;
  var $_current_user_id = NULL;
  var $_db_connector = NULL;


  var $_rubric_id_array = array();
  var $_reader_id_array = array();
  var $_cache_on = true;

  public $_db_prefix = '';
  
   /**
    * Environment - the environment of the CommSy
    */
   var $_environment = null;

   /** constructor: cs_reader_manager
     * the only available constructor, initial values for internal variables
     *
     * @param object cs_environment the environment
     */
   function cs_reader_manager ( $environment ) {
      $this->_environment = $environment;
      $this->_current_user    = $this->_environment->getCurrentUser();
      $this->_current_user_id = $this->_current_user->getItemID();
      $this->_db_connector    = $this->_environment->getDBConnector();
   }

   /** reset limits
    * reset limits of this class
    *
    * @version $Revision$
    */
   function resetLimits () {
   }

   function resetData () {
      $this->_noticed_id_array = array();
      $this->_reader_id_array = array();
   }

   public function setCacheOff () {
      $this->_cache_on = false;
   }

   /** has the current user read a specific item
     * this method returns the latest version_id of an item, the user
     * has already read. Or false, if s/he never read this item.
     *
     * @param integer item_id    id of the item
     *
     * @return array contains the latest version_id and read_date
     */
   function getLatestReader ( $item_id ) {
      if ( in_array($item_id,$this->_rubric_id_array) ) {
         if ( array_key_exists($item_id,$this->_reader_id_array) ) {
            $reader = $this->_reader_id_array[$item_id];
         } else {
            $reader = array();
         }
         return $reader;
      } else {
         return $this->getLatestReaderForUserByID($item_id, $this->_current_user_id);
      }
   }

   function getLatestReaderByUserIDArray ($id_array, $item_id){
      if ($this->_cache_on and count($id_array)>0){
         foreach($id_array as $id){
            if (!in_array($id,$this->_rubric_id_array)){
               $this->_rubric_id_array[] = $id;
            }
         }
         $query  = 'SELECT user_id, version_id, MAX(read_date) as read_date FROM '.$this->addDatabasePrefix('reader').
                ' WHERE item_id="'.encode(AS_DB,$item_id).'"'.
                ' AND   user_id IN ('.implode(",",encode(AS_DB,$id_array)).')'.
                ' GROUP BY user_id';
                $result = $this->_db_connector->performQuery($query);
         if ( !isset($result) ) {
           include_once('functions/error_functions.php');trigger_error('Problems selecting reader from query: "'.$query.'"');
         } else {
            foreach ($result as $rs) {
               $temp = array();
               $temp[$rs['user_id']]['version_id'] = $rs['version_id'];
               $temp[$rs['user_id']]['read_date'] = $rs['read_date'];
               if (!in_array($temp,$this->_reader_id_array)){
                  $this->_reader_id_array[$rs['user_id']]['version_id'] = $rs['version_id'];
                  $this->_reader_id_array[$rs['user_id']]['read_date'] = $rs['read_date'];
               }
            }
         }
      }
   }

   function getLatestReaderByIDArray ($id_array){
      if ($this->_cache_on and count($id_array)>0){
         foreach($id_array as $id){
            if (!in_array($id,$this->_rubric_id_array)){
               $this->_rubric_id_array[] = $id;
            }
         }
         $query  = 'SELECT item_id, version_id, MAX(read_date) as read_date FROM '.$this->addDatabasePrefix('reader').
                ' WHERE item_id IN ('.implode(",",encode(AS_DB,$id_array)).')'.
                ' AND   user_id="'.encode(AS_DB,$this->_current_user_id).'"'.
                ' GROUP BY item_id';
         $result = $this->_db_connector->performQuery($query);
         if ( !isset($result) ) {
           include_once('functions/error_functions.php');trigger_error('Problems selecting reader from query: "'.$query.'"');
         } else {
            $noticed = array();
            foreach ($result as $rs) {
               $temp = array();
               $temp[$rs['item_id']]['version_id'] = $rs['version_id'];
               $temp[$rs['item_id']]['read_date'] = $rs['read_date'];
               if (!in_array($temp,$this->_reader_id_array)){
                  $this->_reader_id_array[$rs['item_id']]['version_id'] = $rs['version_id'];
                  $this->_reader_id_array[$rs['item_id']]['read_date'] = $rs['read_date'];
               }
            }
         }
      }
   }


   function getLatestReaderForUserByID ( $item_id, $user_id ) {
      if (in_array($user_id,$this->_rubric_id_array)){
         if (array_key_exists($user_id,$this->_reader_id_array)){
            $reader = $this->_reader_id_array[$user_id];
         }else{
            $reader = array();
         }
      }else{
         $reader = array();
         $query  = 'SELECT version_id, read_date FROM '.$this->addDatabasePrefix('reader').
                ' WHERE item_id="'.encode(AS_DB,$item_id).'"'.
                ' AND   user_id="'.encode(AS_DB,$user_id).'"'.
                ' ORDER BY read_date DESC';
         $result = $this->_db_connector->performQuery($query);
         if ( !isset($result) ) {
            include_once('functions/error_functions.php');trigger_error('Problems selecting reader from query: "'.$query.'"');
         } else {
            if ( !empty($result[0]) ) {
               $reader['version_id'] = $result[0]['version_id'];
               $reader['read_date'] = $result[0]['read_date'];
            }
         }
      }
      return $reader;
   }

   /** mark an item/version as read by the current user
     *
     * @param integer item_id    id of the item
     * @param integer version_id id of the version
     */
   function markRead ( $item_id, $version_id ) {
      if ( !empty($this->_current_user_id) ) {
         $query = 'INSERT INTO '.$this->addDatabasePrefix('reader').' SET '.
                  ' item_id="'.encode(AS_DB,$item_id).'", '.
                  ' version_id="'.encode(AS_DB,$version_id).'", '.
                  ' user_id="'.encode(AS_DB,$this->_current_user_id).'", '.
                  ' read_date="'.getCurrentDateTimeInMySQL().'"';
         $result = $this->_db_connector->performQuery($query);
         if ( !isset($result) ) {
            include_once('functions/error_functions.php');
            trigger_error('Problems marking item as read from query: "'.$query.'"');
         }
      }
   }

   function mergeAccounts($new_id,$old_id) {
      $select = "SELECT * FROM ".$this->addDatabasePrefix("reader")." WHERE user_id = '".encode(AS_DB,$old_id)."'";

      $result = $this->_db_connector->performQuery($select);
      if ( !isset($result) ) {
         include_once('functions/error_functions.php');trigger_error('Problems creating reader from query: "'.$select.'"',E_USER_WARNING);
      }

      foreach ( $result as $row ) {
           $select2 = "SELECT * FROM ".$this->addDatabasePrefix("reader")." WHERE user_id = '".encode(AS_DB,$new_id)."' ";
           $select2.= " AND item_id = ".$row['item_id'];
           $select2.= " AND version_id = ".$row['version_id'];

         $result2 = $this->_db_connector->performQuery($select2);
         if ( !isset($result2) ) {
            include_once('functions/error_functions.php');trigger_error('Problems creating reader from query: "'.$select2.'"',E_USER_WARNING);
         } elseif ( !empty($result[0]) ) {
            $row2 = $result[0];
         } else {
            $row2 = '';
         }

         if ( empty($row2) ) {
            $update = "UPDATE ".$this->addDatabasePrefix("reader")." SET ";
            $update.= " user_id = ".encode(AS_DB,$new_id);
            $update.= " WHERE user_id = ".encode(AS_DB,$old_id);
            $update.= " AND item_id = ".$row['item_id'];
            $update.= " AND version_id = ".$row['version_id'];

            $result3 = $this->_db_connector->performQuery($update);
            if ( !isset($result3) or !$result3 ) {
               include_once('functions/error_functions.php');trigger_error('Problems creating reader from query: "'.$update.'"',E_USER_WARNING);
            }

         } else {
            $update = "DELETE FROM ".$this->addDatabasePrefix("reader")." ";
            $update.= " WHERE user_id = ".encode(AS_DB,$old_id);
            $update.= " AND item_id = ".$row['item_id'];
            $update.= " AND version_id = ".$row['version_id'];

            $result3 = $this->_db_connector->performQuery($update);
            if ( !isset($result3) or !$result3 ) {
               include_once('functions/error_functions.php');trigger_error('Problems creating reader from query: "'.$update.'"',E_USER_WARNING);
            }
         }
      }
   }
   
   function addDatabasePrefix($db_table){
      return $this->_db_prefix . $db_table;
   }
   
   function moveFromDbToBackup($context_id){
      $id_array_items = array();
      $item_manager = $this->_environment->getItemManager();
      $item_manager->setContextLimit($context_id);
      $item_manager->select();
      $item_list = $item_manager->get();
      $temp_item = $item_list->getFirst();
      while($temp_item){
         $id_array_items[] = $temp_item->getItemID();
         $temp_item = $item_list->getNext();
      }

      $id_array_users = array();
      $user_manager = $this->_environment->getUserManager();
      $user_manager->setContextLimit($context_id);
      $user_manager->select();
      $user_list = $user_manager->get();
      $temp_user = $user_list->getFirst();
      while($temp_user){
         $id_array_users[] = $temp_user->getItemID();
         $temp_user = $user_list->getNext();
      }
      
      global $c_db_backup_prefix;
      $retour = false;
      if(!empty($id_array_items) and !empty($id_array_users)){
         if ( !empty($context_id) ) {
            $query = 'INSERT INTO '.$this->addDatabasePrefix($c_db_backup_prefix.'_'.'reader').' SELECT * FROM '.$this->addDatabasePrefix('reader').' WHERE '.$this->addDatabasePrefix('reader').'.item_id IN ('.implode(",", $id_array_items).') OR '.$this->addDatabasePrefix('reader').'.user_id IN ('.implode(",", $id_array_users).')';
            $result = $this->_db_connector->performQuery($query);
            if ( !isset($result) ) {
               include_once('functions/error_functions.php');
               trigger_error('Problems while copying to backup-table.',E_USER_WARNING);
            } else {
               $retour = $this->deleteFromDb($context_id);
            }
         }
      }
      return $retour;
   }
   
   function moveFromBackupToDb($context_id){
      $id_array_items = array();
      $zzz_item_manager = $this->_environment->getZzzItemManager();
      $zzz_item_manager->setContextLimit($context_id);
      $zzz_item_manager->select();
      $item_list = $zzz_item_manager->get();
      $temp_item = $item_list->getFirst();
      while($temp_item){
         $id_array_items[] = $temp_item->getItemID();
         $temp_item = $item_list->getNext();
      }

      $id_array_users = array();
      $zzz_user_manager = $this->_environment->getZzzUserManager();
      $zzz_user_manager->setContextLimit($context_id);
      $zzz_user_manager->select();
      $user_list = $zzz_user_manager->get();
      $temp_user = $user_list->getFirst();
      while($temp_user){
         $id_array_users[] = $temp_user->getItemID();
         $temp_user = $user_list->getNext();
      }
      
      global $c_db_backup_prefix;
      $retour = false;
      if(!empty($id_array_items) and !empty($id_array_users)){
         if ( !empty($context_id) ) {
            $query = 'INSERT INTO '.$this->addDatabasePrefix('reader').' SELECT * FROM '.$this->addDatabasePrefix($c_db_backup_prefix.'_'.'reader').' WHERE '.$this->addDatabasePrefix($c_db_backup_prefix.'_'.'reader').'.item_id IN ('.implode(",", $id_array_items).') OR '.$this->addDatabasePrefix($c_db_backup_prefix.'_'.'reader').'.user_id IN ('.implode(",", $id_array_users).')';
            $result = $this->_db_connector->performQuery($query);
            if ( !isset($result) ) {
               include_once('functions/error_functions.php');
               trigger_error('Problems while copying to backup-table.',E_USER_WARNING);
            } else {
               $retour = $this->deleteFromDb($context_id, true);
            }
         }
      }
      return $retour;
   }
   
   function deleteFromDb($context_id, $from_backup = false){
   	global $c_db_backup_prefix;
      $retour = false;
      
      $db_prefix = '';
      $id_array_items = array();
      $id_array_users = array();
      if(!$from_backup){
         $item_manager = $this->_environment->getItemManager();
         $item_manager->setContextLimit($context_id);
         $item_manager->select();
         $item_list = $item_manager->get();
         $temp_item = $item_list->getFirst();
         while($temp_item){
            $id_array_items[] = $temp_item->getItemID();
            $temp_item = $item_list->getNext();
         }
         $user_manager = $this->_environment->getUserManager();
         $user_manager->setContextLimit($context_id);
         $user_manager->select();
         $user_list = $user_manager->get();
         $temp_user = $user_list->getFirst();
         while($temp_user){
            $id_array_users[] = $temp_user->getItemID();
            $temp_user = $user_list->getNext();
         }
      } else {
         $db_prefix .= $c_db_backup_prefix.'_';
         $zzz_item_manager = $this->_environment->getZzzItemManager();
         $zzz_item_manager->setContextLimit($context_id);
         $zzz_item_manager->select();
         $item_list = $zzz_item_manager->get();
         $temp_item = $item_list->getFirst();
         while($temp_item){
            $id_array_items[] = $temp_item->getItemID();
            $temp_item = $item_list->getNext();
         }
         $zzz_user_manager = $this->_environment->getZzzUserManager();
         $zzz_user_manager->setContextLimit($context_id);
         $zzz_user_manager->select();
         $user_list = $zzz_user_manager->get();
         $temp_user = $user_list->getFirst();
         while($temp_user){
            $id_array_users[] = $temp_user->getItemID();
            $temp_user = $user_list->getNext();
         }
      }
      
      $query = 'DELETE FROM '.$this->addDatabasePrefix($db_prefix.'reader').' WHERE '.$this->addDatabasePrefix($db_prefix.'reader').'.item_id IN ('.implode(",", $id_array_items).') OR '.$this->addDatabasePrefix($db_prefix.'reader').'.user_id IN ('.implode(",", $id_array_users).')';
      $result = $this->_db_connector->performQuery($query);
      if ( !isset($result) ) {
         include_once('functions/error_functions.php');
         trigger_error('Problems deleting after move to backup-table.',E_USER_WARNING);
      } elseif ( !empty($result[0]) ) {
         $retour = true;
      }
      return $retour;
   }
}
?>