<?php

namespace App\Helpers;

use Mail;
use App\Mail\EmailVerificationMail;
use App\Mail\SubscriptionExpireMail;
use App\Models\Notification;
use App\Models\User;
use App\Models\UserSubscription;
use Twilio\Rest\Client;
use App\Services\GooglePlayService;
use Auth;

class Helper
{

    /**
     * Write code on Method
     *
     * @return response()
     */
    public static function sendMail($view = '', $data = [], $to = '', $from = '', $attechMent = '')
    {
        if (empty($view) || empty($to)) {
            return false;
        }

        $subject = isset($data['subject']) ? $data['subject'] : '';

        $headers = "MIME-Version: 1.0" . "\r\n";
        $headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";
        $headers .= 'From: <info@meet-now.com>' . "\r\n";

        // For sending otp to mail

        if (isset($data['otp'])) {
            $otp = $data['otp'];
            Mail::to($data['email'])->send(new EmailVerificationMail($otp));
        }elseif (isset($data['subscription_expire'])) {
            Mail::to($data['email'])->send(new SubscriptionExpireMail($data));
        }

        return true;
    }
  
    public static function sendOtp($number,$otp)
    {
        if ($number == '') {
            return false;
        }
        $account_sid = env('TWILIO_SID', 'ACadfb118f338e01fbf732eaeb557d48d4');
        $auth_token = env('TWILIO_AUTH_TOKEN', '51361ee3094a75cd2bb6d43b71530e6a');
        $twilio_number = env('TWILIO_NUMBER', '+18557854022');
        $message = "Your meet now otp is ". $otp;
        $client = new Client($account_sid, $auth_token);
        $client->messages->create($number,['from' => $twilio_number, 'body' => $message] );
        return true;
    }

    public static function send_notification($notification_id, $sender_id = '', $receiver_id = '', $title = '', $type = '', $message = '', $custom = [])
    {
        $receiver_data = User::where('id', $receiver_id)->first();

        if ($notification_id == 'single') {
            $notification_id = [$receiver_data->fcm_token];
        }
        // This will give old badges count which is already stored...

        $badge = Notification::where('receiver_id', $receiver_id)->where(function ($query) {
            $query->where('type', 'message');
            $query->orWhere('type', 'video_call');
        })->where('status', 0)->count();

        // If new arriving notification is also for message,video_call then need to add +1 in old count

        if ($type == 'message' || $type == 'video_call') {
            $badge = $badge + 1;
        }

        if (isset($custom['image'])) {
            $image = $custom['image'];
        } else {
            $image = asset('images/meet-now.png');
        }

        if (!empty($receiver_data) && $receiver_data->is_notification_mute == 0 && $receiver_data->fcm_token != '') {
            $accesstoken = env('FCM_KEY');

            $data = [
                "registration_ids" => $notification_id,
                "notification" => [
                    "title" => $title,
                    // "body" => $message,  
                    "type" => $type,
                    "sender_id" => $sender_id,
                    "receiver_id" => $receiver_id,
                    "custom" => !empty($custom) ? json_encode($custom) : null,
                    "image" => $image,
                    "badge" => $badge,
                ],
                "data" => [
                    "title" => $title,
                    // "body" => $message,  
                    "click_action"=> "FLUTTER_NOTIFICATION_CLICK",
                    "type" => $type,
                    "sender_id" => $sender_id,
                    "receiver_id" => $receiver_id,
                    "custom" => !empty($custom) ? json_encode($custom) : null,
                    "image" => $image,
                    "badge" => $badge,
                ],
            ];
            $dataString = json_encode($data);

            $headers = [
                'Authorization:key=' . $accesstoken,
                'Content-Type: application/json',
            ];

            $ch = curl_init();

            curl_setopt($ch, CURLOPT_URL, 'https://fcm.googleapis.com/fcm/send');
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $dataString);

            $response = curl_exec($ch);
        }

