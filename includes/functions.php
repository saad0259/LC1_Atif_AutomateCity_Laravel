<?php 

include("db-info.php");
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

$errors   = array(); 


function sayhelo(){
    echo "Hello from fucntion.php";
}


// $con=mysqli_connect($serverName, $dBUsername, $dBPassword, $dBName); #parameters(hostname, username, password, database_name)
// if ($con->connect_error) {
//     array_push($errors, "Error: Database connection failed"); 
//     die("Connection failed: " . $con->connect_error);
//   }


$conn = new PDO("mysql:host=$serverName;dbname=$dBName", $dBUsername, $dBPassword);
$conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);






if ($_SERVER['REQUEST_METHOD'] == 'POST'){

    if(!empty($_POST['action']) && $_POST['action'] == 'listItem') {
        itemList();
    }
    if(!empty($_POST['action']) && $_POST['action'] == 'addItem') {
        addItem();
    }
    if(!empty($_POST['action']) && $_POST['action'] == 'getItem') {
        getItem();
    }
    if(!empty($_POST['action']) && $_POST['action'] == 'updateItem') {
        updateItem();
    }
    if(!empty($_POST['action']) && $_POST['action'] == 'itemDelete') {
        deleteItem();
    }
        

}



if (session_status() == PHP_SESSION_NONE) {
    session_start();
}


function check_login(){
    
  //  echo 1;die;
    if (session_status() == PHP_SESSION_NONE) {
        session_start();
    }
   // var_dump($_SESSION);die;
    if (!isset($_SESSION['user_id']) && empty($_SESSION['user_id']))
        {//	echo 1;die;
        
            $host = $_SERVER['HTTP_HOST'];
            $uri  = rtrim(dirname($_SERVER['PHP_SELF']), '/\\');
            $extra="./login.php";
            header("Location: http://$host$uri/$extra?session=unauthorized");
            exit();
        }
}

function check_admin(){

    if (session_status() == PHP_SESSION_NONE) {
        session_start();
    }
    // var_dump($_SESSION);die;
    if (!isset($_SESSION['user_id']) && empty($_SESSION['user_id']) && !isset($_SESSION['is_admin'])  && empty($_SESSION['is_admin']))
        {//	echo 1;die;
        
            
                $host = $_SERVER['HTTP_HOST'];
                $uri  = rtrim(dirname($_SERVER['PHP_SELF']), '/\\');
                $extra="./login.php";
                header("Location: http://$host$uri/$extra?session=unauthorized");
                exit();
            
        }

        else{
            if(!$_SESSION['is_admin']==2)
            {
                $host = $_SERVER['HTTP_HOST'];
                $uri  = rtrim(dirname($_SERVER['PHP_SELF']), '/\\');
                $extra="./index.php";
                header("Location: http://$host$uri/$extra?session=unauthorized");
                exit();
            }
        }
}


function check_mod(){

    if (session_status() == PHP_SESSION_NONE) {
        session_start();
    }
    // var_dump($_SESSION);die;
    if (!isset($_SESSION['user_id']) && empty($_SESSION['user_id']) && !isset($_SESSION['is_admin'])  && empty($_SESSION['is_admin']))
        {//	echo 1;die;
        
            
                $host = $_SERVER['HTTP_HOST'];
                $uri  = rtrim(dirname($_SERVER['PHP_SELF']), '/\\');
                $extra="./login.php";
                header("Location: http://$host$uri/$extra?session=unauthorized");
                exit();
            
        }

        else{
            if(!$_SESSION['is_admin']==1 || !$_SESSION['is_admin']==2)
            {
                $host = $_SERVER['HTTP_HOST'];
                $uri  = rtrim(dirname($_SERVER['PHP_SELF']), '/\\');
                $extra="./index.php";
                header("Location: http://$host$uri/$extra?session=unauthorized");
                exit();
            }
        }
}
  


/////////////////////////////////////////// CUSTOM FUNCTIONS ##########################################


//May not work always
function stopRR(){ // Stop-Request-Reload: Stop POST request from reloading
    echo'<script>
            if ( window.history.replaceState ) {
                window.history.replaceState( null, null, window.location.href );
            }
        </script>';
}


	
function insertRecord($table,$data){
	
    global $conn, $errors;

    if (count($errors) == 0){

        try {
            
            // prepare sql and bind parameters
            $qry= "INSERT INTO ".$table." (" . implode(',' , array_keys($data)) . ") VALUES (";
           
            foreach($data as $key => $value)
            {   
                $qry.="?,";
            }
            $qry=rtrim($qry, ',');
            $qry.=")";
           

            $stmt = $conn->prepare($qry);
           
            $c=1;
            foreach($data as $key => $value)
            {   
                
                if($stmt->bindValue($c, $value))
                {
                    $c++;
                }
                
                

            }
           
            $stmt->execute();
            
            
          
            echo "New records created successfully";
          } catch(PDOException $e) {
			array_push($errors, "Error: ".$e->getMessage()); 

          }
          $conn = null;






    }



}


