<?php

namespace App\Ilinya\Message\Facebook;


class Codes{

  /**
    PRIMARY CODES
  */

  public $read          = 0;
  public $delivery      = 100;
  public $postback      = 200;
  public $message       = 300;
  public $error         = 400;

  /**
    POSTBACK CODES
  */
  public $pStart            = 201;
  public $pUserGuide        = 202;
  public $pMyQueueCards     = 203;
  public $pCategories       = 204;
  public $pCategorySelected = 205;
  public $pGetQueueCard     = 206;
  public $pLocate      = 207;
  public $pNext        = 208;
  public $pSend        = 209;
  public $pEdit        = 210;
  public $pDisregard   = 211;
  public $pSearch      = 212;
  public $pCancelQC    = 213;
  public $pPostponeQC  = 214;
  public $pQCViewDetails = 215;
  public $pEditDetails = 216;



  /**
    MESSAGE CODES
  */
  public $mQuickReply  = 301;
  public $mAttachments = 302;
  public $mText        = 303;
  
  /**
    ATTACHMENTS CODES
  */
  public $aLocation    = 310;

  /**
    QUICK REPLY CODES
  */
  public $qrSearch       = 320;
  public $qrPriorityYes  = 321;
  public $qrPriorityNo   = 322;
  public $qrFormCancel   = 323;
  public $qrFormContinue = 324;
  public $qrDisregard    = 325;
  public $qrStageError   = 326;
  public $qrSurvey       = 327;
  public $qrQueueCardCancel    = 328;
  public $qrQueueCardPostpone  = 329;

  /**
    TEXT CODES
  */

  public $replyStageSearch     = 1;
  public $replyStageForm       = 2;
  public $replyStageEdit       = 3;
  public $replyEditDetails     = 4;
  public $replyStageShortCodes = 10;
  
  public $stageStart = 1000;
  public $stageForm  = 2000;
  public $stageReview = 3000;

  protected $postbackPayloads = array();
  protected $quickReplyPayloads = array();



  function __construct(){
    $this->postbackPayloads = array(
      "@pStart"          => $this->pStart,
      "@pUserGuide"      => $this->pUserGuide,
      "@pMyQueueCards"   => $this->pMyQueueCards,
      "@pCategories"     => $this->pCategories,
      "@pCategorySelected"  => $this->pCategorySelected,
      "@pGetQueueCard"      => $this->pGetQueueCard,
      "@pLocate"    => $this->pLocate,
      "@pNext"      => $this->pNext,
      "@pSend"      => $this->pSend,
      "@pEdit"      => $this->pEdit,
      "@pDisregard" => $this->pDisregard,
      "@pSearch"    => $this->pSearch,
      "@pCancelQC"  => $this->pCancelQC,
      "@pPostponeQC"=> $this->pPostponeQC,
      "@pQCViewDetails" => $this->pQCViewDetails,
      "@pEditDetails" => $this->pEditDetails
    );

    $this->quickReplyPayloads = array(
      "@qrSearch"       => $this->qrSearch,
      "@qrPriorityYes"  => $this->qrPriorityYes,
      "@qrPriorityNo"   => $this->qrPriorityNo,
      "@qrFormCancel"   => $this->qrFormCancel,
      "@qrFormContinue" => $this->qrFormContinue,
      "@qrDisregard"    => $this->qrDisregard,
      "@qrStageError"   => $this->qrStageError,
      "@qrSurvey"       => $this->qrSurvey,
      "@qrQueueCardCancel" => $this->qrQueueCardCancel,
      "@qrQueueCardPostpone" => $this->qrQueueCardPostpone
    );
  }

  public function getCode($custom){
    $code = null;
    switch ($custom['type']) {
      case 'read':
        $code = $this->read;
        break;
      case 'delivery':
        $code = $this->delivery;
        break;
      case 'postback':
        $code = $this->getCodeInPostback($custom['payload']);
        break;
      case 'message':
        $code = $this->getCodeInMessage($custom);
    }
    return $code;
  }

  public function getCodeInPostback($payload){
    return $this->postbackPayloads[$payload];
  }

  public function getCodeInMessage($messageType){
    if($messageType['attachments'])return $this->mAttachments;
    else if($messageType['quick_reply'])return $this->getCodeInQuickReply($messageType['quick_reply']['payload']);
    else if($messageType['text'])return $this->mText;
    else return $this->message;
  }

  public function getCodeInQuickReply($payload){
    return $this->quickReplyPayloads[$payload];
  }


}