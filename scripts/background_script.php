<?php
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, "https://tecnica-digital.ga/scripts/auto_notifee.php");
    curl_setopt($ch, CURLOPT_TIMEOUT, 120000);
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 120000);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $data = curl_exec($ch);
    echo "<p>$data</p>";
    curl_close($ch);
?>