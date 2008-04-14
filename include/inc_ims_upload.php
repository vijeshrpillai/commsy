<?php
//
// Copyright (c)2002-2007 Dirk Bloessl, Matthias Finck, Dirk Fust, Oliver Hankel, Iver Jackewitz, Michael Janneck,
// Martti Jeenicke, Detlev Krause, Irina L. Marinescu, Timo Nolte, Bernd Pape,
// Edouard Simon, Monique Strauss, Joseacute; Manuel Gonzaacute;lez Vaacute;zquez
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



function _getMaterialByXMLArray($material_item, $values_array,$directory){
   global $environment;
   $material_item->setVersionID(0);
   $material_item->setContextID($environment->getCurrentContextID());
   $user = $environment->getCurrentUserItem();
   $material_item->setCreatorItem($user);
   $material_item->setCreationDate(getCurrentDateTimeInMySQL());


   $title = '';
   $author = '';
   $pub_date = '';
   $bib_val = '';
   $abstract = '';
   $table_of_content = '';
   $full_text = '';
   $files = array();
   foreach($values_array as $key => $value){
     switch ($value['tag']){
       case 'dc:title':
            if ($value['type'] == 'open'){
                $title = $values_array[$key+1]['value'];
            }
            break;
       case 'dc:creator':
            if (!empty ($author)){
                $author .= ', ';
            }
            if ($value['type'] == 'open'){
                $author = $values_array[$key+1]['value'];
            }
             break;
       case 'dcterms:issued':
            if ($value['type'] == 'open'){
                $pub_date = $values_array[$key+1]['value'];
            }
            break;
       case 'dcterms:abstract':
            if ($value['type'] == 'open'){
                $abstract = $values_array[$key+1]['value'];
            }
            break;
       case 'dcterms:tableOfContents':
            $table_of_content = $values_array[$key]['attributes']['dcx:valueURI'];
            break;
       case 'dc:identifier':
            if ($value['type'] == 'complete'){
                if (isset($values_array[$key]['attributes']['dcx:valueURI'])){
                   if (!empty($full_text)){
                      $full_text .= ', ';
                   }
                   $full_text .= $values_array[$key]['attributes']['dcx:valueURI'];
                   if (strstr($values_array[$key]['attributes']['dcx:valueURI'],'files/')
                       and !strstr($values_array[$key]['attributes']['dcx:valueURI'],'http:')
                        and !strstr($values_array[$key]['attributes']['dcx:valueURI'],'www.')
                        and !strstr($values_array[$key]['attributes']['dcx:valueURI'],'ftp:')
                      ){
                         $files[]=$values_array[$key]['attributes']['dcx:valueURI'];
                      }
                }
            }
            break;
      }

   }
   $file_man = $environment->getFileManager();
   $file_id_array = array();
   foreach ( $files as $file ) {
      $file_data = array();
      $file_name = basename($directory.$file);
      $file_data['tmp_name'] = $directory.$file;
      $file_data['name'] = $file_name;
      $file_data['file_id'] = $file_data['name'].'_'.getCurrentDateTimeInMySQL();
      $file_item = $file_man->getNewItem();
      $file_item->setPostFile($file_data);
      $file_item->save();
      $file_id_array[] = $file_item->getFileID();
   }
   if (empty($author)){
      foreach($values_array as $key => $value){
         switch ($value['tag']){
              case 'dc:contributor':
               if ($value['type'] == 'open'){
                   if (!empty ($author)){
                      $author .= ', ';
                   }
                   $author = $values_array[$key+1]['value'];
                }
                break;
         }
      }
   }
   if (empty($title)){
      $title = getMessage('COMMON_NO_TITLE');
   }
   if (empty($author)){
      $author = getMessage('COMMON_NO_AUTHOR');
   }
   if ( !empty($table_of_content) ){
      $abstract = '<span class="ims_key">'.getMessage('COMMON_TABLE_OF_CONTENT').':</span> '.$table_of_content.BR.$abstract;
   }
   if ( !empty($full_text) ){
      $abstract = '<span class="ims_key">'.getMessage('COMMON_FULL_TEXT').':</span> '.$full_text.BR.$abstract;
   }
   if (!empty($file_id_array)){
      $material_item->setFileIDArray($file_id_array);
   }
   $material_item->setTitle($title);
   $material_item->setAuthor($author);
   $material_item->setPublishingDate($pub_date);
   $material_item->setModificatorItem($user);
   $material_item->setDescription($abstract);
   return $material_item;
}


function _getMaterialListByXML($directory){
   global $environment;
   $xml_file_array = array();
   $xsl_file_array = array();
   if (is_dir($directory)) {
      if ($dh = opendir($directory)) {
         while (($file = readdir($dh)) !== false) {
            if ( strstr($file,'.xml') and $file != 'imsmanifest.xml' and $file != 'meta.xml'){
               $xml_file_array[] = $file;
            }
            if ( strstr($file,'.xsl') ){
               $xsl_file_array[] = $file;
            }
         }
         closedir($dh);
      }
   }
   foreach($xml_file_array as $file){
      $data = implode("", file($directory.$file));
      $parser = xml_parser_create();
      xml_parser_set_option($parser, XML_OPTION_CASE_FOLDING, 0);
      xml_parser_set_option($parser, XML_OPTION_SKIP_WHITE, 1);
      xml_parse_into_struct($parser, $data, $values, $tags);
      $material_manager = $environment->getMaterialManager();
      $material_item = $material_manager->getNewItem();
      $material_item = _getMaterialByXMLArray($material_item,$values,$directory);
      xml_parser_free($parser);
      $proc = new XSLTProcessor;
      $xml = new DOMDocument;
      $xml->loadXML(utf8_encode($data));
      $xsl_filename = '';
      foreach($xsl_file_array as $xsl_file){
         if(strstr($data,$xsl_file)){
           $xsl_filename = $xsl_file;
        }
      }
      if (!empty($xsl_filename)){
         $xsl = new DOMDocument;
         $xsl->load(utf8_encode($directory.$xsl_filename));
         $proc->importStyleSheet($xsl);
         $xml_doc = $proc->transformToXML($xml);
         $material_item->setBibliographicValues($xml_doc);
         $material_item->save();
         unset($material_item);
      }
   }
}


function getMaterialListByIMSZip($filename,$file_tmp_name, $target_directory,$environment){
   $has_manifest = false;
   $zip = new ZipArchive;
  $res = $zip->open($file_tmp_name);
   if ( $res === TRUE ) {
      if( $zip->extractTo($target_directory,'imsmanifest.xml') ) {
        $has_manifest = true;
         $indexfile = "imsmanifest.xml";
         unlink($target_directory.'/imsmanifest.xml');
      }
      if($has_manifest) {
         $filename = str_replace('.zip','',strtolower($filename));
         $zip->extractTo($target_directory.$filename);
         _getMaterialListByXML($target_directory.$filename.'/data/');
      }
      _full_rmdir($target_directory);
      $zip->close();
   }
   unset($zip);
}

function _full_rmdir($dirname) {
   if ( $dirHandle = opendir($dirname) ) {
      $old_cwd = getcwd();
      chdir($dirname);
      while ($file = readdir($dirHandle)){
         if ($file == '.' || $file == '..') continue;
         if ( is_dir($file) ) {
            if ( !_full_rmdir($file) ) {
               chdir($old_cwd);
               return false;
            }
         } else {
            if ( !@unlink($file) ) {
               chdir($old_cwd);
               return false;
            }
         }
      }

      closedir($dirHandle);
      chdir($old_cwd);
      if (!rmdir($dirname)) return false;
      return true;
   } else {
      return false;
   }
}



?>