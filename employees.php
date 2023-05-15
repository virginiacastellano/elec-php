
<?php
include_once "header.php";
include_once "nav.php";
include_once "functions.php";
$employee = getemployee();
?>
<div class="row">
<div class="container-fluid">
                <form class="d-flex" action="">
                    <input class="form-control me-2" type="search" placeholder="Buscar " name="busqueda">
                    <button class="btn btn-outline-info" type="submit" name="enviar">Buscar</button>
                </form>
                </div>

    <div class="col-12">
        <h1 class="text-center">Elecciones 2023</h1>
    </div>
    <div class="col-12">
        <a href="employee_add.php" class="btn btn-primary mb-2">Agregar un nuevo voto <i class="fa fa-plus"></i></a>
    </div>
    <div class="col-12">
        <div class="table-responsive" >
            <table class="table">
                <thead>
                    <tr>
                        <th>Id</th>
                        <th>N° de orden, Nombre, Escuela, Mesa</th>
                        <th>Edit</th>
                        <th>Delete</th>
                    </tr>
                </thead>
                <?php
                // Obtener la conexión a la base de datos
                $conexion=mysqli_connect("localhost","root","","basededatos");


                // Inicializar la variable $where
                $where = "";

                // Verificar si se ha enviado una búsqueda
                if(isset($_GET['enviar'])){
                $busqueda = $_GET['busqueda'];

                // Construir la consulta SQL con la búsqueda
                $where = " WHERE employees.name LIKE '%" . $busqueda . "%'";


                // Ejecutar la consulta SQL
                $query = "SELECT * FROM employees" . $where;
                $result = mysqli_query($conexion, $query);

                if (!$result) {
                    echo "Error al ejecutar la consulta SQL: " . mysqli_error($conexion);
                    exit;
                }
                // Guardar los resultados en un array de objetos
                $employee = [];
                while ($row = mysqli_fetch_object($result)) {
                    $employee[] = $row;
                }
                } else {
                // Si no se ha enviado una búsqueda, obtener todos los empleados
                $employee = getemployee();
                }
                ?>


                <tbody>
                    <?php foreach ($employee as $employees) { ?>
                        <tr>
                            <td>
                                <?php echo $employees->id ?>
                            </td>
                            <td>
                                <?php echo $employees->name ?>
                            </td>
                            <td>
                                <a class="btn btn-warning" href="employee_edit.php?id=<?php echo $employees->id ?>">
                                Edit <i class="fa fa-edit"></i>
                            </a>
                            </td>
                            <td>
                                <a class="btn btn-danger" href="employee_delete.php?id=<?php echo $employees->id ?>">
                                Delete <i class="fa fa-trash"></i>
                            </a>
                            </td>
                        </tr>
                    <?php } ?>

                </tbody>
            </table>
        </div>
    </div>
</div>
<script src="../js/buscador.js"></script>
<?php
include_once "footer.php";
