<?php
include 'config.php';
if(isset($_POST["Import"]))
    {
    $filename=$_FILES["file"]["tmp_name"];
    if($_FILES["file"]["size"] > 0)
    {
        $sql = "CREATE TABLE IF NOT EXISTS employee(
                uid int, 
                firstName varchar(50),
                lastName varchar(80),
                birthDay date,
                dateChange date,
                description varchar(255),
                PRIMARY KEY(uid)
                )
                ENGINE=InnoDB
                DEFAULT CHARSET=utf8";
        // $conn variable grants access to the established connection
        // to the MySQL database defined in the "config.php" file
        $result = mysqli_query($conn, $sql);

        $file = fopen($filename, "r");
        $sql = "CREATE TABLE IF NOT EXISTS upload LIKE employee";
        $result = mysqli_query($conn, $sql);
        $sql = "TRUNCATE TABLE upload";
        $result = mysqli_query($conn, $sql);
        while (($getData = fgetcsv($file, 10000, ",")) !== FALSE)
        {
            //check whether the 'DATE' fields are empty and substitute the 'NULL' value
            if (empty($getData[3]))
            {
                $getData[3] = 'NULL';
            }
            else
            {
                $getData[3] = '\''.$getData[3].'\'';
            }
            if (empty($getData[4]))
            {
                $getData[4] = 'NULL';
            }
            else
            {
                $getData[4] = '\''.$getData[4].'\'';
            }
            $sql = "INSERT INTO upload
            VALUES ($getData[0],'$getData[1]','$getData[2]',$getData[3],$getData[4],'$getData[5]')";  //#38
            $result = mysqli_query($conn, $sql);
            if(!isset($result))
            {
                echo "<script type=\"text/javascript\">
                    alert(\"Invalid File:Please Upload CSV File.\");
                    window.location = \"index.php\"
                    </script>";    
            }
        }
        fclose($file);  
    }
    // primarly delete data with uid absent in the uploaded file
    $sql = "DELETE FROM employee WHERE `uid` NOT IN (SELECT u.uid FROM upload u)";
    $result = mysqli_query($conn, $sql);
    $deletedRows = mysqli_affected_rows($conn);

    // add new data that not present in the DB
    $sql = "INSERT INTO employee
        SELECT u.* FROM upload u
        LEFT JOIN employee e ON u.uid = e.uid
        WHERE e.uid IS NULL";
    $result = mysqli_query($conn, $sql);
    $addedRows = mysqli_affected_rows($conn);
    
    // update data with changed "dateChange" field      
    $sql = "UPDATE employee e 
        INNER JOIN upload u 
            ON e.uid = u.uid
        SET e.firstName = u.firstName,
            e.lastName = u.lastName,
            e.birthDay = u.birthDay,
            e.dateChange = u.dateChange,
            e.description = u.description
        WHERE e.dateChange != u.dateChange";
    $result = mysqli_query($conn, $sql);
    $updatedRows = mysqli_affected_rows($conn);

    // Finally, drop temporary table
    $sql = "DROP TABLE upload";
    $result = mysqli_query($conn, $sql);

    $affected = array('added' => $addedRows, 'updated' => $updatedRows, 'deleted' => $deletedRows);
    }

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css" crossorigin="anonymous">
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap-theme.min.css" crossorigin="anonymous">
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js" crossorigin="anonymous"></script>
</head>
<body>
    <div id="wrap">
        <div class="container">
            <div class="row">
                <form class="form-horizontal" action="" method="post" name="upload_csv" enctype="multipart/form-data">
                    <fieldset>
                        <legend>Upload data from csv file to MySQL DB</legend>
                        <div class="form-group">
                            <label class="col-md-4 control-label" for="filebutton">Select File</label>
                            <div class="col-md-4">
                                <input type="file" name="file" id="file" accept=".csv" class="input-large">
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="col-md-4 control-label" for="singlebutton">Import data</label>
                            <div class="col-md-4">
                                <button type="submit" id="submit" name="Import" class="btn btn-primary button-loading" data-loading-text="Loading...">Import</button>
                            </div>
                        </div>
                    </fieldset>
                </form>
            </div>
            <div class='table-responsive'>
                <table id='Actions executed' class='table table-striped table-bordered'>
                <thead><tr><th>Action</th>
                <th>Affected rows</th>
                </tr></thead>
                <?php
                    foreach ($affected as $item => $item_count) {
                ?>
                <tbody>
                    <tr>
                        <td><?php  echo $item; ?></td>
                        <td><?php  echo $item_count; ?></td>
                    </tr>
                    <?php
                    }
                    ?>
                </tbody>
                </table>
            </div>
        </div>
    </div>
</body>
</html>
