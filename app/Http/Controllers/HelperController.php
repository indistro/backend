<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

use Carbon\Carbon;

use App\Models\AccountUsers;
use App\Models\AccountAdmins;
use App\Models\AccountVendors;

class HelperController extends Controller
{
  // Helper Functions -------------------------------------------------------

  public function checkAccountExist($account, $phone = null,
                             $email = null, $username = null){
    // Return setup
    $retval = array('exist' => false, 'message' => 'Account not exists');

    // check if account name, phone, email exist
    $status = array(
      'account_name' => $this->checkAccountName($account, $username),
      'account_phone' => $this->checkAccountPhone($account, $phone),
      'account_email' => $this->checkAccountEmail($account, $email),
    );
    if ($status['account_name']['exist']) {
      $retval['exist'] = $status['account_name']['exist'];
      $retval['message'] = $status['account_name']['message'];
    }
    if ($status['account_phone']['exist']) {
      $retval['exist'] = $status['account_phone']['exist'];
      $retval['message'] = $status['account_phone']['message'];
    }
    if ($status['account_email']['exist']) {
      $retval['exist'] = $status['account_email']['exist'];
      $retval['message'] = $status['account_email']['message'];
    }
    return $retval;
  }

  public function checkAccountValid($account, $id = null,
                             $email = null, $username = null){
    // Return setup
    $retval = array('exist' => false, 'message' => 'Account not exists');

    // check if account name, phone, email exist
    $status = array(
      'account_name' => $this->checkAccountName($account, $username),
      'account_id' => $this->checkAccountPhone($account, $id),
      'account_email' => $this->checkAccountEmail($account, $email),
    );
    if ($status['account_name']['exist']) {
      $retval['exist'] = $status['account_name']['exist'];
      $retval['message'] = $status['account_name']['message'];
    }
    if ($status['account_id']['exist']) {
      $retval['exist'] = $status['account_id']['exist'];
      $retval['message'] = $status['account_id']['message'];
    }
    if ($status['account_email']['exist']) {
      $retval['exist'] = $status['account_email']['exist'];
      $retval['message'] = $status['account_email']['message'];
    }
    return $retval;
  }

  public function checkAccountName($account, $username){
    // Retval setup
    $retval = array('exist' => false, 'message'=>'Username not exist');
    // Check if username exists
    if ($account::where('username', $username)->count() == 1 ){
      $retval['exist'] = true;
      $retval['message'] = 'Username Exists';
    }
    return $retval;
  }

  public function checkAccountPhone($account, $phone){
    // Retval setup
    $retval = array('exist' => false, 'message'=>'Phone number not exist');
    // Check if username exists
    if ($account::where('phone', $phone)->count() == 1 ){
      $retval['exist'] = true;
      $retval['message'] = 'Phone Exists';
    }
    return $retval;
  }

  public function checkAccountEmail($account, $email){
    // Retval setup
    $retval = array('exist' => false, 'message'=>'Email not exist');
    // Check if username exists
    if ($account::where('email', $email)->count() == 1 ){
      $retval['exist'] = true;
      $retval['message'] = 'Email Exists';
    }
    return $retval;
  }

  //cek password
  public function checkAccountPassword($account, $id = null, $password = null){
    // Retval setup
    $retval = array('exist' => false, 'message'=>'Email not exist');
    // Check if username exists
    if ($account::where([['id', $id],['password',$password],])->count() == 1 ){
      $retval['exist'] = true;
      $retval['message'] = 'exists';
    }
    return $retval;
  }

  public function checkTokenExist($Account, $token){
    if ($Account::where('token', $token)->count() == 0 ){
      return true;
    }
    return false;
  }

  public function getIpAddress(){
    $condition = (
      isset($_SERVER['HTTP_X_FORWARDED_FOR'])&& $_SERVER['HTTP_X_FORWARDED_FOR']
    );
    if ($condition) {
      return $_SERVER['HTTP_X_FORWARDED_FOR'];
    } else {
      return $_SERVER['REMOTE_ADDR'];
    }
  }


  public function isOurServer(){
    $ip = $this->getIpAddress();
    /*Check apakah ip kantor atau bukan ? */
    $condition = ($ip == 'IP_DEVEL_SERVER_HERE') ;
    return $condition;
  }

  public function modifyUtility(){
    date_default_timezone_set('Asia/Jakarta');
    return array(
      'modTime' => Carbon::now(),
      'modBy' => 'SYSTEM_USER',
      'lastIp' => $this->getIpAddress()
    );
  }

  public function authAdminLevel(Request $request, $level){
    // Authenticate Admin Level-3
    $req_auth = $request->header('Authentications');
    $admin_auth = AccountAdmins::where('token',$req_auth)
      ->where('level',$level)
      ->count();
      return $admin_auth;
  }
// Check User Exist
  public function authUser(Request  $request, $id){
    $admin_auth = AccountUsers::where('id',$id)
      ->count();
      return $admin_auth;
  }

}

?>
