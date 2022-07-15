<?php

    include_once 'classes.php';

    class NotificationSystem {
        public function send($idStudent, string $title, string $body) {
            $firebase = new FirebaseMessagingSystem();
            $notification = array("title" => $title, "body" => $body);
            $firebase->send($notification, "/topics/student-$idStudent", false);
        }
        public function multiSend($datas) {
            $firebase = new FirebaseMessagingSystem();
            $notifications = array();
            foreach ($datas as &$data) {
                $idStudent = $data['to'];
                array_push($notifications, array(
                    'to' => "/topics/student-$idStudent",
                    'notification' => array(
                        'title' => $data['title'],
                        'body' => $data['body'],
                        'sound' => 'default'
                    )
                ));
            }
            $firebase->multiplePost($notifications);
        }
    }
?>