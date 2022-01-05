<?php defined('BASEPATH') OR exit('No direct script access allowed');
     
require APPPATH . 'libraries/REST_Controller.php';

class AuthController extends REST_Controller {

    public function __construct(){
        parent::__construct();
        $this->load->model('AuthModel', 'authmodel');
        $this->load->library('API_Controller');
    }
    
    public function index_get(){
        echo json_encode("AUTH CONTROLLER");
    }

    private function key()
    {
        // use database query for get valid key

        return 1452;
    }

    public function login(){
        header("Access-Control-Allow-Origin: *");

    // API Configuration
    $this->_apiConfig([
        'methods' => ['POST'],
    ]);

    // you user authentication code will go here, you can compare the user with the database or whatever
    $payload = [
        'id' => "Your User's ID",
        'other' => "Some other data"
    ];

    // Load Authorization Library or Load in autoload config file
    $this->load->library('authorization_token');

    // generte a token
    $token = $this->authorization_token->generateToken($payload);

    // return data
    $this->api_return(
        [
            'status' => true,
            "result" => [
                'token' => $token,
            ],
            
        ],
    200);
    }    

    public function view(){
        $user_data = $this->_APIConfig([
            'methods' => ['POST'],
            'requireAuthorization' => true
        ]);

        $this->api_return([
                'status' => true,
                "result" => [
                    'user_data' => $user_data['token_data']
                ]
            ], 200);
    }

    // public function login_post(){
    //     $jwt = new JWT();
    //     $post = $this->input->post();

    //     $secretKey = "secretkey";

    //     $result = $this->authmodel->check_login($post['user_id'], $post['password']);

    //     $token = $jwt->encode($result,$secretKey);

    //     echo json_encode($token);
    // }

    // public function token_get(){
    //     $jwt = new JWT();

    //     $secretKey = $this->config->item('jwt_key');
    //     $data = array(
    //         'userId' => 1,
    //         'email' => 'admin@email.com',
    //         'userType' => 'admin',
    //         'password' => 'test'
    //     ); 

    //     $token = $jwt->encode($data, $secretKey);
    //     echo $token;
    // }
    // public function decode_token_post(){
    //     $token = $this->input->post('token');
    //     $jwt = new JWT();

    //     $secretKey = $this->config->item('jwt_key');
    //     $decoded_token = $jwt->decode($token, $secretKey, 'HS256');

    //     $token1 = $jwt->jsonEncode($decoded_token);
    //     echo $token1;
    // }
}