        $input['sender_id']     = $sender_id;
        $input['receiver_id']   = $receiver_id;
        $input['title']         = $title;
        $input['type']          = $type;
        $input['message']       = $message;
        $input['status']        = 0;
        $input['data']          = json_encode($custom);
        $notification_data      = Notification::create($input);
        return true;
    }

    public static function send_notification_by_admin($title = '',  $message = '', $custom = [])
    {
        // Fetch all users in chunks
        User::chunk(1000, function ($users) use ($title, $custom) {
            $accesstoken = env('FCM_KEY');
            $image = isset($custom['image']) ? $custom['image'] : asset('images/meet-now.png');

            $data = [
                "registration_ids" => [],
                "notification" => [
                    "title" => $title,
                    "type" => 'admin_notificaion',
                    "sender_id" => 1,
                    "custom" => json_encode($custom),
                    "image" => $image,
                    "badge" => 0, // Initialize the badge count as 0
                ],
                "data" => [
                    "title" => $title,
                    "type" => 'admin_notificaion',
                    "sender_id" => 1,
                    "custom" => json_encode($custom),
                    "image" => $image,
                    "badge" => 0, // Initialize the badge count as 0
                ],
            ];

            foreach ($users as $user) {
                $receiver_id = $user->id;

                // Update the receiver_id and other notification data dynamically for each user
                $badge = Notification::where('receiver_id', $receiver_id)
                    ->whereIn('type', ['message', 'video_call'])
                    ->where('status', 0)
                    ->count();

                if (!empty($user->is_notification_mute) && $user->is_notification_mute == 0 && !empty($user->fcm_token)) {
                    $data['registration_ids'][] = $user->fcm_token;
                }

                // Update the badge count dynamically for each user
                $data['notification']['badge'] += $badge;
                $data['data']['badge'] += $badge;

                // Save the notification data for each user
                $input = [
                    'sender_id' => 1,
                    'receiver_id' => $receiver_id,
                    'title' => $title,
                    'type' => 'admin_notificaion',
                    'message' => $message,
                    'status' => 0,
                    'data' => json_encode($custom),
                ];
                $notification_data = Notification::create($input);
            }

            // Send the notifications in batches to FCM
            if (!empty($data['registration_ids'])) {
                $dataString = json_encode($data);
                $headers = [
                    'Authorization: key=' . $accesstoken,
                    'Content-Type: application/json',
                ];

                $ch = curl_init();
                curl_setopt($ch, CURLOPT_URL, 'https://fcm.googleapis.com/fcm/send');
                curl_setopt($ch, CURLOPT_POST, true);
                curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
                curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($ch, CURLOPT_POSTFIELDS, $dataString);
                $response = curl_exec($ch);
            }
        });

        return true; 
    }
    
    public static function googlePlanStatusCheck($productId,$purchaseToken){
        $googlePlayService = new GooglePlayService();
         
        $result = $googlePlayService->verifyPurchase($productId, $purchaseToken);
        if($result->orderId){ 
            $time = $result->expiryTimeMillis/1000;
            $exp = new \DateTime("@$time"); 
            $date = $exp->setTimezone(new \DateTimeZone('UTC'))->format('Y-m-d H:i:s') ?? Date('Y-m-d H:i:s', strtotime('+'.$plan_data->plan_duration. 'days')); 
            $is_purchased = UserSubscription::where('user_id',Auth::id())->update(['expire_date' => $date]);
        }
        return true;
    }

    public static function applePlanStatusCheck($apple_order_id){
        $fileFullPath = base_path('AuthKey_7245A2FC8Q.p8'); // DOWNLOADED FROM APP STORE CONNECT API https://appstoreconnect.apple.com/access/api
        $privateKey = '';
        if (file_exists($fileFullPath)) {
            $privateKey = file_get_contents($fileFullPath);
        } 
        $header = [
            'alg' => 'ES256',
            'kid' => env('APPLE_PLAY_KEY', '7245A2FC8Q'), // GET FROM APP STORE CONNECT API https://appstoreconnect.apple.com/access/api
            'typ' => 'JWT',
        ];
        $payload = [
            'iss' => env('APPLE_ISSUER_ID', '1a46ebc7-1fcc-48c5-94a2-dd98bb7f7421'), // GET FROM APP STORE CONNECT API https://appstoreconnect.apple.com/access/api
            'iat' => (int)time(),
            'exp' => (int)(time() + 3600),  
            "aud"=> "appstoreconnect-v1", // STATIC VALUE 
            "bid"=> "live.meetnow.meetnow", // APP BUNDLE ID
        ];

        $jwtToken = \Firebase\JWT\JWT::encode($payload, $privateKey, 'ES256', null, $header);
        $response = \Http::withHeaders([
            'Authorization' => 'Bearer ' . $jwtToken,
        ])
        ->get('https://api.storekit-sandbox.itunes.apple.com/inApps/v1/subscriptions/'.$apple_order_id);
        $statusData = $response->json();
        if ($status = $statusData['data'][0]['lastTransactions'][0]['status'] ?? null) {
            $user_id = Auth::id();
            if($status == 1 || $status == 2 || $status == 3 || $status == 4 || $status == 5){
                $encodedTransactionInfo = $statusData['data'][0]['lastTransactions'][0]['signedTransactionInfo'];
                list($header, $payload, $signature) = explode (".", $encodedTransactionInfo);
                $decode_data = json_decode(base64_decode($payload), true);
                $time = $decode_data['expiresDate']/1000;
                $exp = new \DateTime("@$time"); 
                $expiryDate = $exp->setTimezone(new \DateTimeZone('UTC'))->format('Y-m-d H:i:s');

                $is_purchased = UserSubscription::where('user_id',$user_id)->update(['expire_date' => $expiryDate]);
            }
        } 
        return true; 
    }
}