function insertRecord2($table, $data){

    global $con;
    $sql = "INSERT INTO ".$table." (" . implode(',' , array_keys($data)) . ") VALUES";

    $sql .= "('" . implode("','" , array_values($data)) . "')";
    $resp = mysqli_query($con, $sql);
    if(!$resp )
    {
        return mysqli_error($con);
    }
    return $resp;

}


function updatedb($table, $name, $val, $condition, $c_val){


    try {
        global $conn;
      
        $sql = "UPDATE `".$table."` SET ".$name."='".$val."' WHERE ".$condition."=".$c_val;
      
  
        // Prepare statement
        $stmt = $conn->prepare($sql);
        
      
        // execute the query
        $stmt->execute();
      
        // echo a message to say the UPDATE succeeded
        // echo $stmt->rowCount() . " records UPDATED successfully";
        return true;
    } catch(PDOException $e) {
        // echo $sql . "<br>" . $e->getMessage();
        return false;
      }
}


function updatedb2($table, $name, $val, $condition, $c_val)
{
    global $con;
    $sql = "UPDATE `".$table."` SET ".$name."='".$val."' WHERE ".$condition."=".$c_val;
           
    $stmt = mysqli_stmt_init($con);
    $dbresponse=mysqli_stmt_prepare($stmt, $sql);
    mysqli_stmt_execute($stmt);
   
    
        if (!$dbresponse)
        {
          
           return false;
        }
        else
        {
            
           return true;

        }
}




function upload_file($filename,$newfilename, $dir,  $file_type,$max_filesize, $max_height, $max_width, $must_have, $unique){
    $file_size_allowed= $max_filesize*1000000;
    $file_uploaded_check=false;
    $file_exist_check=true;
 
    $ext_check=true;
    $size_check=true;
  
    
    if (! file_exists($_FILES[$filename]["tmp_name"]))
    {

        if($must_have)
        return "No File selected";
        else 
        return 1;
    }
    else{


        
        if($file_type=='pdf')
        {
            //$uploaddir = '../uploads/files/';
            $uploaddir=isset($dir)?$dir:'../uploads/files/';
            $allowed_file_extension = array(
                "pdf",
                "PDF",
                "docx"
                
                
            );
        }
        elseif($file_type=='image')
        {
            $fileinfo = @getimagesize($_FILES[$filename]["tmp_name"]);
            
            
            
            $uploaddir=isset($dir)?$dir:'../uploads/images/';
            $allowed_file_extension = array(
                "png",
                "PNG",
                "jpg",
                "JPG",
                "jpeg",
                "JPEG"
            );

            if ($width > $max_width || $height > $max_height){
                return "file should be atmost ".$max_width."x".$max_height;

            }



        }
        else{
            return 'Not a valid Filetype';
        }


        $file_extension = pathinfo($_FILES[$filename]["name"], PATHINFO_EXTENSION);
        
        if (! in_array($file_extension, $allowed_file_extension))
        {
            return "Extension not identified : ".$file_extension;
            
        }
        
        else{
            

            if(($_FILES[$filename]["size"] > $file_size_allowed)){
                return "file size should be less than ".$file_size_allowed." bytes";
                
            }

            
        }
        



    }
    




    if($size_check && $ext_check && $file_exist_check)
    {

        
        if(file_exists($uploaddir.$_FILES[$filename]['name']))
        {
            if($unique)
            return "This File Already Exists";
            else 
            return 1;
            
        }
        else{

            $uploadfile = $uploaddir . basename($_FILES[$filename]['name']);

            //echo "<p>";

            
          

            
            
            if (move_uploaded_file($_FILES[$filename]["tmp_name"], $uploaddir . $newfilename)) {
            return 1;
            
            }
            else {
                
            return "File Upload failed";
            }


           /*  echo "</p>";
            echo '<pre>';
            echo 'Here is some more debugging info:';
            print_r($_FILES);
             print "</pre>";*/

        }

    

            
   

    }

    
}


function getDataByProp($table,$name,$val){
    

    try {
        global $conn, $errors;
        $sql = "SELECT * FROM `".$table."` WHERE ".$name."=" . $val;
        $stmt = $conn->prepare($sql);
        $stmt->execute();
      
        // set the resulting array to associative
        $result = $stmt->setFetchMode(PDO::FETCH_ASSOC);

        $data=$stmt->fetch();
        return $data;

      } catch(PDOException $e) {
        array_push($errors, "Error: ".$e->getMessage()); 
        return false;
      }
      $conn = null;

}

function getDataByProp2($table,$name,$val){
    global $con;
    $query = "SELECT * FROM `".$table."` WHERE ".$name."=" . $val;
    
    $response = mysqli_query($con, $query);

    if(!$response){                                   
        //echo mysqli_error($con);
        
    }
    else{

        if(mysqli_num_rows($response)>0)
        {
            return mysqli_fetch_array($response);
        }
        else{
            return false;
        }
        

        
    }
  
}




