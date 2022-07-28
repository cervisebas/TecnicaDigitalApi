<?php
    error_reporting(E_ERROR | E_PARSE);
    date_default_timezone_set('America/Argentina/Buenos_Aires');
    header('Content-type: application/json');
    $headers = apache_request_headers();
    $isAdmin = false;

    include_once 'scripts/classes.php';
    $verifyData = new VerifyData();
    $responses = new Responses();

    $verifySecure = $verifyData->verifyHeaders();
    if ($verifySecure) $isAdmin = $verifySecure['admin'];
    if (!$verifySecure) {
        echo $responses->errorHeader;
        return;
    }
    header('Access-Control-Allow-Origin: *');
    header('Access-Control-Allow-Methods: GET, POST');
    header("Access-Control-Allow-Headers: X-Requested-With");

    $fileSystem = new FileSystem();
    $student = new StudentSystem();
    $directives = new DirectiveSystem();
    $assist = new AssistSystem();
    $console = new ConsoleSystem();
    $annotations = new AnnotationSystem();
    $records = new RecordSystem();
    
    # **************************** Only Students And Family ********************************
    if (isset($_POST['family_login'])) {
        if ($verifyData->issetDataPost(array('dni'))) {
            $open = $student->family_logIn($_POST['dni']);
            echo json_encode($open);
            return;
        }
        echo json_encode($responses->errorTypical);
        return;
    }
    if (isset($_POST['family_getData'])) {
        if ($verifyData->issetDataPost(array('dni'))) {
            $idStudent = $student->family_getStudentId($_POST['dni']);
            if (is_object($idStudent)) {
                echo json_encode($idStudent);
                return;
            }
            $get = $student->family_getData($idStudent);
            echo json_encode($get);
            return;
        }
        echo json_encode($responses->errorTypical);
        return;
    }
    if (isset($_POST['family_getDataAssist'])) {
        if ($verifyData->issetDataPost(array('dni'))) {
            $idStudent = $student->family_getStudentId($_POST['dni']);
            if (is_object($idStudent)) {
                echo json_encode($idStudent);
                return;
            }
            $get = $assist->family_getData($idStudent);
            echo json_encode($get);
            return;
        }
        echo json_encode($responses->errorTypical);
        return;
    }

    if (!$isAdmin) {
        echo $responses->errorNoPost;
        return;
    }
    # **************************** ************************ ********************************

    // Students
    if (isset($_POST['addNewStudent'])) {
        if ($verifyData->issetDataPost(array('username', 'password', 'name', 'dni', 'course', 'tel', 'date'))) {
            $idDirective = $directives->getDirectiveId($_POST['username'], $_POST['password']);
            if (is_object($idDirective)) {
                echo json_encode($idDirective);
                return;
            }
            $create = $student->create($idDirective, $_POST['name'], $_POST['dni'], $_POST['course'], $_POST['tel'], $_POST['date']);
            echo json_encode($create);
            return;
        }
        echo json_encode($responses->errorTypical);
        return;
    }
    if (isset($_POST['getAllStudent'])) {
        if ($verifyData->issetDataPost(array('username', 'password'))) {
            $idDirective = $directives->getDirectiveId($_POST['username'], $_POST['password']);
            if (is_object($idDirective)) {
                echo json_encode($idDirective);
                return;
            }
            $get = $student->getAll($idDirective);
            echo json_encode($get);
            return;
        }
        echo json_encode($responses->errorTypical);
        return;
    }
    if (isset($_POST['editStudent'])) {
        if ($verifyData->issetDataPost(array('username', 'password', 'id')) && $verifyData->issetPosts(array('name', 'dni', 'course', 'tel', 'date', 'email'))) {
            $idDirective = $directives->getDirectiveId($_POST['username'], $_POST['password']);
            if (is_object($idDirective)) {
                echo json_encode($idDirective);
                return;
            }
            $edit = $student->modify($idDirective, $_POST['id'], $_POST['name'], $_POST['dni'], $_POST['course'], $_POST['tel'], $_POST['date'], $_POST['email']);
            echo json_encode($edit);
            return;
        }
        echo json_encode($responses->errorTypical);
        return;
    }
    if (isset($_POST['deleteStudent'])) {
        if ($verifyData->issetDataPost(array('username', 'password', 'id'))) {
            $idDirective = $directives->getDirectiveId($_POST['username'], $_POST['password']);
            if (is_object($idDirective)) {
                echo json_encode($idDirective);
                return;
            }
            $delete = $student->delete($idDirective, $_POST['id']);
            echo json_encode($delete);
            return;
        }
        echo json_encode($responses->errorTypical);
        return;
    }

    // Assist
    if (isset($_POST['createGroupAssist'])) {
        if ($verifyData->issetDataPost(array('username', 'password', 'curse', 'date', 'hour'))) {
            $idDirective = $directives->getDirectiveId($_POST['username'], $_POST['password']);
            if (is_object($idDirective)) {
                echo json_encode($idDirective);
                return;
            }
            $create = $assist->createGroup(
                $idDirective,
                $_POST['curse'],
                $_POST['date'],
                $_POST['hour']
            );
            echo json_encode($create);
            return;
        }
        echo json_encode($responses->errorTypical);
        return;
    }
    if (isset($_POST['getAllGroupAssist'])) {
        if ($verifyData->issetDataPost(array('username', 'password'))) {
            $idDirective = $directives->getDirectiveId($_POST['username'], $_POST['password']);
            if (is_object($idDirective)) {
                echo json_encode($idDirective);
                return;
            }
            $get = $assist->getAll($idDirective);
            echo json_encode($get);
            return;
        }
        echo json_encode($responses->errorTypical);
        return;
    }
    if (isset($_POST['getGroupAssist'])) {
        if ($verifyData->issetDataPost(array('username', 'password', 'id'))) {
            $idDirective = $directives->getDirectiveId($_POST['username'], $_POST['password']);
            if (is_object($idDirective)) {
                echo json_encode($idDirective);
                return;
            }
            $get = $assist->get($idDirective, $_POST['id']);
            echo json_encode($get);
            return;
        }
        echo json_encode($responses->errorTypical);
        return;
    }
    if (isset($_POST['confirmGroupAssist'])) {
        if ($verifyData->issetDataPost(array('username', 'password', 'idGroup', 'datas'))) {
            $idDirective = $directives->getDirectiveId($_POST['username'], $_POST['password']);
            if (is_object($idDirective)) {
                echo json_encode($idDirective);
                return;
            }
            $confirm = $assist->confirmAssist(
                $idDirective,
                $_POST['idGroup'],
                json_decode(base64_decode($_POST['datas']), true)
            );
            echo json_encode($confirm);
            return;
        }
        echo json_encode($responses->errorTypical);
        return;
    }
    if (isset($_POST['deleteGroupAssist'])) {
        if ($verifyData->issetDataPost(array('username', 'password', 'idGroup'))) {
            $idDirective = $directives->getDirectiveId($_POST['username'], $_POST['password']);
            if (is_object($idDirective)) {
                echo json_encode($idDirective);
                return;
            }
            $delete = $assist->delete($idDirective, $_POST['idGroup']);
            echo json_encode($delete);
            return;
        }
        echo json_encode($responses->errorTypical);
        return;
    }
    if (isset($_POST['getIndividualAssist'])) {
        if ($verifyData->issetDataPost(array('username', 'password', 'idStudent'))) {
            $idDirective = $directives->getDirectiveId($_POST['username'], $_POST['password']);
            if (is_object($idDirective)) {
                echo json_encode($idDirective);
                return;
            }
            $get = $assist->getIndividual($idDirective, $_POST['idStudent']);
            echo json_encode($get);
            return;
        }
        echo json_encode($responses->errorTypical);
        return;
    }

    // Annotations
    if (isset($_POST['setAnnotationAssist'])) {
        if ($verifyData->issetDataPost(array('username', 'password', 'idGroup', 'note'))) {
            $idDirective = $directives->getDirectiveId($_POST['username'], $_POST['password']);
            if (is_object($idDirective)) {
                echo json_encode($idDirective);
                return;
            }
            $set = $annotations->set($idDirective, $_POST['idGroup'], $_POST['note']);
            echo json_encode($set);
            return;
        }
        echo json_encode($responses->errorTypical);
        return;
    }
    if (isset($_POST['getGroupAnnotationAssist'])) {
        if ($verifyData->issetDataPost(array('username', 'password', 'idGroup'))) {
            $idDirective = $directives->getDirectiveId($_POST['username'], $_POST['password']);
            if (is_object($idDirective)) {
                echo json_encode($idDirective);
                return;
            }
            $get = $annotations->getAll($idDirective, $_POST['idGroup']);
            echo json_encode($get);
            return;
        }
        echo json_encode($responses->errorTypical);
        return;
    }
    if (isset($_POST['deleteAnnotationAssist'])) {
        if ($verifyData->issetDataPost(array('username', 'password', 'idGroup'))) {
            $idDirective = $directives->getDirectiveId($_POST['username'], $_POST['password']);
            if (is_object($idDirective)) {
                echo json_encode($idDirective);
                return;
            }
            $delete = $annotations->delete($idDirective, $_POST['idGroup']);
            echo json_encode($delete);
            return;
        }
        echo json_encode($responses->errorTypical);
        return;
    }

    // Directives
    if (isset($_POST['openSessionDirectives'])) {
        if ($verifyData->issetDataPost(array('username', 'password'))) {
            $open = $directives->open($_POST['username'], $_POST['password']);
            echo json_encode($open);
            return;
        }
        echo json_encode($responses->errorTypical);
        return;
    }
    if (isset($_POST['getAllDirectives'])) {
        if ($verifyData->issetDataPost(array('username', 'password'))) {
            $idDirective = $directives->getDirectiveId($_POST['username'], $_POST['password']);
            if (is_object($idDirective)) {
                echo json_encode($idDirective);
                return;
            }
            $get = $directives->getAllDirectives($idDirective);
            echo json_encode($get);
            return;
        }
        echo json_encode($responses->errorTypical);
        return;
    }
    if (isset($_POST['editDirective'])) {
        if ($verifyData->issetDataPost(array('username', 'password', 'idEdit')) && $verifyData->issetPosts(array('name', 'position', 'dni', 'newUsername', 'newPassword', 'permission'))) {
            $idDirective = $directives->getDirectiveId($_POST['username'], $_POST['password']);
            if (is_object($idDirective)) {
                echo json_encode($idDirective);
                return;
            }
            $edit = $directives->modify(
                $idDirective,
                $_POST['idEdit'],
                $_POST['name'],
                $_POST['position'],
                $_POST['dni'],
                $_POST['newUsername'],
                $_POST['newPassword'],
                $_POST['permission']
            );
            echo json_encode($edit);
            return;
        }
        echo json_encode($responses->errorTypical);
        return;
    }
    if (isset($_POST['addDirective'])) {
        if ($verifyData->issetDataPost(array('username', 'password', 'name', 'position', 'dni', 'newUsername', 'newPassword', 'permission'))) {
            $idDirective = $directives->getDirectiveId($_POST['username'], $_POST['password']);
            if (is_object($idDirective)) {
                echo json_encode($idDirective);
                return;
            }
            $set = $directives->create(
                $idDirective,
                $_POST['name'],
                $_POST['position'],
                $_POST['dni'],
                $_POST['newUsername'],
                $_POST['newPassword'],
                $_POST['permission']
            );
            echo json_encode($set);
            return;
        }
        echo json_encode($responses->errorTypical);
        return;
    }
    if (isset($_POST['deleteDirective'])) {
        if ($verifyData->issetDataPost(array('username', 'password', 'idDirective'))) {
            $idDirective = $directives->getDirectiveId($_POST['username'], $_POST['password']);
            if (is_object($idDirective)) {
                echo json_encode($idDirective);
                return;
            }
            $delete = $directives->delete($idDirective, $_POST['idDirective']);
            echo json_encode($delete);
            return;
        }
        echo json_encode($responses->errorTypical);
        return;
    }

    // Records
    if (isset($_POST['getRecords'])) {
        if ($verifyData->issetDataPost(array('username', 'password'))) {
            $idDirective = $directives->getDirectiveId($_POST['username'], $_POST['password']);
            if (is_object($idDirective)) {
                echo json_encode($idDirective);
                return;
            }
            $get = $records->getAll($idDirective);
            echo json_encode($get);
            return;
        }
        echo json_encode($responses->errorTypical);
        return;
    }

    // Console
    if (isset($_POST['getAllStudentsConsole'])) {
        if ($verifyData->issetDataPost(array('keyAccess', 'dateAccess'))) {
            $verify = $console->verify($_POST['keyAccess'], $_POST['dateAccess']);
            if (is_object($verify)) {
                echo json_encode($verify);
                return;
            }
            $get = $student->get_console();
            echo json_encode($get);
            return;
        }
        echo json_encode($responses->errorTypical);
        return;
    }

    echo $responses->errorNoPost;
?>