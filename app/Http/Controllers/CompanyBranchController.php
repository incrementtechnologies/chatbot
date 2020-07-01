<?php

namespace App\Http\Controllers;

use App\CompanyBranch;
use Illuminate\Http\Request;

class CompanyBranchController extends APIController
{
    function __construct(){
        $this->model = new CompanyBranch();

        $this->notRequired = array(
          "name",
          "code",
          "email",
          "address",
          "contact_number",
          "fax_number"
        );

        $this->useUserCompanyID = false;
    }
    public function create(Request $request){
      $request = $request->all();
      $this->createEntry($request);
      return $this->output();
    }
}
