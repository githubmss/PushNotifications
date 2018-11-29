<?php

namespace Mmsbuilder\Pushnotification\Helper;

use \Mmsbuilder\Pushnotification\Model\PushnotificationsFactory;
use \Magento\Framework\App\Helper\AbstractHelper;

class Data extends AbstractHelper
{
    const SEND_IOS_PASSWORD = 'mss_pushnotification/setting/passphrase';
    const GOOGLE_API_KEY    = 'mss_pushnotification/setting_and/googlekey';
    const ANDROID_CODE = 1;
    const IOS_CODE     = 2;

    public $total_notification    = 0;
    public $ios_notification      = 0;
    public $android_notification  = 0;
    public $total_android_success = 0;
    public $total_ios_success     = 0;
    public $total_ios_error       = 0;
    public $total_android_error   = 0;

    /**
     * @param PushNotificationsFactory $modelNewsFactory
     */
    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        PushnotificationsFactory $modelNotificationsFactory,
        \Magento\Framework\App\Filesystem\DirectoryList $directory_list,
        \Magento\Store\Model\StoreManagerInterface $storeManager
    ) {
        parent::__construct($context);
        $this->modelNotifications = $modelNotificationsFactory;
        $this->directory_list     = $directory_list;
        $this->_storeManager      = $storeManager;
        $this->scopeConfig = $context->getScopeConfig();

    }

    public function msgConsole($type, $message)
    {
        switch ($type) {
            case '1':
                $device = 'android';
                break;
            case '2':
                $device = 'ios';
                break;
            default:
                $device = 'both';
                break;
        }
        $command = 'php bin/magento generation:notification ' . $device . ' ' . "'$message'";
        shell_exec($command);
    }

    public function sendPushNotifications($type, $message)
    {

        $response = array();
        $Model    = $this->modelNotifications->create();

        if (!$type) {
            $Collection = $Model->getCollection();
        } else {
            
            $Collection = $Model->getCollection()->addFieldToFilter('device_type', array('eq' => ($type-1)));
            $data       = $Collection->getData();

            if (!empty($data)) {

                if ($type == self::ANDROID_CODE) {

                    if ($this->getAndroidNotificationStatus()) {
                        foreach ($data as $key => $value) {
                            $status = $this->sendPushAndroid($value['registration_id'], $message);
                            if ($status == 401) {
                                $response['statusCode'] = 401;
                                $response['msg']        = "Invalid (legacy) Server-key delivered or Sender is not authorized to perform request.";
                                return $response;
                            }
                        }
                        $response['statusCode'] = 200;
                        $response['type']       = $type;
                        $response['msg']        = 'Message has been delivered';
                        $response['success']    = $this->total_android_success . ' Android notification has been sent successfully.';
                        $response['error']      = $this->total_android_error . ' Android notification has been failed.';
                        return $response;
                    } else {
                        $response['statusCode'] = 402;
                        $response['type']       = $type;
                        $response['msg']        = 'Android Notification is not enabled.';
                        return $response;
                    }
                } else if ($type == self::IOS_CODE) {

                    if ($this->getIosNotificationStatus()) {
                        foreach ($data as $key => $value) {
                            $status = $this->sendPushIOS($value['registration_id'], $message);
                            if (isset($status['error'])) {
                                $response['statusCode'] = $status['code'];
                                $response['msg']        = $status['error'];
                                return $response;
                            }
                        }
                        $response['statusCode'] = 200;
                        $response['type']       = $type;
                        $response['msg']        = 'Notifications has been sent.';
                        $response['success']    = $this->total_ios_error . ' Ios Notification has been sent successfully.';
                        $response['error']      = $this->total_ios_success . ' Ios Notification has been failed.';
                        return $response;
                    } else {
                        $response['statusCode'] = 402;
                        $response['type']       = $type;
                        $response['msg']        = 'Ios Notification is not enabled.';
                        return $response;
                    }
                } else {
                    $response['statusCode'] = 402;
                    $response['type']       = $type;
                    $response['msg']        = 'Development is in progress.';
                    return $response;
                }
            }
        }

    }

    /**
     * Sending Push Notification Android
     */

    public function sendPushAndroid($registration_id, $message)
    {   
       
        $this->android_notification++;
        $url = "https://fcm.googleapis.com/fcm/send";

        $baseUrl = parse_url($this->_storeManager->getStore()->getBaseUrl());
        $title = preg_replace("/^([a-zA-Z0-9].*\.)?([a-zA-Z0-9][a-zA-Z0-9-]{1,61}[a-zA-Z0-9]\.[a-zA-Z.]{2,})$/", '$2', $baseUrl['host']);
        $msg = array
        (
            'message'  => $message,
            'title' => $title,
                
        );
        $fields = array
        (
            'to'        => $registration_id,
            'data'  => $msg
        );
        $headers = array
        (
            'Authorization: key=' . $this->getGoogleGcmKey(),
            'Content-Type: application/json'
        );

        try {
            // Open connection
            $ch = curl_init();

            // Set the url, number of POST vars, POST data
            curl_setopt($ch, CURLOPT_URL, $url);

            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

            // Disabling SSL Certificate support temporarly
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($fields));

            // Execute post
            $result   = curl_exec($ch);
            $httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            if ($httpcode == '401') {
                curl_close($ch);
                return $httpcode;
            }
            if ($result === false) {
                $this->total_android_error++;
            } else {
                $this->total_android_success++;
            }
        } 
      
        catch (\Exception $e) {
            $error['code']  = 401;
            $error['error'] = $e->getMessage();
            return $error;
        }
 
        curl_close($ch);
        return;
    }


     public function sendPushIOS($registration_id, $message)
    {
       
        $path= $_SERVER['DOCUMENT_ROOT'].'/AudioOnline_Dev.pem';
        $this->ios_notification++;

        $passphrase = $this->getPassPharas();

        $ctx = stream_context_create();
        stream_context_set_option($ctx, 'ssl', 'local_cert', $path);
        stream_context_set_option($ctx, 'ssl', 'passphrase', $passphrase);

        $ios_mode = ($this->getMode() == 1) ? 'ssl://gateway.sandbox.push.apple.com:2195' : 'ssl://gateway.push.apple.com:2195';
        // Open a connection to the APNS server

        try {
            $fp = stream_socket_client(
                $ios_mode, $err,
                $errstr, 60, STREAM_CLIENT_CONNECT | STREAM_CLIENT_PERSISTENT, $ctx);
            if (!$fp) {
                $error['code']  = 401;
                $error['error'] = "The detail entered for IOS is not correct: $err $errstr";
                return $error;
            }

            // Create the payload body
            $body['aps'] = array(
                'alert' => $message,
                'sound' => 'default',
            );

            // Encode the payload as JSON
            $payload = json_encode($body);

            // Build the binary notification
            $msg = chr(0) . pack('n', 32) . pack('H*', $registration_id) . pack('n', strlen($payload)) . $payload;

            // Send it to the server
            $result = fwrite($fp, $msg, strlen($msg));
        } catch (\Exception $e) {
            $error['code']  = 401;
            $error['error'] = $e->getMessage();
            return $error;
        }

        if (!$result) {
            $this->total_ios_error++;
        } else {
            $this->total_ios_success++;
        }

        fclose($fp);
        return;

    }

    public function getGoogleGcmKey()
    {
        return $this->scopeConfig->getValue(
            'mmsbuilder_pushnotification/android_notification/google_api_key',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }

    public function getAndroidNotificationStatus()
    {
        return $this->scopeConfig->getValue(
            'mmsbuilder_pushnotification/android_notification/push_notification_status',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }

    public function getIosNotificationStatus()
    {
        return $this->scopeConfig->getValue(
            'mmsbuilder_pushnotification/ios_notification/ios_push_notification_status',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }

    public function getPassPharas()
    {
        return $this->scopeConfig->getValue(
            'mmsbuilder_pushnotification/ios_notification/passphras',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }

    public function getPemFile()
    {
        return $this->scopeConfig->getValue(
            'mmsbuilder_pushnotification/ios_notification/upload_pem',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }

    public function getMode()
    {
        return $this->scopeConfig->getValue(
            'mmsbuilder_pushnotification/ios_notification/push_notification_mode',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }

    public function getBackground()
    {
        return $this->scopeConfig->getValue(
            'mmsbuilder_pushnotification/background_notification/background_notification',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }

    public function sendPushNotificationsByOrder($type, $message, $registrationId)
    {
        $response = array();
        if (!empty($registrationId)) {
            if ($type == self::ANDROID_CODE) {
                if ($this->getAndroidNotificationStatus()) {

                    $status = $this->sendPushAndroid($registrationId, $message);
                    if ($status == 401) {
                        $response['statusCode'] = 401;
                        $response['msg']        = "Invalid (legacy) Server-key delivered or Sender is not authorized to perform request.";
                        return $response;
                    }

                    $response['statusCode'] = 200;
                    $response['type']       = $type;
                    $response['msg']        = 'Message has been delivered';
                    $response['success']    = $this->total_android_success . ' Android notification has been sent successfully.';
                    $response['error']      = $this->total_android_error . ' Android notification has been failed.';
                    return $response;
                } else {
                    $response['statusCode'] = 402;
                    $response['type']       = $type;
                    $response['msg']        = 'Android Notification is not enabled.';
                    return $response;
                }
            } else if ($type == self::IOS_CODE) {
                if ($this->getIosNotificationStatus()) {
                    $status = $this->sendPushIOS($registrationId, $message);
                    if (isset($status['error'])) {
                        $response['statusCode'] = $status['code'];
                        $response['msg']        = $status['error'];
                        return $response;
                    }
                    $response['statusCode'] = 200;
                    $response['type']       = $type;
                    $response['msg']        = 'Notifications has been sent.';
                    $response['success']    = $this->total_ios_error . ' Ios Notification has been sent successfully.';
                    $response['error']      = $this->total_ios_success . ' Ios Notification has been failed.';
                    return $response;
                } else {
                    $response['statusCode'] = 402;
                    $response['type']       = $type;
                    $response['msg']        = 'Ios Notification is not enabled.';
                    return $response;
                }
            } else {
                $response['statusCode'] = 402;
                $response['type']       = $type;
                $response['msg']        = 'Development is in progress.';
                return $response;
            }
        }

    }

}
