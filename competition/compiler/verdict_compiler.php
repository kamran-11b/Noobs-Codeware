<?php

include '../../connection.php'; 
if (!isset($_SESSION["Username"])) {
    header("Location: ../../login.php");
    exit();
}

$cid = $_POST['cid'];
$pid = $_POST['pid'];
$ptit = $_POST['ptit'];
$lang = $_POST['language'];
$code = $_POST['code'];
$fs = 0;

$compilation_command = '';
$execution_command = '';
$code_file = '';
$success = 0;
$verdict = '';

if ($lang == 'C') {
    $code_file = 'noob.c';
    $compilation_command = 'gcc noob.c -o noob 2>comp_msg.txt';
    $execution_command = 'timeout 5s time -v ./noob < input.txt 2>exec_msg.txt >output.txt';
    $binary_file = './noob';
}

else if ($lang == 'C++') {
    $code_file = 'noob.cpp';
    $compilation_command = 'g++ noob.cpp -o noob 2>comp_msg.txt';
    $execution_command = 'timeout 5s time -v ./noob < input.txt 2>exec_msg.txt >output.txt';
    $binary_file = './noob';
}

else if ($lang == 'Java') {
    $code_file = 'noob.java';
    $compilation_command = 'javac noob.java 2>comp_msg.txt';
    $execution_command = 'timeout 5s time -v java noob < input.txt 2>exec_msg.txt >output.txt';
    $binary_file = 'noob.class';
}

$inps = scandir('../../uploads/admin/testcases/'.$pid.'/input');
$outs = scandir('../../uploads/admin/testcases/'.$pid.'/output');

$verdict = '';
$flag = 1;
$output = '';
$time_needed = 0.00; //Seconds
$memory_needed = 0.00; //MegaBytes

file_put_contents($code_file, $code);
$fs = filesize($code_file); //in Bytes
shell_exec($compilation_command);
$comp_msg = file_get_contents('comp_msg.txt');

if (trim($comp_msg)){ //Compilation Error
    $verdict = 'Compilation Error';
    echo '<h5 class = "mb-3 ver-ce"><b>Compilation Error!</b></h5>';
    echo '<div class="text-muted">'.nl2br($comp_msg).'</div>';
    $flag = 0;
}

else {
    for ($i = 2; $i < count($inps); $i++) {
        //put the input and execute the binary file
        file_put_contents('input.txt', file_get_contents('../../uploads/admin/testcases/'.$pid.'/input/'.$inps[$i]));
        shell_exec($execution_command);

        $exec_msg = file('exec_msg.txt');
        $sz = count($exec_msg);

        if ($sz == 0) {
            $verdict = 'TLE On Testcase ' . ($i - 2);
            echo '<h5 class = "mb-3 ver-tle">TLE On Testcase ' . ($i - 2).'</h5>';
            $time_needed = 5.00;
            $flag = 0;
            break;
        }

        else {
            if ($sz > 23) { //Runtime Error
                $verdict = 'Runtime Error On Testcase ' . ($i - 2);
                echo '<h5 class = "mb-3 ver-re">Runtime Error On Testcase ' . ($i - 2) . '</h5>';
                break;
            }

            else {
                $judge_output = trim(file_get_contents('../../uploads/admin/testcases/'.$pid.'/output/'.$outs[$i]));
                $ex_output = trim(file_get_contents('output.txt'));
                if ($ex_output != $judge_output) {
                    $verdict = 'Wrong Answer On Testcase ' . ($i - 2);
                    echo '<h5 class = "mb-3 ver-wa">Wrong Answer On Testcase ' . ($i - 2).'</h5>';
                    $flag = 0;
                    break;
                }
            }

            $exec_msg = array_slice($exec_msg, $sz - 23);
            $time_needed = floatval(substr($exec_msg[4], -6)); //in seconds
            $memory_needed = floatval(substr($exec_msg[9], 36)); //in kB
        }
    }

}
shell_exec('rm '.$binary_file);
file_put_contents('output.txt', 'Hello Noobs!');

if ($flag) {
    $verdict = 'Accepted';
    echo '<h5 class = "mb-3 text-success">Accepted</h5>';
}
$unm = $_SESSION['Username'];

$code = str_replace('\n', '\\\\n' ,$code);
$code = str_replace('\r', '\\\\r' ,$code);
$code = str_replace('\t', '\\\\t' ,$code);
$qry = "INSERT INTO Contest_Submission(Contest_Id, Problem_Title, Username, Source_Code, Verdict, Language, Time, Memory, Source_Size) VALUES('{$cid}', '{$ptit}', '{$unm}', '{$code}', '{$verdict}', '{$lang}', {$time_needed}, {$memory_needed}, {$fs});";
mysqli_query($con, $qry);

?>