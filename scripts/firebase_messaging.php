<?php
    include_once 'classes.php';

    class FirebaseMessagingSystem {
        private string $server_key = "AAAAzjV7P40:APA91bH0sO_JSbFB9wESf9NY2rSEYepOqwk-gRe9Xh4OLIueLB4xP-3Yi9wM_Vz-HwFiqV6JP2lLBqQQqQYdC6UOAiD6ng8xDDyzJIGt-ab8TFXmjAajxPDnYQNGFcC8BcVo4cPyxpip";
        public function send($data, string $to, bool $all) {
            $t = ($all)? '/topics/all': $to;
            $post = $this->post($data, $t);
            return $post;
        }
        private function post($notification, string $to) {
            try {
                $data = json_encode(array("notification" => $notification, "to" => $to));
                $headers = array("Content-Type:application/json", "Authorization:key=$this->server_key");
                $ch = curl_init();
                curl_setopt($ch, CURLOPT_URL, "https://fcm.googleapis.com/fcm/send");
                curl_setopt($ch, CURLOPT_POST, true);
                curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
                $result = curl_exec($ch);
                curl_close($ch);
                return ($result !== false);
            } catch (\Throwable $th) {
                return false;
            }
        }
        public function multiplePost($notifications) {
            try {
                $headers = array("Content-Type:application/json", "Authorization:key=$this->server_key");
                $url = "https://fcm.googleapis.com/fcm/send";
                foreach ($notifications as &$notif) {
                    $ch = curl_init();
                    curl_setopt($ch, CURLOPT_URL, $url);
                    curl_setopt($ch, CURLOPT_POST, true);
                    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
                    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode(array(
                        "notification" => $notif['notification'],
                        "to" => $notif['to']
                    )));
                    curl_exec($ch);
                    curl_close($ch);
                }
                return true;
            } catch (\Throwable $th) {
                return false;
            }
        }
    }
    
?>