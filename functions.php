
<?php
if (!defined("RFID_STATUS_FILE")) {
    define("RFID_STATUS_FILE", "rfid_status");
}
if (!defined("RFID_STATUS_READING")) {
    define("RFID_STATUS_READING", "r");
}
if (!defined("RFID_STATUS_PAIRING")) {
    define("RFID_STATUS_PAIRING", "p");
}
if (!defined("PAIRING_employees_ID_FILE")) {
    define("PAIRING_employees_ID_FILE", "pairing_employees_id_file");
}

function getemployeeWithRfid()
{
    $query = "SELECT employees_id, rfid_serial FROM employees_rfid";
    $db = getDatabase();
    $statement = $db->query($query);
    return $statement->fetchAll();
}

function onRfidSerialRead($rfidSerial)
{
    if (getReaderStatus() === RFID_STATUS_PAIRING) {
        pairemployeesWithRfid($rfidSerial, getPairingemployeesId());
        setReaderStatus(RFID_STATUS_READING);
    } else {
        $employees = getemployeesByRfidSerial($rfidSerial);
        if ($employees) {
            saveemployeesAttendance($employees->id);
        }
    }
}
function deleteemployeesAttendanceByIdAndDate($employeesId, $date)
{

    $query = "DELETE FROM employees_attendance where employees_id = ? and date = ?";
    $db = getDatabase();
    $statement = $db->prepare($query);
    return $statement->execute([$employeesId, $date]);
}

function saveemployeesAttendance($employeesId)
{
    $date = date("Y-m-d");
    deleteemployeesAttendanceByIdAndDate($date, $employeesId);
    $status = "presence";
    $query = "INSERT INTO employees_attendance(employees_id, date, status) VALUES (?, ?, ?)";
    $db = getDatabase();
    $statement = $db->prepare($query);
    return $statement->execute([$employeesId, $date, $status]);
}

function setReaderForemployeesPairing($employeesId)
{
    setReaderStatus(RFID_STATUS_PAIRING);
    setPairingemployeesId($employeesId);
}

function setPairingemployeesId($employeesId)
{
    file_put_contents(PAIRING_employees_ID_FILE, $employeesId);
}

function getPairingemployeesId()
{
    return file_get_contents(PAIRING_employees_ID_FILE);
}

function pairemployeesWithRfid($rfidSerial, $employeesId)
{
    removeRfidFromemployees($rfidSerial);
    $query = "INSERT INTO employees_rfid(employees_id, rfid_serial) VALUES (?, ?)";
    $db = getDatabase();
    $statement = $db->prepare($query);
    return $statement->execute([$employeesId, $rfidSerial]);
}

function removeRfidFromemployees($rfidSerial)
{
    $query = "DELETE FROM employees_rfid WHERE rfid_serial = ?";
    $db = getDatabase();
    $statement = $db->prepare($query);
    return $statement->execute([$rfidSerial]);
}

function getemployeesByRfidSerial($rfidSerial)
{
    $query = "SELECT e.id, e.name FROM employees e INNER JOIN employees_rfid
    ON employees_rfid.employees_id = e.id
    WHERE employees_rfid.rfid_serial = ?";

    $db = getDatabase();
    $statement = $db->prepare($query);
    $statement->execute([$rfidSerial]);
    return $statement->fetchObject();
}
function getemployeesRfidById($employeesId)
{
    $query = "SELECT rfid_serial FROM employees_rfid WHERE employees_id = ?";
    $db = getDatabase();
    $statement = $db->prepare($query);
    $statement->execute([$employeesId]);
    return $statement->fetchObject();
}

function getReaderStatus()
{
    return file_get_contents(RFID_STATUS_FILE);
}

function setReaderStatus($newStatus)
{
    if (!in_array($newStatus, [RFID_STATUS_PAIRING, RFID_STATUS_READING])) {
        return;
    }

    file_put_contents(RFID_STATUS_FILE, $newStatus);
}

function getemployeesWithAttendanceCount($start, $end)
{
    $query = "select employees.name, 
sum(case when status = 'presence' then 1 else 0 end) as presence_count,
sum(case when status = 'absence' then 1 else 0 end) as absence_count 
 from employees_attendance
 inner join employees on employees.id = employees_attendance.employees_id
 where date >= ? and date <= ?
 group by employees_id, name;";
    $db = getDatabase();
    $statement = $db->prepare($query);
    $statement->execute([$start, $end]);
    return $statement->fetchAll();
}

function saveAttendanceData($date, $employees)
{
    deleteAttendanceDataByDate($date);
    $db = getDatabase();
    $db->beginTransaction();
    $statement = $db->prepare("INSERT INTO employees_attendance(employees_id, date, status) VALUES (?, ?, ?)");
    foreach ($employees as $employees) {
        $statement->execute([$employees->id, $date, $employees->status]);
    }
    $db->commit();
    return true;
}

function deleteAttendanceDataByDate($date)
{
    $db = getDatabase();
    $statement = $db->prepare("DELETE FROM employees_attendance WHERE date = ?");
    return $statement->execute([$date]);
}
function getAttendanceDataByDate($date)
{
    $db = getDatabase();
    $statement = $db->prepare("SELECT employees_id, status FROM employees_attendance WHERE date = ?");
    $statement->execute([$date]);
    return $statement->fetchAll();
}


function deleteemployees($id)
{
    $db = getDatabase();
    $statement = $db->prepare("DELETE FROM employees WHERE id = ?");
    return $statement->execute([$id]);
}

function updateemployees($name, $id)
{
    $db = getDatabase();
    $statement = $db->prepare("UPDATE employees SET name = ? WHERE id = ?");
    return $statement->execute([$name, $id]);
}
function getemployeesById($id)
{
    $db = getDatabase();
    $statement = $db->prepare("SELECT id, name FROM employees WHERE id = ?");
    $statement->execute([$id]);
    return $statement->fetchObject();
}

function saveemployees($name)
{
    $db = getDatabase();
    $statement = $db->prepare("INSERT INTO employees(name) VALUES (?)");
    return $statement->execute([$name]);
}

function getemployee()
{
    $db = getDatabase();
    $statement = $db->query("SELECT id, name FROM employees");
    return $statement->fetchAll();
}

function getVarFromEnvironmentVariables($key)
{
    if (defined("_ENV_CACHE")) {
        $vars = _ENV_CACHE;
    } else {
        $file = "env.php";
        if (!file_exists($file)) {
            throw new Exception("The environment file ($file) does not exists. Please create it");
        }
        $vars = parse_ini_file($file);
        define("_ENV_CACHE", $vars);
    }
    if (isset($vars[$key])) {
        return $vars[$key];
    } else {
        throw new Exception("The specified key (" . $key . ") does not exist in the environment file");
    }
}

function getDatabase()
{
    $password = getVarFromEnvironmentVariables("MYSQL_PASSWORD");
    $user = getVarFromEnvironmentVariables("MYSQL_USER");
    $dbName = getVarFromEnvironmentVariables("MYSQL_DATABASE_NAME");
    $database = new PDO('mysql:host=localhost;dbname=' . $dbName, $user, $password);
    $database->query("set names utf8;");
    $database->setAttribute(PDO::ATTR_EMULATE_PREPARES, FALSE);
    $database->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $database->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_OBJ);
    return $database;
}
