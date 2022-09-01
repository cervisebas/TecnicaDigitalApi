<?php
    //include_once 'scripts/classes.php';

    /*$directives = new DirectiveSystem();

    $directives->create(
        base64_encode('Jose Alegre'),
        base64_encode('Docente'),
        base64_encode('31154724'),
        base64_encode('alegre.tecnica'),
        base64_encode('alegre_2022')
    );*/
    //$filesystem = new FileSystem();

    $compare = (strtotime("2022/09/01") - strtotime("2022/08/01")) >= 0;

    echo "<p style='margin: 0;'>".strtotime("2022/08/01")."</p><br>";
    echo "<p style='margin: 0;'>".strtotime("2022/09/01")."</p><br>";
    echo "<p style='margin: 0;'>".($compare ? "true": "false")."</p><br>";

    /*$image = $filesystem->createStudentImage2("./632051.png");*/
?>
