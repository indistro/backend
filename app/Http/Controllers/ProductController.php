<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

use Carbon\Carbon;

use App\Http\Controllers\HelperController;
use Illuminate\Support\Facades\Crypt;

use App\Models\AccountUsers;

class ProductController extends Controller
{
  public function __construct(){
    date_default_timezone_set('Asia/Jakarta');
  }

  public function getProducts(Request $request){
    date_default_timezone_set('Asia/Jakarta');

    $res = array(
      'success' => 0,
      'message' => 'Unknown Error',
      'data' => array()
    );

    // check if user exist 
    $helper = new HelperController;
        // get user Accounts
        $user_accounts = Products::get();
        // Set user data to response
        array_push($res['data'],$user_accounts);
        $res['success'] = 1;
        $res['message'] = "Succeed";

    // if not Authorized
    
    // Print to json
    return json_encode($res);
  }



  public function registerProduct(Request $request){
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
      // Get IP Address
      $ipaddress = $helper->getIpAddress();
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
          $res['message'] = "Product Registration Succeed";

        }catch(Exception $e){
          // Error when inserting to DB
          $res['success'] = 0;
          $res['message'] = "Product Registration Failed";
        }
    // Error validating user data
    }catch(Exception $e){
      $res['success'] = 0;
      $res['message'] = "Error validating product";
    }
    
    // Print JSON
    return json_encode($res);
  }

  public function updateProduct(Request $request){
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
    $account = new Products;

    $userExist = $helper->checkAccountValid(
                                      $account, $id, $email, $username);
    // Get Ip address
    $ipaddress = $helper->getIpAddress();


    // Check user exist
    if ($userExist['exist'] ) {
      // if user exist go on
      try{
        $user_account = new AccountProduct;
        // setup user_data for insert to db
        $user_data = $this->productDataUpdate($request);
        // update to DB
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

  function productDataFill($request){
    date_default_timezone_set('Asia/Jakarta');
    $helper = new HelperController;
    return array(
      'name' => $request->name,
      'available'=> $request->available,
      'price'=> $request->price,
      'prodType'=> $request->prodType,
      'prodSubType'=> $request->prodSubType,
      'lastLogin'=>Carbon::now(),
      'creby'=>'SYSTEM_USER',
      'lastIP' => $helper->getIpAddress()
    );
  }

  function productDataUpdate($request){
    date_default_timezone_set('Asia/Jakarta');
    $helper = new HelperController;
    return array(
      'name' => $request->name,
      'available'=> $request->available,
      'price'=> $request->price,
      'prodType'=> $request->prodType,
      'prodSubType'=> $request->prodSubType
    );
  }
// TODO: Delete User, Forgot Password, "get User ID", Get User Info, Get Token

}
