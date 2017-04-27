<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Client;

use Auth;

use App\User;

use App\Libraries\ServiceLib;

class ServiceController extends Controller
{
    function index(){

    	return view('service.login');
	    // if ( !Auth::user() ) {
	    // 	return redirect( ServiceLib::getLoginLink());
	    // }

	    // return redirect( ServiceLib::getIndexLink());
    }

    function auth(){

        $code   = request()->get('code');

        $http = new Client;

        $postData = [
            'form_params'   => [
                'grant_type'    => 'authorization_code',
                'client_id'     => env('OAUTH_CLIENT_ID'),
                'client_secret' => env('OAUTH_CLIENT_SECRET'),
                'redirect_uri'  => env('OAUTH_SERVER_REDIRECT'),
                'code'          => $code,
            ]
        ];

        $response       = $http->post(env('OAUTH_SERVER_TOKEN'), $postData );
        $arrData        = json_decode( (string) $response->getBody(), true );
        $access_token   = $arrData['access_token'];

// print_r($access_token);
// die();

        $ret = $this->getUserByToken($access_token);

        if ( !$ret ) {
            echo 'user not exist !!';
            die();

        }

        $empId      = $ret['id'];

        $loginId    = $ret['login_id'];
        $empNo      = $ret['emp_no'];
        $status     = $ret['emp_status'];
        $ename      = $ret['ename'];
        $name       = $ret['name'];
        $email      = $ret['email'];

        $groupId    = $ret['group_id'];

        $user = User::where('empId', $empId)->first();

        if ( !$user ) {
            $user = User::create(['empId' => $empId, 'logger' => '[]']);
      //      dd($user );
       //     die();
        }

        $user->loginId = $loginId;
        $user->empNo   = $empNo;
        $user->status  = $status;
        $user->ename   = $ename;
        $user->name    = $name;
        $user->email   = $email;
        $user->groupId = $groupId;

        $user->logger = json_encode( $ret );

        $user->save();

        Auth::login( $user );

        return redirect( ServiceLib::getIndexLink() );
    }

    public function getUserByToken( $accessToken )
    {
        $http       = new Client;
        $response   = $http->request('GET', env('OAUTH_SERVER_USER'), [
            'headers' => [
                'Accept'        => 'application/json',
                'Authorization' => 'Bearer ' . $accessToken,
            ],
        ]);

        return json_decode((string) $response->getBody(), true);
    }

    public function login() {
		return view('service.login');
    }

    public function logout() {
    	Auth::logout();
    	return redirect( ServiceLib::getLoginLink() );
    }
}
