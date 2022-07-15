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
        /*public function multiplePost($notifications) {
            try {
                $datas = array();
                $headers = array("Content-Type:application/json", "Authorization:key=$this->server_key");
                foreach ($notifications as &$notif) {
                    array_push($datas, array("notification" => $notif['notification'], "to" => $notif['to']));
                }
                
                $curls = array();
                $mh = curl_multi_init();
                $active = null;
                
                foreach ($datas as $index => $data) {
                    $curls[$index] = curl_init();
                    curl_setopt($curls[$index], CURLOPT_URL, "https://fcm.googleapis.com/fcm/send");
                    curl_setopt($curls[$index], CURLOPT_POST, true);
                    curl_setopt($curls[$index], CURLOPT_HTTPHEADER, $headers);
                    curl_setopt($curls[$index], CURLOPT_RETURNTRANSFER, true);
                    curl_setopt($curls[$index], CURLOPT_POSTFIELDS, $data);
                    curl_multi_add_handle($mh, $curls[$index]);
                }

                do {
                    curl_multi_exec($mh, $active);
                } while ($active);

                foreach ($curls as &$curl) {
                    curl_multi_remove_handle($mh, $curl);
                }

                curl_multi_close($mh);
            } catch (\Throwable $th) {
                return false;
            }
        }*/
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