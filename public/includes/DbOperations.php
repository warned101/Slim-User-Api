<?php

    class DbOperations{
        private $con;

        function __construct(){
            require_once dirname(__FILE__) . '/DBConnect.php';

            $db = new DBConnect;

            $this->con = $db->connect();
        }

        public function createUser($email,$pass, $name, $school){
           if(!$this->isEmailExist($email)){
            $stmt =  $this->con->prepare("INSERT INTO users(email, pass, name, school)VALUES(?,?,?,?)");
            $stmt->bind_param("ssss", $email, $pass, $name, $school);
            if($stmt->execute()){
                return USER_CREATED;
            }else{
                return USER_FAILURE;
            }
           }
           return USER_EXISTS;
        }

        public function userLogin($email, $pass){
            if($this->isEmailExist($email)){
                $hashed_password = $this->getUsersPasswordByEmail($email);
                if(password_verify($pass, $hashed_password)){
                    return USER_AUTHENTICATED;
                }else{
                    return USER_PASSWORD_DO_NOT_MATCH;
                }
            }else{
                return USER_NOT_FOUND;
            }
        }

        private function getUsersPasswordByEmail($email){
            $stmt = $this->con->prepare("SELECT pass from users where email = ?");
            $stmt->bind_param("s", $email);
            $stmt->execute();
            $stmt->bind_result( $pass);
            $stmt->fetch();
            return $pass;
        }

        public function getAllUsers(){
            $stmt = $this->con->prepare("SELECT id, email, name, school FROM users;");
            $stmt->execute(); 
            $stmt->bind_result($id, $email, $name, $school);
            $users = array();        
            while($stmt->fetch()){ 
                $user = array(); 
                $user['id'] = $id; 
                $user['email']=$email; 
                $user['name'] = $name; 
                $user['school'] = $school; 
                array_push($users, $user);
            }             
            return $users; 
          }

          public function getUserByEmail($email){
            $stmt = $this->con->prepare("SELECT id, email, name, school FROM users WHERE email = ?");
            $stmt->bind_param("s", $email);
            $stmt->execute(); 
            $stmt->bind_result($id, $email, $name, $school);
            $stmt->fetch(); 
            $user = array(); 
            $user['id'] = $id; 
            $user['email']=$email; 
            $user['name'] = $name; 
            $user['school'] = $school; 
            return $user; 
        }

        public function updateUser($email, $name, $school, $id){
            $stmt = $this->con->prepare("UPDATE users SET email=?, name=?, school=? where id = ?");
            $stmt->bind_param("sssi", $email, $name, $school, $id);
            if ($stmt->execute())
                return true;
            return false;
            
        }

        public function updatePassword($currentpassword, $newpassword, $email){
            $hashed_password = $this->getUsersPasswordByEmail($email);

            if(password_verify($currentpassword, $hashed_password)){
                $hash_password = password_hash($newpassword, PASSWORD_DEFAULT);

                $stmt = $this->con->prepare("UPDATE users SET pass=? where email = ?");
                $stmt->bind_param("ss", $hash_password, $email);
                if ($stmt->execute())
                return PASSWORD_CHANGED;
            return PASSWORD_NOT_CHANGED;
            }
            else{
                return PASSWORD_DO_NOT_MATCH;
            }
        }

        public function deleteuser($id){
            $stmt = $this->con->prepare("DELETE from users where id = ?");
            $stmt->bind_param("i", $id);
            if($stmt->execute())
                return true;
            return false;

        }

        private function isEmailExist($email){
            $stmt = $this->con->prepare("SELECT id from users where email = ?");
            $stmt->bind_param("s", $email);
            $stmt->execute();
            $stmt->store_result();
            return $stmt->num_rows > 0;
        }
    }
