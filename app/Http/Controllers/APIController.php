<?php

namespace App\Http\Controllers;

use Storage;
use Illuminate\Http\Request;
use Illuminate\Http\Input;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Support\Pluralizer;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Tymon\JWTAuth\Facades\JWTAuth;
use Tymon\JWTAuthExceptions\JWTException;
use App\CompanyBranchEmployee;

class APIController extends Controller
{
    protected $model = null;
    protected $validation = array();
    protected $test = null;
    protected $userSession = null;
    protected $response = array(
      "data" => null,
      "error" => array(),// {status, message}
      "debug" => null,
      "request_timestamp" => 0
    );
    protected $responseType = 'json';
    protected $rawRequest = null;
    protected $tableColumns = null;
    protected $notRequired = array();
    protected $defaultValue = array();
    protected $requiredForeignTable = array();//children that are always retrieve, singular
    /**
      Array for single fileupload input.
      [{
        "name" => '', Name of the input
        "path" => '', Path to be saved
        "column" => '', column name in the table
        "replace" => Boolean Delete previous file
      }]
    **/
    protected $singleImageFileUpload = array();
    /***
      Array of editable table. The value can be list of table names or associative array of table with its rules
      List:
        [table1, table2, table3]
      With rules
        [
          table1 => array(
            no_create_on_update: true
          )
          table2 => array()
        ]
      Rules:
        no_create_on_update boolean prevent creation of foreign table during update operation
    **/
    protected $editableForeignTable = array();
    protected $foreignTable = array();
    protected $useUserCompanyID = true;

