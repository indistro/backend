<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

use Carbon\Carbon;

use App\Http\Controllers\HelperController;
use Illuminate\Support\Facades\Crypt;

use App\Models\AccountUsers;

class UserController extends Controller
{
  public function __construct(){
    date_default_timezone_set('Asia/Jakarta');
  }

  public function getUser(Request $request, $id){
    date_default_timezone_set('Asia/Jakarta');

    $res = array(
      'success' => 0,
      'message' => 'Unknown Error',
      'data' => array()
    );

    // check if user exist 
    $helper = new HelperController;
    $admin_auth = $helper->authUser($request,$id);

    if ($admin_auth){
        // get user Accounts
        $user_accounts = AccountUsers::select("id", "username", "email", "phone")->where("id",$id)->get();
        // Set user data to response
        array_push($res['data'],$user_accounts);
        $res['success'] = 1;
        $res['message'] = "Succeed";

    // if not Authorized
    }else{
      $res['success'] = 0;
      $res['message'] = "Not Authorized";
    }
    // Print to json
    return json_encode($res);
  }

  public function updatePassword(request $request){
    date_default_timezone_set('Asia/Jakarta');
    // Set Response
    $res = array(
      'success' => 0,
      'message' => 'Unknown Error'
    );

    $username = $request->username;
    $password = Crypt::encryptString($request->oldPassword);
    $newPassword = Crypt::encryptString($request->newPassword);
    var_dump($newPassword);
    $newPasswordComfirm = Crypt::encryptString($request->newPasswordComfirm);
    var_dump($newPasswordComfirm);

    $newPassword2 = Crypt::decryptString($newPassword);
    var_dump($newPassword2);
    $newPasswordComfirm2 = Crypt::decryptString($newPasswordComfirm);
    var_dump($newPasswordComfirm2);

    $id = $request->id;
    $account = new AccountUsers;
    // check if user exist 
    $helper = new HelperController;
    $admin_auth = $helper->checkAccountPassword($account,$id,$password);

    if ($admin_auth){
        // get user Accounts

        // if($newPassword == $newPasswordComfirm){
        //   $user_accounts = new AccountUsers;
        //   $newPassword = bcrypt($request->username.$request->newPassword);
        //   $user_accounts->where("id",$id)->update(["password" =>$newPassword]);
        //   // Set user data to response
        //   $res['success'] = 1;
        //   $res['message'] = "Password Update Succeed";  
        // }else{
        //   // Set user data to response
        //   $res['success'] = 0;
        //   $res['message'] = "Password Comfirmation Not Same";
        // }

    // if not Authorized
    }else{
      $res['success'] = 0;
      $res['message'] = "Old Password Not Match";
    }
    // Print to json
    return json_encode($res);
  }

  public function registerUser(Request $request){
    date_default_timezone_set('Asia/Jakarta');
    // Set Response
    $res = array(
      'success' => 0,
      'message' => 'Unknown Error',
      'data' => array()
    );

    try{
      // Check if user exist param setup
      $phone = $request->phone;
      $email = $request->email;
      $username = $request->username;

      $helper = new HelperController;
      $account = new AccountUsers;

      $userExist = $helper->checkAccountExist(
                              $account, $phone, $email, $username);
      // Get IP Address
      $ipaddress = $helper->getIpAddress();

      if ( ! $userExist['exist'] ) {
        // if user not exist go on
        try{
          $user_account = new AccountUsers;
          // setup user_data for insert to db
          $user_data = $this->userDataFill($request);

          // Insert to DB
          $id = $user_account->insertGetId($user_data);

          // Setup response Sucess
          $res['data'] = $user_data;
          $res['success'] = 1;
          $res['message'] = "Registration Succeed";

        }catch(Exception $e){
          // Error when inserting to DB
          $res['success'] = 0;
          $res['message'] = "Registration Failed";
        }
      }else{
        // User exist
        $res['success'] = 0;
        $res['message'] = $userExist['message'];
      }
    // Error validating user data
    }catch(Exception $e){
      $res['success'] = 0;
      $res['message'] = "Error validating user";
    }
    
    // Print JSON
    return json_encode($res);
  }

