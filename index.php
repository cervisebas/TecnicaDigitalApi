<?php
    error_reporting(E_ERROR | E_WARNING | E_PARSE);
    // CORS
    if (isset($_SERVER['HTTP_ORIGIN'])) {
        header("Access-Control-Allow-Origin: {$_SERVER['HTTP_ORIGIN']}");
        header('Access-Control-Allow-Credentials: true');
        header('Access-Control-Max-Age: 86400');
    }
    if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
        if (isset($_SERVER['HTTP_ACCESS_CONTROL_REQUEST_METHOD'])) header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
        if (isset($_SERVER['HTTP_ACCESS_CONTROL_REQUEST_HEADERS'])) header("Access-Control-Allow-Headers: {$_SERVER['HTTP_ACCESS_CONTROL_REQUEST_HEADERS']}");
        exit(0);
    }
    date_default_timezone_set('America/Argentina/Buenos_Aires');
    header('Content-type: application/json');
    $headers = apache_request_headers();
    $isAdmin = false;

    include_once 'scripts/classes.php';
    $verifyData = new VerifyData();
    $responses = new Responses();

    $verifySecure = $verifyData->verifyHeaders();
    if ($verifySecure) {
        if (!$verifySecure['ok']) {
            echo json_encode($verifySecure);
            return;
        }
        $isAdmin = $verifySecure['admin'];
    }
    if (!$verifySecure) {
        echo json_encode($responses->errorHeader);
        return;
    }

    $fileSystem = new FileSystem();
    $student = new StudentSystem();
    $directives = new DirectiveSystem();
    $assist = new AssistSystem();
    $console = new ConsoleSystem();
    $annotations = new AnnotationSystem();
    $records = new RecordSystem();
    $cursesGroup = new CursesGroupSystem();
    $matters = new MatterSheduleSystem();
    $schedule = new ScheduleSystem();
    $preferences = new DirectivesPreferencesSystem();
    
    # **************************** Only Students And Family ********************************
    if (isset($_POST['family_login'])) {
        if ($verifyData->issetDataPost(array('dni'))) {
            $verifyData->checkDataTypes($_POST['dni'], 'string-base64');
            
            $open = $student->family_logIn($_POST['dni']);
            echo json_encode($open);
            return;
        }
        echo json_encode($responses->errorTypical);
        return;
    }
    if (isset($_POST['family_getData'])) {
        if ($verifyData->issetDataPost(array('dni'))) {
            $verifyData->checkDataTypes($_POST['dni'], 'string-base64');

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
            $verifyData->checkDataTypes($_POST['dni'], 'string-base64');
            
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
    if (isset($_POST['family_getSchedule'])) {
        if ($verifyData->issetDataPost(array('dni', 'curse'))) {
            $verifyData->checkDataTypes($_POST['dni'], 'string-base64', $_POST['curse'], 'string-base64');
            
            $idStudent = $student->family_getStudentId($_POST['dni']);
            if (is_object($idStudent)) {
                echo json_encode($idStudent);
                return;
            }
            $get = $schedule->get($_POST['curse']);
            echo json_encode($get);
            return;
        }
        echo json_encode($responses->errorTypical);
        return;
    }

    if (!$isAdmin) {
        echo json_encode($responses->errorNoPost);
        return;
    }
    # **************************** ************************ ********************************

    // Students
    if (isset($_POST['addNewStudent'])) {
        if ($verifyData->issetDataPost(array('username', 'password', 'name', 'dni', 'course', 'tel', 'date'))) {
            $verifyData->checkDataTypes(
                $_POST['username'], 'string-base64',
                $_POST['password'], 'string-base64',
                $_POST['name'], 'string-base64',
                $_POST['dni'], 'string-base64',
                $_POST['course'], 'string-base64',
                $_POST['tel'], 'string-base64',
                $_POST['date'], 'string-base64'
            );
            
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
            $verifyData->checkDataTypes($_POST['username'], 'string-base64', $_POST['password'], 'string-base64');

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
    if (isset($_POST['getAllTeachers'])) {
        if ($verifyData->issetDataPost(array('username', 'password'))) {
            $verifyData->checkDataTypes($_POST['username'], 'string-base64', $_POST['password'], 'string-base64');
            
            $idDirective = $directives->getDirectiveId($_POST['username'], $_POST['password']);
            if (is_object($idDirective)) {
                echo json_encode($idDirective);
                return;
            }
            $get = $student->getTeachers($idDirective);
            echo json_encode($get);
            return;
        }
        echo json_encode($responses->errorTypical);
        return;
    }
    if (isset($_POST['editStudent'])) {
        if ($verifyData->issetDataPost(array('username', 'password', 'id')) && $verifyData->issetPosts(array('name', 'dni', 'course', 'tel', 'date', 'email'))) {
            $verifyData->checkDataTypes($_POST['username'], 'string-base64', $_POST['password'], 'string-base64', $_POST['id'], 'number');
            
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
            $verifyData->checkDataTypes($_POST['username'], 'string-base64', $_POST['password'], 'string-base64', $_POST['id'], 'number');
            
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
            $verifyData->checkDataTypes(
                $_POST['username'], 'string-base64',
                $_POST['password'], 'string-base64',
                $_POST['curse'], 'string-base64',
                $_POST['date'], 'string-base64',
                $_POST['hour'], 'string-base64'
            );
            
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
            $verifyData->checkDataTypes($_POST['username'], 'string-base64', $_POST['password'], 'string-base64');
            
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
    if (isset($_POST['getAllGroupTeachersAssist'])) {
        if ($verifyData->issetDataPost(array('username', 'password'))) {
            $verifyData->checkDataTypes($_POST['username'], 'string-base64', $_POST['password'], 'string-base64');
            
            $idDirective = $directives->getDirectiveId($_POST['username'], $_POST['password']);
            if (is_object($idDirective)) {
                echo json_encode($idDirective);
                return;
            }
            $get = $assist->getAllTeachers($idDirective);
            echo json_encode($get);
            return;
        }
        echo json_encode($responses->errorTypical);
        return;
    }
    if (isset($_POST['getGroupAssist'])) {
        if ($verifyData->issetDataPost(array('username', 'password', 'id'))) {
            $verifyData->checkDataTypes(
                $_POST['username'], 'string-base64',
                $_POST['password'], 'string-base64',
                $_POST['id'], 'number'
            );
            
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
            $verifyData->checkDataTypes(
                $_POST['username'], 'string-base64',
                $_POST['password'], 'string-base64',
                $_POST['idGroup'], 'number',
                $_POST['datas'], 'string-base64'
            );
            
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
            $verifyData->checkDataTypes(
                $_POST['username'], 'string-base64',
                $_POST['password'], 'string-base64',
                $_POST['idGroup'], 'number'
            );
            
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
            $verifyData->checkDataTypes(
                $_POST['username'], 'string-base64',
                $_POST['password'], 'string-base64',
                $_POST['idStudent'], 'number'
            );
            
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
            $verifyData->checkDataTypes(
                $_POST['username'], 'string-base64',
                $_POST['password'], 'string-base64',
                $_POST['idGroup'], 'number',
                $_POST['note'], 'string-base64'
            );
            
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
            $verifyData->checkDataTypes(
                $_POST['username'], 'string-base64',
                $_POST['password'], 'string-base64',
                $_POST['idGroup'], 'number'
            );
            
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
            $verifyData->checkDataTypes(
                $_POST['username'], 'string-base64',
                $_POST['password'], 'string-base64',
                $_POST['idGroup'], 'number'
            );
            
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
            $verifyData->checkDataTypes($_POST['username'], 'string-base64', $_POST['password'], 'string-base64');
            
            $open = $directives->open($_POST['username'], $_POST['password']);
            echo json_encode($open);
            return;
        }
        echo json_encode($responses->errorTypical);
        return;
    }
    if (isset($_POST['getAllDirectives'])) {
        if ($verifyData->issetDataPost(array('username', 'password'))) {
            $verifyData->checkDataTypes($_POST['username'], 'string-base64', $_POST['password'], 'string-base64');
            
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
            $verifyData->checkDataTypes(
                $_POST['username'], 'string-base64',
                $_POST['password'], 'string-base64',
                $_POST['idEdit'], 'number'
            );
            
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
            $verifyData->checkDataTypes(
                $_POST['username'], 'string-base64',
                $_POST['password'], 'string-base64',
                $_POST['name'], 'string-base64',
                $_POST['position'], 'string-base64',
                $_POST['dni'], 'string-base64',
                $_POST['newUsername'], 'string-base64',
                $_POST['newPassword'], 'string-base64',
                $_POST['permission'], 'number'
            );
            
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
            $verifyData->checkDataTypes(
                $_POST['username'], 'string-base64',
                $_POST['password'], 'string-base64',
                $_POST['idDirective'], 'number'
            );
            
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

    // Preferences
    if (isset($_POST['getPreferencesDirective'])) {
        if ($verifyData->issetDataPost(array('username', 'password'))) {
            $verifyData->checkDataTypes($_POST['username'], 'string-base64', $_POST['password'], 'string-base64');
            
            $idDirective = $directives->getDirectiveId($_POST['username'], $_POST['password']);
            if (is_object($idDirective)) {
                echo json_encode($idDirective);
                return;
            }
            $get = $preferences->get($idDirective);
            echo json_encode($get);
            return;
        }
        echo json_encode($responses->errorTypical);
        return;
    }
    if (isset($_POST['updatePreferencesDirective'])) {
        if ($verifyData->issetDataPost(array('username', 'password', 'date', 'datas'))) {
            $verifyData->checkDataTypes(
                $_POST['username'], 'string-base64',
                $_POST['password'], 'string-base64',
                $_POST['date'], 'string-base64',
                $_POST['datas'], 'string-base64'
            );
            
            $idDirective = $directives->getDirectiveId($_POST['username'], $_POST['password']);
            if (is_object($idDirective)) {
                echo json_encode($idDirective);
                return;
            }
            $update = $preferences->updateNow(
                $idDirective,
                $_POST['date'],
                $_POST['datas']
            );
            echo json_encode($update);
            return;
        }
        echo json_encode($responses->errorTypical);
        return;
    }

    // Schedule
    if (isset($_POST['addSchedule'])) {
        if ($verifyData->issetDataPost(array('username', 'password', 'curse', 'data'))) {
            $verifyData->checkDataTypes(
                $_POST['username'], 'string-base64',
                $_POST['password'], 'string-base64',
                $_POST['curse'], 'string-base64',
                $_POST['data'], 'string-base64'
            );
            
            $idDirective = $directives->getDirectiveId($_POST['username'], $_POST['password']);
            if (is_object($idDirective)) {
                echo json_encode($idDirective);
                return;
            }
            $set = $schedule->create(
                $idDirective,
                $_POST['curse'],
                $_POST['data']
            );
            echo json_encode($set);
            return;
        }
        echo json_encode($responses->errorTypical);
        return;
    }
    if (isset($_POST['editSchedule'])) {
        if ($verifyData->issetDataPost(array('username', 'password', 'idSchedule', 'curse', 'data'))) {
            $verifyData->checkDataTypes(
                $_POST['username'], 'string-base64',
                $_POST['password'], 'string-base64',
                $_POST['idSchedule'], 'number',
                $_POST['curse'], 'string-base64',
                $_POST['data'], 'string-base64'
            );
            
            $idDirective = $directives->getDirectiveId($_POST['username'], $_POST['password']);
            if (is_object($idDirective)) {
                echo json_encode($idDirective);
                return;
            }
            $modify = $schedule->modify(
                $idDirective,
                $_POST['idSchedule'],
                $_POST['data']
            );
            echo json_encode($modify);
            return;
        }
        echo json_encode($responses->errorTypical);
        return;
    }
    if (isset($_POST['getAllSchedules'])) {
        if ($verifyData->issetDataPost(array('username', 'password'))) {
            $verifyData->checkDataTypes($_POST['username'], 'string-base64', $_POST['password'], 'string-base64');
            
            $idDirective = $directives->getDirectiveId($_POST['username'], $_POST['password']);
            if (is_object($idDirective)) {
                echo json_encode($idDirective);
                return;
            }
            $get = $schedule->getAll($idDirective);
            echo json_encode($get);
            return;
        }
        echo json_encode($responses->errorTypical);
        return;
    }
    if (isset($_POST['deleteSchedule'])) {
        if ($verifyData->issetDataPost(array('username', 'password', 'idSchedule'))) {
            $verifyData->checkDataTypes(
                $_POST['username'], 'string-base64',
                $_POST['password'], 'string-base64',
                $_POST['idSchedule'], 'number'
            );
            
            $idDirective = $directives->getDirectiveId($_POST['username'], $_POST['password']);
            if (is_object($idDirective)) {
                echo json_encode($idDirective);
                return;
            }
            $delete = $schedule->delete($idDirective, $_POST['idSchedule']);
            echo json_encode($delete);
            return;
        }
        echo json_encode($responses->errorTypical);
        return;
    }

    // Matters
    if (isset($_POST['addMatter'])) {
        if ($verifyData->issetDataPost(array('username', 'password', 'idTeacher', 'name'))) {
            $verifyData->checkDataTypes(
                $_POST['username'], 'string-base64',
                $_POST['password'], 'string-base64',
                $_POST['idTeacher'], 'number',
                $_POST['name'], 'string-base64'
            );
            
            $idDirective = $directives->getDirectiveId($_POST['username'], $_POST['password']);
            if (is_object($idDirective)) {
                echo json_encode($idDirective);
                return;
            }
            $set = $matters->create(
                $idDirective,
                $_POST['idTeacher'],
                $_POST['name']
            );
            echo json_encode($set);
            return;
        }
        echo json_encode($responses->errorTypical);
        return;
    }
    if (isset($_POST['getAllMatters'])) {
        if ($verifyData->issetDataPost(array('username', 'password'))) {
            $verifyData->checkDataTypes($_POST['username'], 'string-base64', $_POST['password'], 'string-base64');
            
            $idDirective = $directives->getDirectiveId($_POST['username'], $_POST['password']);
            if (is_object($idDirective)) {
                echo json_encode($idDirective);
                return;
            }
            $get = $matters->getAll($idDirective);
            echo json_encode($get);
            return;
        }
        echo json_encode($responses->errorTypical);
        return;
    }
    if (isset($_POST['deleteMatter'])) {
        if ($verifyData->issetDataPost(array('username', 'password', 'idMatter'))) {
            $verifyData->checkDataTypes(
                $_POST['username'], 'string-base64',
                $_POST['password'], 'string-base64',
                $_POST['idMatter'], 'number'
            );
            
            $idDirective = $directives->getDirectiveId($_POST['username'], $_POST['password']);
            if (is_object($idDirective)) {
                echo json_encode($idDirective);
                return;
            }
            $delete = $matters->delete($idDirective, $_POST['idMatter']);
            echo json_encode($delete);
            return;
        }
        echo json_encode($responses->errorTypical);
        return;
    }
    if (isset($_POST['editMatter'])) {
        if ($verifyData->issetDataPost(array('username', 'password', 'idMatter', 'idTeacher', 'name'))) {
            $verifyData->checkDataTypes(
                $_POST['username'], 'string-base64',
                $_POST['password'], 'string-base64',
                $_POST['idMatter'], 'number',
                $_POST['idTeacher'], 'number',
                $_POST['name'], 'string-base64'
            );
            
            $idDirective = $directives->getDirectiveId($_POST['username'], $_POST['password']);
            if (is_object($idDirective)) {
                echo json_encode($idDirective);
                return;
            }
            $modify = $matters->modify(
                $idDirective,
                $_POST['idMatter'],
                $_POST['idTeacher'],
                $_POST['name']
            );
            echo json_encode($modify);
            return;
        }
        echo json_encode($responses->errorTypical);
        return;
    }
    

    // Records
    if (isset($_POST['getRecords'])) {
        if ($verifyData->issetDataPost(array('username', 'password'))) {
            $verifyData->checkDataTypes($_POST['username'], 'string-base64', $_POST['password'], 'string-base64');
            
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

    // Curses Groups
    if (isset($_POST['getAllCursesGroups'])) {
        if ($verifyData->issetDataPost(array('username', 'password'))) {
            $verifyData->checkDataTypes($_POST['username'], 'string-base64', $_POST['password'], 'string-base64');
            
            $idDirective = $directives->getDirectiveId($_POST['username'], $_POST['password']);
            if (is_object($idDirective)) {
                echo json_encode($idDirective);
                return;
            }
            $get = $cursesGroup->getAll($idDirective);
            echo json_encode($get);
            return;
        }
        echo json_encode($responses->errorTypical);
        return;
    }
    if (isset($_POST['editCurseGroup'])) {
        if ($verifyData->issetDataPost(array('username', 'password', 'idEdit')) && $verifyData->issetPosts(array('curse', 'group', 'students'))) {
            $verifyData->checkDataTypes(
                $_POST['username'], 'string-base64',
                $_POST['password'], 'string-base64',
                $_POST['idEdit'], 'number'
            );
            
            $idDirective = $directives->getDirectiveId($_POST['username'], $_POST['password']);
            if (is_object($idDirective)) {
                echo json_encode($idDirective);
                return;
            }
            $edit = $cursesGroup->modify(
                $idDirective,
                $_POST['idEdit'],
                $_POST['curse'],
                $_POST['group'],
                $_POST['students']
            );
            echo json_encode($edit);
            return;
        }
        echo json_encode($responses->errorTypical);
        return;
    }
    if (isset($_POST['addCurseGroup'])) {
        if ($verifyData->issetDataPost(array('username', 'password', 'curse', 'group', 'students'))) {
            $verifyData->checkDataTypes(
                $_POST['username'], 'string-base64',
                $_POST['password'], 'string-base64',
                $_POST['curse'], 'string-base64',
                $_POST['group'], 'string-base64',
                $_POST['students'], 'string-base64'
            );
            
            $idDirective = $directives->getDirectiveId($_POST['username'], $_POST['password']);
            if (is_object($idDirective)) {
                echo json_encode($idDirective);
                return;
            }
            $set = $cursesGroup->create(
                $idDirective,
                $_POST['curse'],
                $_POST['group'],
                $_POST['students']
            );
            echo json_encode($set);
            return;
        }
        echo json_encode($responses->errorTypical);
        return;
    }
    if (isset($_POST['deleteCurseGroup'])) {
        if ($verifyData->issetDataPost(array('username', 'password', 'idGroup'))) {
            $verifyData->checkDataTypes(
                $_POST['username'], 'string-base64',
                $_POST['password'], 'string-base64',
                $_POST['idGroup'], 'number'
            );
            
            $idDirective = $directives->getDirectiveId($_POST['username'], $_POST['password']);
            if (is_object($idDirective)) {
                echo json_encode($idDirective);
                return;
            }
            $delete = $cursesGroup->delete($idDirective, $_POST['idGroup']);
            echo json_encode($delete);
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
    if (isset($_POST['setConsoleListAssist'])) {
        if ($verifyData->issetDataPost(array('keyAccess', 'dateAccess', 'data'))) {
            $verify = $console->verify($_POST['keyAccess'], $_POST['dateAccess']);
            if (is_object($verify)) {
                echo json_encode($verify);
                return;
            }
            $set = $assist->setDataFromConsole($_POST['data']);
            echo json_encode($set);
            return;
        }
        echo json_encode($responses->errorTypical);
        return;
    }

    echo json_encode($responses->errorNoPost);
?>