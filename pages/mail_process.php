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

$mail_obj = new cs_mail_obj();
$mail_obj = $mail_obj->fromSession();

// If the mail should be send automatic without showing the form
if ( $mail_obj->isSendMailAuto() ) {
   include_once('classes/cs_mail.php');
   $mail = new cs_mail();
   $sender = $mail_obj->getSender();
   $senderName = "";
   $senderAddress = "";
   foreach ( $sender as $name => $address ) {
      $senderName = $name;
      $senderAddress = $address;
   }
   $mail->set_from_email($senderAddress);
   $mail->set_from_name($senderName);
   $mail->set_reply_to_email($senderAddress);
   $mail->set_reply_to_name($senderName);
   $mail->set_subject($mail_obj->getSubject());
   $mail->set_message($mail_obj->getContent());
   $receiversA = $mail_obj->getReceivers();
   $receivers = implode(",",$receiversA);

   $mail->set_to($receivers);
   $mail->send();

   $mail_obj->goBackLink();

}

// Get the translator object
$translator = $environment->getTranslationObject();

// option contains the name of the submit button, if this
// script is called as result of a form post
if (!empty($_POST['option'])) {
   $command = $_POST['option'];
} else {
   $command = '';
}


if ( $command != 'error' ) {
   $class_params= array();
   $class_params['environment'] = $environment;
   $form = $class_factory->getClass(MAIL_PROCESS_FORM,$class_params);
   unset($class_params);

   if ( isOption($command,$translator->getMessage('MAIL_NOT_SEND_BUTTON')) ) {
      $mail_obj->goBackLink();
   } else {

          // init display data
      if ( !empty($_POST) ) {     // second call of form: set post data
         $form->setFormPost($_POST);
      }

      $form->setMailObject($mail_obj);
      $form->prepareForm();
      $form->loadValues();

      if ( !empty($command) AND isOption($command,$translator->getMessage('MAIL_SEND_BUTTON')) ) {
         $correct = $form->check();
         if ( $correct ) {
            include_once('classes/cs_mail.php');
            $mail = new cs_mail();
            $mail->set_from_email($_POST['senderAddress']);
            $mail->set_from_name($_POST['senderName']);
            $mail->set_reply_to_email($_POST['senderAddress']);
            $mail->set_reply_to_name($_POST['senderName']);
            $mail->set_subject($_POST['subject']);
            $mail->set_message($_POST['content']);
            if ( is_array($_POST['receivers']) ) {
               $receivers = implode(",",$_POST['receivers']);
            } else {
               $receivers = $_POST['receivers'];
            }
            $mail->set_to($receivers);
            $mail->send();

            $mail_obj->goBackLink();
         }
      }

      // display form
      $params = array();
      $params['environment'] = $environment;
      $params['with_modifying_actions'] = true;
      $form_view = $class_factory->getClass(CONFIGURATION_FORM_VIEW,$params);
      unset($params);
      $form_view->setAction(curl($environment->getCurrentContextID(),'mail','process',''));
      $form_view->setForm($form);
      if ( $environment->inServer() or $environment->inPortal() ) {
         $page->addForm($form_view);
      } else {
         $page->add($form_view);
      }
   }
}
?>