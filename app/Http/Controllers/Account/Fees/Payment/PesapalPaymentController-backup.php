<?php
/*
 * Mr. Umesh Kumar Yadav
 * Business With Technology Pvt. Ltd.
 * Kathmandu-32 (Subidhanagar, Tinkune), Nepal
 * +977-9868156047
 * freelancerumeshnepal@gmail.com
 * https://codecanyon.net/item/unlimited-edu-firm-school-college-information-management-system/21850988
 */

namespace App\Http\Controllers\Account\Fees\Payment;
use App\Http\Controllers\CollegeBaseController;
use App\Models\FeeCollection;
use App\Models\Student;
use Carbon\Carbon;
use Illuminate\Http\Request;

class PesapalPaymentControllerBackup extends CollegeBaseController
{

    protected $base_route = 'account.fees';
    protected $view_path = 'account.fees';
    protected $panel = 'Pesapal';

    public function pesapalForm(Request $request)
    {
        //dd('ok');
        $data=[];
        $student = Student::select('students.id','students.reg_no','students.email','students.first_name','students.last_name','ai.mobile_1')
            ->where('students.id',$request->student_id)
            ->join('addressinfos as ai','ai.students_id','=','students.id')
            ->first();

        if($student) {
            $reg = $student->reg_no;
            $amount = $request->net_balance;
            $fee_masters_id = $request->fee_masters_id;
            $description = [
                'STUD_ID'        => $request->student_id,
                'REG_NO'        => $reg,
                'FEE_MASTER_ID' => $request->fee_masters_id,
                'DESCRIPTION'   => $request->description
            ];
            $description = json_encode($description);

            $reference = $reg.'-'.$request->fee_masters_id;

            $data = [
                'student_id' => $request->student_id,
                'fee_masters_id' => $fee_masters_id,
                'reference' => $reference,
                'amount' => $amount,
                'email' => $student->email,
                'firstname' => $student->first_name,
                'lastname' => $student->last_name,
                'phone' => $student->mobile_1,
                'description' => $description,
            ];
        }

        return view(parent::loadDataToView('account.fees.payment.pesapal.form'), compact('data'));
    }

    public function pesapalPayment(Request $request)
    {
        //include_once('OAuth.php');
        //include_once base_path() .'\vendor\pesapal\OAuth.php';

        //include_once base_path() .'\vendor\pesapal\OAuth.php';
        //include_once base_path() .'\vendor\pesapal\OAuthSignatureMethod.php';

        //dd($request->all());

        //pesapal params
        $token = $params = NULL;

        /*
        PesaPal Sandbox is at http://demo.pesapal.com. Use this to test your developement and
        when you are ready to go live change to https://www.pesapal.com.
        */
        $consumer_key = '1DZnnqEnnJK5zS0m063oY7fqdl9vykpF';//Register a merchant account on
        //demo.pesapal.com and use the merchant key for testing.
        //When you are ready to go live make sure you change the key to the live account
        //registered on www.pesapal.com!
        $consumer_secret = 'csdvesY5q5Hnp0B/vB92XgDW7jw=';// Use the secret from your test
        //account on demo.pesapal.com. When you are ready to go live make sure you
        //change the secret to the live account registered on www.pesapal.com!

        $signature_method = new \OAuthSignatureMethod_HMAC_SHA1();
        $iframelink = 'http://demo.pesapal.com/api/PostPesapalDirectOrderV4';//change to
        //https://www.pesapal.com/API/PostPesapalDirectOrderV4 when you are ready to go live!

       // dd($iframelink);

        //get form details
        $amount = $request->amount;
        $amount = number_format($amount, 2);//format amount to 2 decimal places

        $desc = $request->description;
        $type = $request->type; //default value = MERCHANT
        $reference = $request->reference;//unique order id of the transaction, generated by merchant
        $first_name = $request->firstname;
        $last_name = $request->lastname;
        $email = $request->email;
        $phonenumber = $request->phone;//ONE of email or phonenumber is required

        //$callback_url = 'http://www.yourdomain.com/redirect.php'; //redirect url, the page that will handle the response from pesapal.
        $callback_url = route('account.fees'); //redirect url, the page that will handle the response from pesapal.

        $post_xml = "<?xml version=\"1.0\" encoding=\"utf-8\"?><PesapalDirectOrderInfo xmlns:xsi=\"http://www.w3.org/2001/XMLSchema-instance\" xmlns:xsd=\"http://www.w3.org/2001/XMLSchema\" Amount=\"".$amount."\" Description=\"".$desc."\" Type=\"".$type."\" Reference=\"".$reference."\" FirstName=\"".$first_name."\" LastName=\"".$last_name."\" Email=\"".$email."\" PhoneNumber=\"".$phonenumber."\" xmlns=\"http://www.pesapal.com\" />";
        $post_xml = htmlentities($post_xml);

        $consumer = new \OAuthConsumer($consumer_key, $consumer_secret);

        //post transaction to pesapal
        $iframe_src = \OAuthRequest::from_consumer_and_token($consumer, $token, "GET", $iframelink, $params);
        $iframe_src->set_parameter("oauth_callback", $callback_url);
        $iframe_src->set_parameter("pesapal_request_data", $post_xml);
        $iframe_src->sign_request($signature_method, $consumer, $token);

        //display pesapal - iframe and pass iframe_src

        return back();

    }




}