<?php

namespace App\Http\Controllers;

use App\Account;
use App\CompanyBranchEmployee;
use Illuminate\Support\Facades\Hash;
use Illuminate\Http\Request;

class AccountController extends APIController
{
     function __construct(){
        $this->model = new Account();
        $this->validation = array(
          "email" => "unique:accounts",
          "username"  => "unique:accounts",
          "account_information.account_type_id" => "required"
        );
        $this->editableForeignTable = array(
          'account_information'
        );
        $this->foreignTable = array(
          'account_information',
          'account_profile_picture',
          'company_branch_employee',
          'company_branch'
        );
    }

    /*
      1. account
      2. account_information
    */

    public function create(Request $request){
     $request = $request->all();
     $request['password'] = Hash::make($request['password']);
     $result = $this->createEntry($request);
     if($this->response['data']){
        $accountTypeID = isset($request['account_information']['account_type_id']) ? $request['account_information']['account_type_id'] : false;
        if($accountTypeID){
          $accountResponseData = $this->response['data'];
          $this->model = new CompanyBranchEmployee();
          $companyBranchRequest = array(
            'company_branch_id' => $this->getUserCompanyBranchID(),
            'identification_number' => $accountResponseData['id'], // dummy id
            'account_id' => $accountResponseData['id']
          );
          $this->validation = array();
          $this->createEntry($companyBranchRequest);
        }
     }
     return $this->output();
    }

    public function update(Request $request){
      $this->updateEntry($this->hashPassword($request));
      return $this->output();
    }

    public function hashPassword(Request $request){
      $data = $request->all();
      $data['password'] = Hash::make($data['password']);
      return $data;
    }
}
