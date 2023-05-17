
<?php
if (!isset($_POST["name"])) {
    exit("No data provided");
}
include_once "functions.php";
$name = $_POST["name"];
saveemployees($name);
header("Location: employees.php");
