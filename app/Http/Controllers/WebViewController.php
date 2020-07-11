<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class WebViewController extends Controller
{
    //
    public function packageForm(){
         return view('package_form');
    }
}
