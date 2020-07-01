<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use App\Company;

class CompanyController extends APIController
{


  private $account_id;
  private $company_id;
  private $company_branch_id;

  function __construct(){
    $this->model = new Company();

    $this->notRequired = array(
      "lat",
      "lng"
    );

  }


    /*
      1. account
      2. account_information
      3. companies
      4. company_branches
      5. company_branch_employees
    */

  public function create(Request $request){
     
     $flag = array(
      "account" => true,
      "account_message" => array(),
      "company" => true,
      "company_message" => array(),
      "branch"  => true,
      "branch_message" => array()
     );

     $request = $request->all();
     if(isset($request['account'])){
       $request['account']['account_information']['account_type_id'] = 1; // 1 => ADMIN
       $accountRequest = new Request($request['account']);
       $accountResult = $this->sendCustomRequestByCreate('App\Http\Controllers\AccountController',$accountRequest);

       if($accountResult['data'] == NULL){
        $flag["account"] = false;
        $flag["account_message"] = $accountResult;
       }
       else{
        $flag["account"] = true;
        $this->account_id = $accountResult['data']['id'];
        $companyRequest['business_type_id'] = $request['business_type_id'];
        $companyRequest['name'] = $request['name'];
        $companyRequest['address'] = $request['address'];
        $this->createEntry($companyRequest);
        $companyResult = json_decode($this->output()->getContent(), true);
        if($companyResult["data"] != NULL){
          $this->company_id = $companyResult["data"];
          $flag["company"] = true;
          $branchRequestArray["branch"]["company_id"] = $this->company_id;
          $branchRequest = new Request($branchRequestArray["branch"]);
          $branchResult = $this->sendCustomRequestByCreate('App\Http\Controllers\CompanyBranchController', $branchRequest);
          if($branchResult['data'] != NULL){
            $this->company_branch_id = $branchResult['data'];
            $flag["branch"] = true;
          }else{
            $flag["branch"] = false;
            $flag["branch_message"] = $branchResult;
          }
        }else{
          $flag["company"] = false;
          $flag["company_message"] = $companyResult;
        }
       }
       $this->checkFlag($flag);
     }
  }

  public function checkFlag($flag){
      if($flag["account"] == true && $flag["branch"] == true && $flag["company"] == true){
        
        $request["company_branch_id"] = $this->company_branch_id;
        $request["account_id"] = $this->account_id;
        $branchEmployeeRequest = new Request($request);
        $controller = "App\Http\Controllers\CompanyBranchEmployeeController";
        $branchEmployeesResult = $this->sendCustomRequestByCreate($controller,$branchEmployeeRequest);
        if($branchEmployeesResult["data"] != NULL){
          $thisOutput =  array(
            "data"  => array(
                "flag"  => true,
                "account_id"  => $this->account_id,
                "company_id"  => $this->company_id,
                "company_branch_id" => $this->company_branch_id
            ),
            "error" => array(
              "status"  => null,
              "message" => null
            ),
            "debug" => null
          );   
          echo json_encode($thisOutput);
          return $thisOutput;
        }else{
          return $branchEmployeesResult;
        }
      }else if($flag["account"] == false){
        return $flag["account_message"];
      }else if($flag["company"] ==  false){
        return $flag["company_message"];
      }else if($flag["branch"] == false){
        return $flag["branch_message"];
      }
  }

  public function sendCustomRequestByCreate($controller, $request){
    $response = App($controller)->create($request);
    $result = json_decode($response->getContent(), true);
    return $result;
  }
}