    public function test(){
      $user = $this->getUserCompanyID();
      // $this->printR($this->userSession);
      // echo response()->json($user);
    }
    public function output(){
      // sleep(2);
      $this->response["request_timestamp"] = date("Y-m-d h:i:s");
      if($this->responseType == 'array'){
        return $this->response;
      }else{
        return response()->json($this->response);
      }
      // echo json_encode($this->response);
    }
    public function create(Request $request){
      $this->rawRequest = $request;
      $this->createEntry($request->all());
      return $this->output();
    }
    public function retrieve(Request $request){
      $this->rawRequest = $request;
      $this->retrieveEntry($request->all());
      return $this->output();
    }
    public function update(Request $request){
      $this->rawRequest = $request;
      if ($request->hasFile('image_file')){
      }
      else{
      }

      $this->updateEntry($request->all());

      return $this->output();
    }
    public function delete(Request $request){
      $this->deleteEntry($request->all());
      return $this->output();
    }
    public function printR($object){
      echo "<pre>";
      print_r($object);
      echo "</pre>";
    }
    /**
      Check if form fields are valid
      ouput : true - if no errors
              output() - if errors found.
    */
    public function filterRequest($request){
      foreach($this->tableColumns as $column){
        switch($column){
          case "company_id":
            if($this->useUserCompanyID){
              $request[$column] = $this->getUserCompanyID();
            }
            break;
        }
      }
      return $request;
    }
    public function isValid($request, $action = "create", $subTableName = false){
      /*Require all fields*/
      unset($this->tableColumns[0]);
      array_pop($this->tableColumns);//deleted at
      array_pop($this->tableColumns);//updated at
      array_pop($this->tableColumns);//created at
      foreach($this->tableColumns as $column){
        $this->validation[$column] = (isset($this->validation[$column])) ? $this->validation[$column] : '';
        if(!in_array($column, $this->notRequired) && !isset($this->defaultValue[$column])){//requiring all field by default
          if($action !== "update"){
            $this->validation[$column] = $this->validation[$column].($this->validation[$column] ? "| ":"")."required";
          }else if($action === "update"){
            if(in_array($column, $request)){
              $this->validation[$column] = $this->validation[$column].($this->validation[$column] ? "| ":"")."required";
            }else{

              unset($this->validation[$column]);
            }
          }
        }
      }
      if($action == "update"){
        $this->validation["id"] = "required";
        if(!isset($request["id"])){
          $this->response["error"]["status"] = 102;
          $this->response["error"]["message"] = "ID required";
          return false;
        }
      }
      if(count($this->validation)){
        foreach($this->validation as $validationKey => $validationValue){
          if($action == "update"){
            if(strpos( $validationValue, "unique" ) !== false ) { //check if rule has unique
              $rules = explode("|", $this->validation[$validationKey]);
              foreach($rules as $ruleKey => $ruleValue){ //find the unique rule
                if(strpos( $ruleValue, "unique" ) !== false){
                  $rules[$ruleKey] = Rule::unique(str_replace("unique:", "", $ruleValue), $validationKey)->ignore($request["id"], "id");
                }
              }
              $this->validation[$validationKey] = $rules;
            }
          }
          if(strpos( $validationKey, "_id" ) !== false){
            $table = explode(".", str_plural(str_replace("_id", "", $validationKey)));
            $table = (count($table) > 1) ? $table[1] : $table[0];
            if(strpos( $validationKey, "parent" ) !== false){
              $table = $this->model->getTable();
            }
            $this->validation[$validationKey] = $this->validation[$validationKey]."|exists:".$table.",id";
          }
        }
        $validator = Validator::make($request, $this->validation);
        if ($validator->fails()) {
          if(!$subTableName){
            $this->response["error"]["status"] = 100;
            $this->response["error"]["message"] = $validator->errors()->toArray();
          }
          return false;
        }else{
          return true;
        }
      }
    }
    public function createEntry($request){
      $responseType = isset($request['response_type']) ? $request['response_type'] : 'json';
      $tableColumns = $this->model->getTableColumns();
      $this->tableColumns = $tableColumns;
      $request = $this->filterRequest($request);

      if(!$this->isValid($request, "create")){
        return $this->output();
      }
      unset($tableColumns[0]);
      foreach($tableColumns as $columnName){
        if(isset($request[$columnName])){
          $this->model->$columnName = $request[$columnName];
        }else if(isset($this->defaultValue[$columnName])){
          $this->model->$columnName = $this->defaultValue[$columnName];
        }
      }
      $this->model->save();
      $childID = array();
      if($this->model->id && count($this->singleImageFileUpload)){
        for($x = 0; $x < count($this->singleImageFileUpload); $x++){
          $this->uploadSingleImageFile(
            $this->model->id,
            $this->singleImageFileUpload[$x]['name'],
            $this->singleImageFileUpload[$x]['path'],
            $this->singleImageFileUpload[$x]['column']
          );
        }
      }
      if($this->model->id && $this->editableForeignTable){
        foreach($this->editableForeignTable as $childTable){
          if(isset($request[$childTable]) && $request[$childTable]){
            $child = $request[$childTable];
            if(count(array_filter(array_keys($child), 'is_string')) > 0){//associative
              if(!isset($childID[$childTable])){
                $childID[$childTable] = array();
              }
              $child[str_singular($this->model->getTable()).'_id'] = $this->model->id;
              $foreignTable = $this->model->newModel($childTable, $child);
              foreach($child as $childKey => $childValue){
                $foreignTable->$childKey = $childValue;
              }
              $result = $this->model->find($this->model->id)->$childTable()->save($foreignTable);
              $childID[$childTable][] = $result["id"];
            }else{ // list
              foreach($child as $childValue){
                if(!isset($childID[$childTable])){
                  $childID[$childTable] = array();
                }
                $childValue[str_singular($this->model->getTable()).'_id'] = $this->model->id;
                $foreignTable = $this->model->newModel($childTable, $childValue);
                foreach($childValue as $childValueKey => $childValueValue){
                  if($childValueValue == null || $childValueValue == "" || empty($childValueValue)){
                    $foreignTable->$childValueKey = $childValueValue;
                  }
                }
                $result = $this->model->find($this->model->id)->$childTable()->save($foreignTable);
                $childID[$childTable][] = $result["id"];
              }
            }
          }
        }
        $response = $this->model->id;
        if(count($childID)){
          $childID["id"] = $this->model->id;;
          $response = $childID;
        }
        $this->response["data"] = $response;
        return $response;
      }else{
        if($this->model->id){
          $this->response["data"] = $this->model->id;
          return true;
        }else{
          $this->response["error"]["status"] = 1;
          $this->response["error"]["message"] = "Failed to create entry in database";
          return false;
        }
      }

    }
    public function uploadSingleImageFile($id, $inputName, $path, $dbColumn, $replace = false){
      if($id){
        if ($this->rawRequest->hasFile($inputName) && $this->rawRequest->file($inputName)->isValid()){
          $imagePath = $this->rawRequest[$inputName]->store($path);


          if($replace){
            $modelTemp = clone $this->model;
            $this->model->where('id', '=',$id);
            $entry = $this->retrieveEntry(array(
              "id" => $id
            ));
            if(count($entry) && $entry[0][$dbColumn] != '' && $entry[0][$dbColumn] != null){
              Storage::delete($path.'/'.$entry[0][$dbColumn]);
            }
            $this->model = $modelTemp;
          }
          $responseTemp = $this->response;
          $this->updateEntry(array(
            'id' => $id,
            $dbColumn => str_replace($path.'/', '', $imagePath)
          ), true);
          $this->response = $responseTemp;
          return true;
        }
      }
      return false;
    }
    function initCondition($condition){
      $initializedCondition = array(
        "main_table" => array(),
        "foreign_table" => array()
      );
      if(isset($condition)){
        for($x = 0; $x < count($condition); $x++){
          $columnExploded = explode('.', $condition[$x]['column']);
          if(count($columnExploded) > 1){ // foreign table
            if(!isset($initializedCondition['foreign_table'][$columnExploded[0]])){
              $initializedCondition['foreign_table'][$columnExploded[0]] = array();
            }
            $initializedCondition['foreign_table'][$columnExploded[0]][] = $condition[$x];
          }else{
            $initializedCondition['main_table'][] = $condition[$x];
          }

        }
      }
      return $initializedCondition;
    }
    public function retrieveEntry($request){
      $responseType = isset($request['response_type']) ? $request['response_type'] : 'json';
      $allowedForeignTable = array_merge($this->foreignTable, $this->editableForeignTable, $this->requiredForeignTable);
      $tableName = $this->model->getTable();
      $singularTableName = str_singular($tableName);
      $tableColumns = $this->model->getTableColumns();
      // if(count($this->requiredForeignTable)){
      //   $this->model = $this->model->with($this->requiredForeignTable);
      //   for($x = 0; $x < count($this->requiredForeignTable); $x++){
      //     $singularForeignTable = str_singular($this->requiredForeignTable[$x]);
      //     $pluralForeignTable = str_plural($this->requiredForeignTable[$x]);
      //     $this->model = $this->model->leftJoin($pluralForeignTable, $pluralForeignTable.'.id', '=', $tableName.'.'.$singularForeignTable.'_id');
      //   }
      // }
      $condition = isset($request['condition']) ? $this->initCondition($request['condition']) : array('main_table' => array(), 'foreign_table' => array());
      if(!empty($condition['foreign_table'])){
        $foreignTable = array();
        // TODO revise this to a recursive function
        foreach($condition['foreign_table'] as $foreignTable => $foreignCondition){
          $this->model = $this->model->whereHas($foreignTable, function($q) use($foreignCondition, $foreignTable){
            $tempForeignTablePlural = str_plural($foreignTable);
            for($x = 0; $x < count($foreignCondition); $x++){ // loop each
              $column = explode('.', $foreignCondition[$x]['column']);
              $value = $foreignCondition[$x]['value'];
              $clause = isset($foreignCondition[$x]['clause']) ? $foreignCondition[$x]['clause'] : '=';

              if(count($column) <= 2){ // level 1 relation
                $q->where($column[1], $clause, $value);
              }else{ // level 2 relation. maybe more
                $column2 = $column[2];
                $q->whereHas($column[1], function($q2) use($column2, $clause, $value){
                  $q2->where($column2, $clause, $value);
                });
              }
            }
          });
        }
      }
      if(isset($request['with_foreign_table'])){
          $this->model = $this->model->with($request['with_foreign_table']);
      }
      if(count($condition['main_table'])){
        $this->addCondition($condition['main_table']);
      }
      if(isset($request["id"])){
         $this->model = $this->model->where($tableName.".id", "=", $request["id"]);
      }else{
        (isset($request['sort'])) ? $this->addOrderBy($request['sort']) : null;
        if(isset($request['limit'])){
          $this->response['total_entries'] = $this->model->count();
          $this->model = $this->model->limit($request['limit']);
        }
        (isset($request['offset'])) ?  $this->model = $this->model->offset($request['offset'] * 1) : null;

      }
      if(isset($request['with_soft_delete'])){
        $this->model = $this->model->withTrashed();
      }

      for($x = 0; $x < count($tableColumns); $x++){
        $tableColumns[$x] = $tableName.'.'.$tableColumns[$x];
      }

      $result = $this->model->get($tableColumns)->toArray();
      if(count($result)){
        $this->response["data"] = $result;
        if(isset($request["id"])){
          $this->response["data"] = $this->response["data"][0];
        }
      }else{
        $this->response["error"][] = [
          "status" => 200,
          "message" => "No Result"
        ];
      }
      return $result;
    }
    public function updateEntry($request, $noFile = false){
      $responseType = isset($request['response_type']) ? $request['response_type'] : 'json';
      $tableColumns = $this->model->getTableColumns();
      $this->tableColumns = $tableColumns;
      $request = $this->filterRequest($request);
      $tableColumns = $this->model->getTableColumns();

      $this->tableColumns = $tableColumns;
      if(!$this->isValid($request, "update")){
        return $this->output();
      }
      $this->model = $this->model->find($request["id"]);
      foreach($this->tableColumns as $columnName){
        if(isset($request[$columnName])){
          $this->model->$columnName = $request[$columnName];//setting attribute
        }else if(isset($this->defaultValue[$columnName]) && isset($request[$columnName])){
          $this->model->$columnName = $this->defaultValue[$columnName];
        }

      }
      $result = $this->model->save();
      if($result && count($this->singleImageFileUpload) && !$noFile){
        $id = $this->model->id;
        for($x = 0; $x < count($this->singleImageFileUpload); $x++){
          $this->uploadSingleImageFile(
            $id,
            $this->singleImageFileUpload[$x]['name'],
            $this->singleImageFileUpload[$x]['path'],
            $this->singleImageFileUpload[$x]['column'],
            $this->singleImageFileUpload[$x]['replace']
          );
        }
      }
      if($result && $this->editableForeignTable){
        $childID = array();
        foreach($this->editableForeignTable as $childTable => $childTableValue){
          if(is_string($childTableValue)){
            $childTable = $childTableValue;
          }
          if(isset($request[$childTable]) && $request[$childTable]){
            $child = $request[$childTable];
            if(count(array_filter(array_keys($child), 'is_string')) > 0){//associative
              if(!isset($childID[$childTable])){
                $childID[$childTable] = array();
              }
              $result = false;
              if(isset($child["id"]) && $child["id"]*1) { // update
                $pk = $child["id"];
                unset($child["id"]);
                $result = $this->model->find($this->model->id)->$childTable()->where('id', $pk)->where(str_singular($this->model->getTable()).'_id', $request["id"])->update($child);
              }else if(!isset($childTableValue['no_create_on_update'])){
                $result = $this->model->find($this->model->id)->$childTable()->create($child)->id;
              }
              $childID[$childTable] = $result;
            }else{ // list
              foreach($child as $childValue){
                if(!isset($childID[$childTable])){
                  $childID[$childTable] = array();
                }
                $result = false;
                if(isset($childValue["id"]) && $childValue["id"]*1) {//update
                  $pk = $childValue["id"];
                  unset($childValue["id"]);
                  $foreignTable = $this->model->find($this->model->id)->$childTable()
                    ->where('id', $pk)
                    ->where(str_singular($this->model->getTable()).'_id', $request["id"]);
                  foreach($childValue as $childValueKey => $childValueValue){
                    if($childValueValue == null || $childValueValue == ""){
                      $foreignTable->$childValueKey = $childValueValue;
                    }
                  }
                  $result = $foreignTable
                    ->update($childValue);
                  // $foreignTable->save($foreignTable);

                }else{ //create
                  $childValue[str_singular($this->model->getTable()).'_id'] = $this->model->id;
                  $foreignTable = $this->model->newModel($childTable, $childValue);
                  // foreach($childValue as $childValueKey => $childValueValue){
                  //   if($childValueValue == null || $childValueValue == ""){
                  //     $foreignTable->$childValueKey = $childValueValue;
                  //   }
                  // }
                  $result = $this->model->find($this->model->id)->$childTable()->save($foreignTable)->id;
                }
                $childID[$childTable][] = $result;
              }
            }
          }
          if(isset($request['deleted_foreign_table'][$childTable])){
            for($x = 0; $x < count($request['deleted_foreign_table'][$childTable]); $x++){
              $this->model->find($this->model->id)->$childTable()->where('id', $request['deleted_foreign_table'][$childTable][$x])->delete();
            }
          }
        }

        $response = $this->model->id;
        if(count($childID)){
          $childID["id"] = $response;
          $response = $childID;
        }
        $this->response["data"] = $response;
        return $response;
      }else{
        if($result){
          $this->response["data"] = $result;
        }else{
          $this->response["error"] = "Failed to update entry";
        }
      }
    }
    public function deleteEntry($request){
      $responseType = isset($request['response_type']) ? $request['response_type'] : 'json';
      $validator = Validator::make($request, ["id" => "required"]);
      if ($validator->fails()) {
        $this->response["error"]["status"] = 101;
        $this->response["error"]["message"] = $validator->errors()->toArray();
        return false;
      }
      $this->response["data"] = $this->model->destroy($request["id"]);
    }
    public function addCondition($conditions){
      /*
        column, clause, value
      */
      if($conditions){
        foreach($conditions as $condition){
          /*Table.Column, Clause, Value*/
          $condition["clause"] = (isset($condition["clause"])) ? $condition["clause"] : "=";
          $condition["value"] = (isset($condition["value"])) ? $condition["value"] : null;
          switch($condition["clause"]){
            default :
              $this->model = $this->model->where($condition["column"], $condition["clause"], $condition["value"]);
          }
        }
      }
    }
    public function addOrderBy($sort)
    {
      foreach($sort as $column => $order){
        $this->model = $this->model->orderBy($column, $order);
      }
    }
    public function getAuthenticatedUser()
    {
      try {
        if (! $userRaw = JWTAuth::parseToken()->authenticate()) {
          return response()->json(['user_not_found'], 404);
        }
      } catch (Tymon\JWTAuth\Exceptions\TokenExpiredException $e) {
        return response()->json(['token_expired'], $e->getStatusCode());
      } catch (Tymon\JWTAuth\Exceptions\TokenInvalidException $e) {
        return response()->json(['token_invalid'], $e->getStatusCode());
      } catch (Tymon\JWTAuth\Exceptions\JWTException $e) {
        return response()->json(['token_absent'], $e->getStatusCode());
      } catch(\Tymon\JWTAuth\Exceptions\JWTException $e){//general JWT exception
        $this->response['debug'][] = 'No token parsed';
        return false;
      }
      // the token is valid and we have found the user via the sub claim
      $user = $userRaw->toArray();
      $this->userSession = $user;
      $this->userSession['company_id'] = NULL;
      $this->userSession['company_branch_id'] = NULL;
    }
    public function getUserCompanyDetail(){
      if(!$this->userSession){
        $this->getAuthenticatedUser();
      }
      if(!$this->userSession['company_id'] && $this->userSession){
        $company = (new CompanyBranchEmployee())->with(['company_branch'])->where('account_id', $this->userSession['id'])->get()->toArray();
        $this->userSession['company_id'] = $company ? $company[0]['company_branch']['company_id'] : 0;
        $this->userSession['company_branch_id'] = $company ? $company[0]['company_branch']['id'] : 0;
      }
    }
    public function getUserCompanyID(){
      $this->getUserCompanyDetail();
      return $this->userSession['company_id'];
    }
    public function getUserCompanyBranchID(){
      $this->getUserCompanyDetail();
      return $this->userSession['company_branch_id'];
    }
    public function getUserID(){
      if(!$this->userSession){
        $this->getAuthenticatedUser();
      }
      return $this->userSession['id'];
    }
}
