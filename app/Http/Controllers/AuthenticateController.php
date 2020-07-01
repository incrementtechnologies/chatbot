<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use Tymon\JWTAuth\Facades\JWTAuth;
use Tymon\JWTAuthExceptions\JWTException;

use App\UserAuth;

class AuthenticateController extends Controller
{
  public function __construct()
   {
     // Apply the jwt.auth middleware to all methods in this controller
     // except for the authenticate method. We don't want to prevent
     // the user from retrieving their token if they don't already have it
     $this->middleware('jwt.auth', ['except' => ['authenticate']]);
   }
  public function index()
  {
    $users = UserAuth::all();
    return $users;
  }
  public function refreshToken(){
    // config/jwt.php ttl to change token life
    // if ($token = JWTAuth::parseToken()->refresh()){
    //   return response()->json(['error' => 'failed_parse', 'token' => JWTAuth::getToken()], 401);
    // }
    $current_token  = JWTAuth::getToken();
    $token          = JWTAuth::refresh($current_token);
    return response()->json(compact('token'));
  }
  public function authenticate(Request $request)
  {
      $credentials = $request->only('username', 'password');
      try {
        // verify the credentials and create a token for the user
        if (! $token = JWTAuth::attempt($credentials)) {
            return response()->json(['error' => 'invalid_credentials'], 401);
        }
      } catch (JWTException $e) {
        // something went wrong
        return response()->json(['error' => 'could_not_create_token'], 500);
      }
      // if no errors are encountered we can return a JWT
      return response()->json(compact('token'));
  }
  public function deauthenticate(){
    JWTAuth::invalidate(JWTAuth::getToken());
    return response()->json(['token' => NULL]);
  }
  public function getAuthenticatedUser()
  {
      try {
        if (! $user = JWTAuth::parseToken()->authenticate()) {
          return response()->json(['user_not_found'], 404);
        }
      } catch (Tymon\JWTAuth\Exceptions\TokenExpiredException $e) {
        return response()->json(['token_expired'], $e->getStatusCode());
      } catch (Tymon\JWTAuth\Exceptions\TokenInvalidException $e) {
        return response()->json(['token_invalid'], $e->getStatusCode());
      } catch (Tymon\JWTAuth\Exceptions\JWTException $e) {
        return response()->json(['token_absent'], $e->getStatusCode());
      }

      // the token is valid and we have found the user via the sub claim
      return response()->json($user);
  }
}
