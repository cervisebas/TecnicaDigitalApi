<?php
    include_once 'classes.php';

    $db = new DBSystem();
    $notification = new NotificationSystem();

    try {
        $consult = $db->Query("SELECT * FROM `notifications`");
        if ($consult) {
            if ($consult->num_rows == 0) {
                echo "false";
                return;
            }
            $ids = "";
            while ($data = $consult->fetch_array()) {
                $notify = unserialize(base64_decode($data['datas']));
                $notification->multiSend($notify);
                $ids = $ids.((strlen($ids) == 0)? "": ", ").$data['id'];
            }
            $db->Query("DELETE FROM `notifications` WHERE `id` IN ($ids)");
            echo "true";
            return;
        }
        echo "false";
    } catch (\Throwable $th) {
        echo "false";
    } 
?>