function getDataById2($id, $table){
    global $con;
    $query = "SELECT * FROM ".$table." WHERE user_id=" . $id." LIMIT 1";
    $response = mysqli_query($con, $query);

    if(!$response){                                   
        //echo mysqli_error($con);
        
    }
    else{

        if(mysqli_num_rows($response)>0)
        {
           return mysqli_fetch_array($response);
        }
        else{
            return "user not Found";
        }
        

        
    }
  
}





function strip_bad_chars( $input ){
    $output = preg_replace( "/[^a-zA-Z0-9_-]/", "", $input);
    return $output;
}

function e($val){
    global $con;
    return mysqli_real_escape_string($con, trim($val));
}


/////////////////////////////////////////// DataTable FUNCTIONS: Functions related to DataTables ##########################################



function itemList(){
    global $conn;
    $dbTable="services";
    $sqlQuery = "SELECT * FROM `".$dbTable."` WHERE deleted_at IS NULL ";
    if(!empty($_POST["search"]["value"])){
        $sqlQuery .= 'AND (id LIKE "%'.$_POST["search"]["value"].'%" ';
        $sqlQuery .= ' OR title LIKE "%'.$_POST["search"]["value"].'%" ';			
        $sqlQuery .= ' OR description LIKE "%'.$_POST["search"]["value"].'%" ';
        // $sqlQuery .= ' OR address LIKE "%'.$_POST["search"]["value"].'%" ';
        $sqlQuery .= ' OR date LIKE "%'.$_POST["search"]["value"].'%")';			
    }
    
    
    if(!empty($_POST["order"])){
        $sqlQuery .= 'ORDER BY '.$_POST['order']['0']['column'].' '.$_POST['order']['0']['dir'].' ';
    } else {
        $sqlQuery .= 'ORDER BY id DESC ';
    }
    if($_POST["length"] != -1){
        $sqlQuery .= 'LIMIT ' . $_POST['start'] . ', ' . $_POST['length'];
    }	

    $result = $conn->prepare($sqlQuery);
    // print_r($result);
    $result->execute();

    
    $sqlQuery1 = "SELECT * FROM `".$dbTable."`  where deleted_at IS NULL";

    $result1 = $conn->prepare($sqlQuery1);
    $result1->execute();


    $numRows = count($result1->fetchAll());
    
    $itemData = array();

    $somedata = $result->setFetchMode(PDO::FETCH_ASSOC);
    foreach(new RecursiveArrayIterator($result->fetchAll()) as $k=>$item) {
        
        $dataRows = array();			
        $dataRows[] = $item['id'];
        $dataRows[] = ucfirst($item['title']);
        $dataRows[] = $item['description'];		
        $dataRows[] = $item['date'];	
        $dataRows[] = $item['purchases'];
        $dataRows[] = $item['price'];					
        $dataRows[] = '<button type="button" name="update" id="'.$item["id"].'" class="btn btn-warning btn-sm update">Update</button>';
        $dataRows[] = '<button type="button" name="delete" id="'.$item["id"].'" class="btn btn-danger btn-sm delete" >Delete</button>';
        $itemData[] = $dataRows;
        
    }

    $output = array(
        "draw"				=>	intval($_POST["draw"]),
        "recordsTotal"  	=>  $numRows,
        "recordsFiltered" 	=> 	$numRows,
        "data"    			=> 	$itemData
    );
    echo json_encode($output);
}
function getItem(){
    $dbTable="services";

    if($_POST["id"]) {
        
        $row=getDataByProp($dbTable, 'id', $_POST["id"]);

        
        echo json_encode($row);
    }
}
function updateItem(){
    global $conn;
    $dbTable="services";
    
    try {
        if($_POST['id']) {
            $updateQuery = "UPDATE ".$dbTable." 
            SET title = '".$_POST["title"]."', description = '".$_POST["description"]."', date = '".$_POST["date"]."', purchases = '".$_POST["purchases"]."' , price = '".$_POST["price"]."'
            WHERE id ='".$_POST["id"]."'";

            $result = $conn->prepare($updateQuery);
            $result->execute();
            


            // $isUpdated = mysqli_query($conn, $updateQuery);		
        }	
    } catch (PDOException $e) {
        array_push($errors, "Error: ".$e->getMessage()); 

    }

    
}
function addItem(){
    global $conn;
    $dbTable="services";
    unset($_POST['action']);
    insertRecord($dbTable, $_POST);		

}
function deleteItem(){
    global $conn;
    $dbTable="services";



    if($_POST["id"]) {

        updatedb($dbTable, 'deleted_at', date("Y-m-d H:i:s"), 'id', $_POST['id']);
        // $sqlDelete = "
        // 	DELETE FROM ".$dbTable."
        // 	WHERE id = '".$_POST["id"]."'";		
        // mysqli_query($conn, $sqlDelete);
        
        
    }
}



/////////////////////////////////////////// UNIQUE FUNCTIONS: Functions unique to this website ##########################################








?>

                        
