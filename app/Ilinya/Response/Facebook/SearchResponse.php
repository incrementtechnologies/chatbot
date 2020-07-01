<?php
namespace App\Ilinya\Response\Facebook;

/*
    @Providers
*/
use App\Ilinya\Webhook\Facebook\Messaging;
use App\Ilinya\User;
use App\Ilinya\Tracker;
use Illuminate\Http\Request;
use App\Ilinya\Response\Facebook\CategoryResponse;
/*
    @Template
*/
use App\Ilinya\Templates\Facebook\QuickReplyTemplate;
use App\Ilinya\Templates\Facebook\ButtonTemplate;
use App\Ilinya\Templates\Facebook\GenericTemplate;
use App\Ilinya\Templates\Facebook\LocationTemplate;
use App\Ilinya\Templates\Facebook\ListTemplate;

/*
    @Elements
*/

use App\Ilinya\Templates\Facebook\ButtonElement;
use App\Ilinya\Templates\Facebook\GenericElement;
use App\Ilinya\Templates\Facebook\QuickReplyElement;


/*
    @API
*/
use App\Ilinya\API\Controller;
use App\Ilinya\Message\Facebook\Form;
use App\Ilinya\Helper\Validation;
use App\Ilinya\API\QueueForm;


class SearchResponse{

  protected $messaging;
  protected $tracker;
  protected $category;
  protected $form;
  protected $validation;

  public function __construct(Messaging $messaging){
      $this->messaging = $messaging;
      $this->tracker   = new Tracker($messaging);
      $this->category  = new CategoryResponse($messaging);
      $this->form      = new Form($messaging);
      $this->validation = new Validation($messaging);
  }

  public function manage($value){
      return $this->category->search($value, null);
  }

  public function manageSearchByCode($value){
      //Retrieve from forms

    if(strrpos($value, '-') && strlen($value) == 8){
      list($companyCode, $formCode) = explode('-', $value);

        if(strlen($companyCode) != 3 || strlen($formCode) != 4){
          return $this->error();
        }
        else if($this->validation->validateNumber(["number" => $companyCode]) == false || $this->validation->validateNumber(["number" => $formCode]) == false){
          return $this->error();
        }
        else{
          $companyId = intval(strrev($companyCode));
          $formId    = intval($formCode);

          $data = [
            "id"    => $formId,
            "company_id"  => $companyId
          ];

          $formResult = QueueForm::retrieve($data);
          if(sizeof($formResult) > 0){
            $trackerData = [
              "company_id"    => $companyId,
              "search_option" => null,
              "reply"         => null,
              "form_sequence" => null
            ];  
            $this->tracker->update($trackerData);
            $this->form->manageForms($formResult);
          }
          else{
            return $this->error();
          }
        }
    }
    else{
      return $this->error();
    }
  }


  public function error(){
    return ["text" => "Invalid Code :'( Please Enter the Code again(e.q. 111-1111):"];
  }

  public function searchOption(){
      $quickReplies[] = QuickReplyElement::title('By Code')->contentType('text')->payload('1@qrSearch');
      $quickReplies[] = QuickReplyElement::title('By Company Name')->contentType('text')->payload('2@qrSearch');
      $quickReplies[] = QuickReplyElement::title('By Company Location')->contentType('text')->payload('3@qrSearch');

      return QuickReplyTemplate::toArray('Select options for search:', $quickReplies);
  }

  public function question($option){
    $option = intval($option);
    switch ($option) {
       case 1:
         return "Enter Code:";
         break;
       case 2:
         return "Enter Company Name:";
         break;
       case 3:
         return "Enter Company Location:";
         break; 
     }
  }

}