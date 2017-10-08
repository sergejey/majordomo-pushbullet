<?php
/**
* Pushbullet service integraion
* @package project
* @author Wizard <sergejey@gmail.com>
* @copyright http://majordomo.smartliving.ru/ (c)
* @version 0.1 (wizard, 13:03:10 [Mar 13, 2016])
*/
//
//
class pushbullet extends module {
/**
* pushbullet
*
* Module class constructor
*
* @access private
*/
function pushbullet() {
  $this->name="pushbullet";
  $this->title="Pushbullet";
  $this->module_category="<#LANG_SECTION_APPLICATIONS#>";
  $this->checkInstalled();
}
/**
* saveParams
*
* Saving module parameters
*
* @access public
*/
function saveParams($data=0) {
 $p=array();
 if (IsSet($this->id)) {
  $p["id"]=$this->id;
 }
 if (IsSet($this->view_mode)) {
  $p["view_mode"]=$this->view_mode;
 }
 if (IsSet($this->edit_mode)) {
  $p["edit_mode"]=$this->edit_mode;
 }
 if (IsSet($this->tab)) {
  $p["tab"]=$this->tab;
 }
 return parent::saveParams($p);
}
/**
* getParams
*
* Getting module parameters from query string
*
* @access public
*/
function getParams() {
  global $id;
  global $mode;
  global $view_mode;
  global $edit_mode;
  global $tab;
  if (isset($id)) {
   $this->id=$id;
  }
  if (isset($mode)) {
   $this->mode=$mode;
  }
  if (isset($view_mode)) {
   $this->view_mode=$view_mode;
  }
  if (isset($edit_mode)) {
   $this->edit_mode=$edit_mode;
  }
  if (isset($tab)) {
   $this->tab=$tab;
  }
}
/**
* Run
*
* Description
*
* @access public
*/
function run() {
 global $session;
  $out=array();
  if ($this->action=='admin') {
   $this->admin($out);
  } else {
   $this->usual($out);
  }
  if (IsSet($this->owner->action)) {
   $out['PARENT_ACTION']=$this->owner->action;
  }
  if (IsSet($this->owner->name)) {
   $out['PARENT_NAME']=$this->owner->name;
  }
  $out['VIEW_MODE']=$this->view_mode;
  $out['EDIT_MODE']=$this->edit_mode;
  $out['MODE']=$this->mode;
  $out['ACTION']=$this->action;
  $this->data=$out;
  $p=new parser(DIR_TEMPLATES.$this->name."/".$this->name.".html", $this->data, $this);
  $this->result=$p->result;
}
/**
* BackEnd
*
* Module backend
*
* @access public
*/
function admin(&$out) {
 $this->getConfig();
 $out['CKEY']=$this->config['CKEY'];
 $out['LEVEL']=(int)$this->config['LEVEL'];
 $out['DEVICE_ID']=$this->config['DEVICE_ID'];
 $out['PREFIX']=$this->config['PREFIX'];

 $out['DISABLED']=$this->config['DISABLED'];


 if ($this->view_mode=='update_settings') {

   global $ckey;
   $this->config['CKEY']=$ckey;

   global $level;
   $this->config['LEVEL']=(int)$level;

   global $device_id;
   $this->config['DEVICE_ID']=$device_id;

   global $prefix;
   $this->config['PREFIX']=$prefix;


   global $disabled;
   $this->config['DISABLED']=$disabled;

   $this->saveConfig();
   $this->redirect("?ok=1");
 }

 if ($_GET['ok']) {
  $out['OK']=1;
 }
 
}

/**
* FrontEnd
*
* Module frontend
*
* @access public
*/
function usual(&$out) {
 $this->admin($out);
}

 function processSubscription($event, $details='') {
  $this->getConfig();
  if ($event=='SAY') {
    $level=$details['level'];
    $message=$details['message'];
    

   $consumerKey = $this->config['CKEY'];
   if (!$consumerKey && defined('SETTINGS_PUSHBULLET_KEY')) {
    $consumerKey    = SETTINGS_PUSHBULLET_KEY;
   }

   $consumerLevel = (int)$this->config['LEVEL'];
   $device_id = trim($this->config['DEVICE_ID']);
   $prefix = trim($this->config['PREFIX']);



   if ($consumerKey == '')
   return 0;

    if (!$this->config['DISABLED'] && $level>=$consumerLevel)
    {
          require_once(DIR_MODULES . 'pushbullet/pushbullet.inc.php');

          $push_bullet_apikey=trim($consumerKey);
          $p = new PushBulletAPI($push_bullet_apikey);

          if ($prefix) {
           $message=$prefix.' '.$message;
          }

          if (mb_strlen($message, 'UTF-8')>100) {
           $title=mb_substr($message, 0, 100, 'UTF-8').'...';
           $data=$message;
          } else {
           $title=$message;
           $data='';
          }


          if ($device_id) {
           $devices=explode(',', $device_id);
           $total=count($devices);
           for($i=0;$i<$total;$i++) {
            $push_bullet_device_id=trim($devices[$i]);
            if ($push_bullet_device_id) {
                          try {
                           DebMes("Sending to $push_bullet_device_id title: $title ,  data: $data");
                           $res=$p->pushNote($push_bullet_device_id, $title, $data);
                          } catch(Exception $e){
                              DebMes("Pushbullet error: ".get_class($e).', '.$e->getMessage());
                          }
            }
           }
          } else {
           $res=$p->getDevices();
           $devices=$res->devices;
           $total=count($devices);
           for($i=0;$i<$total;$i++) {
            if ($devices[$i]->iden) {

                          try {
                           DebMes("Sending to ".$devices[$i]->iden." title: $title ,  data: $data");
                           $res=$p->pushNote($devices[$i]->iden, $title, $data);
                          } catch(Exception $e){
                              DebMes("Pushbullet error: ".get_class($e).', '.$e->getMessage());
                          }

            }
           }
          }
    }
  }
 }
/**
* Install
*
* Module installation routine
*
* @access private
*/
 function install($data='') {
  subscribeToEvent($this->name, 'SAY');
  parent::install();
 }
// --------------------------------------------------------------------
}
/*
*
* TW9kdWxlIGNyZWF0ZWQgTWFyIDEzLCAyMDE2IHVzaW5nIFNlcmdlIEouIHdpemFyZCAoQWN0aXZlVW5pdCBJbmMgd3d3LmFjdGl2ZXVuaXQuY29tKQ==
*
*/
