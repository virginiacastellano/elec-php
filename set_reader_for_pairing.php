<?php
if (!isset($_GET["employees_id"])) {
    exit("employees_id is required");
}
include_once "functions.php";
$employeesId = $_GET["employees_id"];
setReaderForemployeesPairing($employeesId);