  // Update user #
  /*
    #Method => POST
    #Authentications => Member Credentials
    #Request
      @params
        request
        id
    #Response
      JSON Array of User Accounts
  */
  public function updateUser(Request $request){
    date_default_timezone_set('Asia/Jakarta');
    // Set Response
    $res = array(
      'success' => 0,
      'message' => 'Unknown Error',
      'data' => array()
    );
    // Check if user exist param setup
    $phone = $request->phone;
    $email = $request->email;
    $username = $request->username;
    $id = $request->id;

    $helper = new HelperController();
    $account = new AccountUsers;

    $userExist = $helper->checkAccountValid(
                                      $account, $id, $email, $username);
    // Get Ip address
    $ipaddress = $helper->getIpAddress();


    // Check user exist
    if ($userExist['exist'] ) {
      // if user exist go on
      try{
        $user_account = new AccountUsers;
        // setup user_data for insert to db
        $user_data = $this->userDataUpdate($request);
        // Insert to DB
        $user_account->where('id',$id)->update($user_data);
        // modify utility
        $mod_util = $helper->modifyUtility();
        $user_account->where('id',$id)->update($mod_util);

        // Setup response Sucess
        array_push($res['data'],$user_data);
        $res['success'] = 1;
        $res['message'] = "Update Succeed";
      }catch(Exception $e){
        // Error when inserting to DB
        $res['success'] = 0;
        $res['message'] = "Update Failed";
      }
    }else{
      // User exist
      $res['success'] = 0;
      $res['message'] = "User Exist";
    }
    // Print JSON
    return json_encode($res);
  }

  // Login
  /*
    @params
      email
      password
    @return
      if exist false
      else true
    */

  public function loginUser(Request $request){
    date_default_timezone_set('Asia/Jakarta');

    $user_account = new AccountUsers;

    $res = array(
      'success' => 0,
      'message' => 'Unknown Error',
      'data' => array()
    );

    // Check Email and password combinations
    $user_data = $user_account
      ->where('email', $request->email)
      ->where('password', $request->password)
      ->count();

    if ($user_data == 1 ) {
      $userdata = AccountUsers::where('email', $request->email)
        ->where('password', $request->password)
        ->get()
        ->first();

        $res['success'] = 1;
        $res['message'] = 'Login Succeed';
        $res['data']['nama'] = $request->username;
        $res['data']['rememberToken'] = $userdata->rememberToken;
      
    // Password or email incorrect
    }else{
      $res['success'] = 0;
      $res['message'] = 'password or email incorrect';
    }
    return json_encode($res);
  }

  /* Check if user exist
    @params
      request
    @return
      if exist false
      else true
  */
  function userDataFill($request){
    date_default_timezone_set('Asia/Jakarta');
    $helper = new HelperController;
    return array(
      'username'=>$request->username,
      'email'=>$request->email,
      'phone'=>$request->phone,
      'password'=>$request->password,
      'salt'=>$request->username,
      'firstName'=>$request->firstName,
      'lastName'=>$request->lastName,
      'activated'=>false,
      'dateOfBirth'=>Carbon::parse($request->dateOfBirth)->format('Y-m-d'),
      'sex'=>$request->sex,
      'address'=>$request->address,
      'addCity'=>$request->addCity,
      'addProvince'=>$request->addProvince,
      'addPostalCode'=>$request->addPostalCode,
      'addCountry'=>$request->addCountry,
      'userBankAccount'=>$request->userBankAccount,
      'userBankAccountName'=>$request->userBankAccountName,
      'lastLogin'=>Carbon::now(),
      'creby'=>'SYSTEM_USER',
      'lastIP' => $helper->getIpAddress()
    );
  }

  function userDataUpdate($request){
    date_default_timezone_set('Asia/Jakarta');
    $helper = new HelperController;
    return array(
      'username'=>$request->username,
      'email'=>$request->email,
      'phone'=>$request->phone,
      'firstName'=>$request->firstName,
      'lastName'=>$request->lastName,
      'dateOfBirth'=>Carbon::parse($request->dateOfBirth)->format('Y-m-d'),
      'sex'=>$request->sex,
      'address'=>$request->address,
      'addCity'=>$request->addCity,
      'addProvince'=>$request->addProvince,
      'addPostalCode'=>$request->addPostalCode,
      'addCountry'=>$request->addCountry,
      'userBankAccount'=>$request->userBankAccount,
      'userBankAccountName'=>$request->userBankAccountName,
    );
  }
// TODO: Delete User, Forgot Password, "get User ID", Get User Info, Get Token

}
