<?php
          $env = parse_ini_file(__DIR__ . '/../.env');

          $connect = mysqli_connect(
              $env['DB_HOST'],
              $env['DB_USER'],
              $env['DB_PASS'],
              $env['DB_NAME'],
              $env['DB_PORT']
          );
          if(!$connect){
            echo 'Error Code: ' . mysqli_connect_errno(); // error code in number form
            echo 'Error Message: ' . mysqli_connect_error(); // error message
            exit; // exit the program if there is an error 
            
          }
          
        ?>