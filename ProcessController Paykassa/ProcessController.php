<?php
namespace App\Http\Controllers\Gateway\btc_paykassa;
use App\Models\Deposit;
use App\Models\GeneralSetting;
use App\Http\Controllers\Gateway\PaymentController;
use App\Http\Controllers\Controller;
use Auth;
 class PayKassaSCI {
        public $version = "0.4";
        public function __construct ($sci_id, $sci_key, $test=false)  {
            $this->domain = $_SERVER['SERVER_NAME'];
            $this->params = [];
            $this->params["sci_id"] = $sci_id;
            $this->params["sci_key"] = $sci_key;
            $this->params["test"] = $test;
            $this->params["domain"] = $this->domain;

            $this->url = "https://paykassa.app/sci/".$this->version."/index.php";

            $this->curl = null;
            if (null === $this->curl) {
                $this->curl = curl_init();
                curl_setopt($this->curl, CURLOPT_FAILONERROR, true);
                curl_setopt($this->curl, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($this->curl, CURLOPT_SSL_VERIFYPEER, 0);
                curl_setopt($this->curl, CURLOPT_SSL_VERIFYHOST, 0);

                if (!defined('CURL_HTTP_VERSION_2_0')) {
                    define('CURL_HTTP_VERSION_2_0', 3);
                }

                curl_setopt($this->curl, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_2_0);

                curl_setopt($this->curl, CURLOPT_HTTPHEADER,[
                    'Content-type: application/x-www-form-urlencoded',
                ]);
            }
        }

        private function ok($message, $data=[]) {
            return [
                'error' => true,
                'message' => $message,
                'data' => $data,
            ];
        }

        private function err($message, $data=[]) {
            return [
                'error' => true,
                'message' => $message,
                'data' => $data,
            ];
        }

        public function post_json_request($url, $data=[]) {
            return $this->request_post($url, $data);
        }

        public function request_post($url, $data=[]) {
            curl_setopt($this->curl, CURLOPT_URL, $url);
            curl_setopt($this->curl, CURLOPT_POSTFIELDS, http_build_query($data));
            curl_setopt($this->curl, CURLOPT_POST, 1);

            $res = curl_exec($this->curl);
            if (false === $res) {
                return $this->err(
                    curl_error($this->curl)
                );
            }
            $data = json_decode($res, true);
            if (null === $data || false === $data) {
                log_write("Json parse error. Cryptokassa API", $res, $data);
                return $this->err(
                    'Json parse error: '. json_last_error_msg()
                );
            }

            if (!isset($data['error'], $data['message'])) {
                return $this->err('Bad format response');
            }

            return $data;
        }

        private function query($url, $data=[]) {
            $result = $this->post_json_request($url, $data + $this->params);
            if ($result === false || $result === null) {
                return [
                    "error" => true,
                    "message" => "Ошибка запроса к сервису!",
                ];
            }
            return $result;
        }

        public function sci_create_order($amount, $currency, $order_id="", $comment="", $system=0, $phone=false, $paid_commission="", $static='no') {
            $fields = ["amount", "currency", "order_id", "comment", "system", "phone", "paid_commission", "static", ];
            return $this->query($this->url, [
                "func" => "sci_create_order",
            ] + compact($fields));
        }

        public function sci_create_order_merchant($amount, $currency, $order_id="", $comment="", $system=0, $phone=false, $paid_commission="", $static='no') {
            $fields = ["amount", "currency", "order_id", "comment", "system", "phone", "paid_commission", "static", ];
            return $this->query($this->url, [
                    "func" => "sci_create_order_merchant",
                ] + compact($fields));
        }

        public function sci_create_order_get_data($amount, $currency, $order_id="", $comment="", $system=0, $phone=false, $paid_commission="", $static='no') {
            $fields = ["amount", "currency", "order_id", "comment", "system", "phone", "paid_commission", "static", ];
            return $this->query($this->url, [
                    "func" => "sci_create_order_get_data",
                ] + compact($fields));
        }

        public function sci_confirm_order($hash) {
            $private_hash = $hash;
            $fields = ["private_hash",];
            return $this->query($this->url, [
                "func" => "sci_confirm_order",
            ] + compact($fields));
        }
        
        public function sci_confirm_transaction_notification($order) {
            $private_hash =$order;
            $fields = ["private_hash",];
            return $this->query($this->url, [
                "func" => "sci_confirm_transaction_notification",
            ] + compact($fields));
        }

        public function alert($message) { ?>
            <script>window.alert("<?php echo $message; ?>");</script>
        <?php
        }

        public function format_currency($money, $currency) {
            if (strtolower($currency) === "btc") {
                return format_btc($money);
            }
            return format_money($money);
        }

        public function format_btc($money) {
            return sprintf("%01.8f", $money);
        }

        public function format_money($money) {
            return sprintf("%01.2f", $money);
        }

        public function post($key, $value=false) {
            if ($value) {
                $_POST[$key] = $value;
            }
            return isset($_POST[(string)$key]) ? $_POST[(string)$key] : "";
        }

        public function get($key, $value=false) {
            if ($value) {
                $_GET[$key] = $value;
            }
            return isset($_GET[(string)$key]) ? $_GET[(string)$key] : "";
        }

        public function request($key, $value=false) {
            if ($value) {
                $_REQUEST[$key] = $value;
            }
            return isset($_REQUEST[(string)$key]) ? $_REQUEST[(string)$key] : "";
        }
    }



class ProcessController extends Controller
{

    /*
     * Perfect Money Gateway
     */
    public static function process($deposit)
    {
        $basic =  GeneralSetting::first();

        $gateway_currency = $deposit->gateway_currency();

        $perfectAcc = json_decode($gateway_currency->gateway_parameter);

        $gateway_alias = $gateway_currency->gateway_alias;

       
  

  
    $id_c = '11';
$currency = 'BTC';
       $url ="https://currency.paykassa.pro/index.php?currency_in=usd&currency_out=btc";
$cURL = curl_init();
curl_setopt($cURL, CURLOPT_URL, $url);
curl_setopt($cURL, CURLOPT_HTTPGET, true);
curl_setopt($cURL, CURLOPT_RETURNTRANSFER, true);
curl_setopt($cURL, CURLOPT_HTTPHEADER, array(
    'Content-Type: application/json',
    'Accept: application/json'
));
$result = curl_exec($cURL);
curl_close($cURL);
$result = json_decode($result);
$amount = $deposit->final_amo;


      $paykassa_merchant_id = '';  // paykassa merchant_id
    $paykassa_merchant_password = ''; // paykassa merchant password

    $paykassa = new PayKassaSCI( 
        $paykassa_merchant_id,       // идентификатор мерчанта
        $paykassa_merchant_password,
        false                                                // пароль мерчанта
    );
        $order_id = $deposit->trx;
        $comment = time();
$res = $paykassa->sci_create_order(
        $amount,    // обязательный параметр, сумма платежа, пример: 1.0433
        $currency,  // обязательный параметр, валюта, пример: BTC
        $order_id,  // обязательный параметр, уникальный числовой идентификатор платежа в вашей системе, пример: 150800
        $comment,   // обязательный параметр, текстовый комментарий платежа, пример: Заказ услуги #150800
        $id_c // обязательный параметр, указав его Вас минуя мерчант переадресует на платежную систему, пример: 12 - Ethereum
    );
    
     $val['PAYEE_NAME'] = $basic->sitename;
        $send['val'] = $val;
        $send['view'] = 'user.payment.redirect';
        $send['method'] = 'post';
        $send['url'] = $res['data']['url'];

        return json_encode($send);
    }
    
    
    public function ipn()
    {
    $paykassa_merchant_id = '15986';
    $paykassa_merchant_password = 'OFzRTJYscVsRmg1h90XnzyLCSAmy7nF9';
    $paykassa = new PayKassaSCI( 
        $paykassa_merchant_id,       // идентификатор мерчанта
        $paykassa_merchant_password,
        false                                                // пароль мерчанта
    );
    

$hash = $_REQUEST['private_hash'];
    $res = $paykassa->sci_confirm_order($hash);
    if ($res['error']) {        // $res['error'] - true если ошибка
        die($res['message']);   // $res['message'] - текст сообщения об ошибке
        // действия в случае ошибки
    } else {
        // действия в случае успеха        
        $id = $res["data"]["order_id"];        // уникальный числовой идентификатор платежа в вашей системе, пример: 150800
        $transaction = $res["data"]["transaction"]; // номер транзакции в системе paykassa: 96401
        $hash = $res["data"]["hash"];               // hash, пример: bde834a2f48143f733fcc9684e4ae0212b370d015cf6d3f769c9bc695ab078d1
        $currency = $res["data"]["currency"];       // валюта платежа, пример: DASH
        $system = $res["data"]["system"];           // система, пример: Dash
        $address = $res["data"]["address"];         // адрес криптовалютного кошелька, пример: Xybb9RNvdMx8vq7z24srfr1FQCAFbFGWLg
        $tag = $res["data"]["tag"];                 // Tag для Ripple и Stellar
        $partial = $res["data"]["partial"];      
        $amount = (float)$res["data"]["amount"];
$url ="https://currency.paykassa.pro/index.php?currency_in=".$currency."&currency_out=usd";
$cURL = curl_init();
curl_setopt($cURL, CURLOPT_URL, $url);
curl_setopt($cURL, CURLOPT_HTTPGET, true);
curl_setopt($cURL, CURLOPT_RETURNTRANSFER, true);
curl_setopt($cURL, CURLOPT_HTTPHEADER, array(
    'Content-Type: application/json',
    'Accept: application/json'
));
$result = curl_exec($cURL);
curl_close($cURL);
$result = json_decode($result);
if($currency == 'perfectmoney')
{
 $amount_total = $amount;   
}else{
    $amount_total = $amount * $result->data->value;
}

        
        
             $data = Deposit::where('trx', $id)->orderBy('id', 'DESC')->first();
              if ($amount_total >= getAmount($data->final_amo) && $data->status == '0') {
                PaymentController::userDataUpdate($data->trx);
            }
   echo $id.'|success'; // обязательно, для подтверждения зачисления платежа
    }    
    }
}
