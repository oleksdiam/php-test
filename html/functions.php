<?php
    if(isset($_POST["Import"]))
    {

      // connect to the database
      $db = 'csvdata';
      $host = '127.0.0.1';
      $user = 'web';
      $pass = file_get_contents('/opt/tp.txt') or die("Unable to open file!");
      $conn = mysqli_connect($host, $user, $pass, $db);
      $filename=$_FILES["file"]["tmp_name"];
      if($_FILES["file"]["size"] > 0)
      {
        $file = fopen($filename, "r");
        $sql = "CREATE TABLE IF NOT EXISTS upload LIKE employee";
        $result = mysqli_query($conn, $sql);
        $sql = "TRUNCATE TABLE upload";
        $result = mysqli_query($conn, $sql);
        while (($getData = fgetcsv($file, 10000, ",")) !== FALSE)
        {
            $sql = "INSERT INTO upload
            VALUES ($getData[0],'$getData[1]','$getData[2]','$getData[3]','$getData[4]','$getData[5]')";  //#21
            echo $sql;
            $result = mysqli_query($conn, $sql);
            $res = var_dump($result);
                
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

      echo "<script type=\"text/javascript\">
        alert(\"CSV File has been successfully Imported.\");
        window.location = \"index.php\"
        </script>";

      $affected = array('added' => $addedRows, 'updated' => $updatedRows, 'deleted' => $deletedRows);
      return $affected;
  }
  function output(data)
  {
      echo "<div class='table-responsive'><table id='Action executed' class='table table-striped table-bordered'>
      <thead><tr><th>Action</th>
                <th>Affected rows</th>
                </tr></thead><tbody>";
                foreach ($data as $item => $item_count) {
                  echo "<tr><td>" . $item."</td>
                        <td>" . $item_count . "</td></tr>";  
                  }
                  // foreach ($affected as $item => $item_count) {
      // echo "<tr><td>" . $item."</td>
      //       <td>" . $item_count . "</td></tr>";  
      // }

  }
?>
