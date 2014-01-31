<?php
/**
 * A CUSer class that represents a User
 *
 */
class CUSer {

  /**
   * Properties
   *
   */
   protected $db; // Database object
   
   
   
   public function __construct($dbCredentials) {

    $this->db = new CDatabase($dbCredentials);
   }
   
  /*
   * Validates if user is loged in
   *
   */
  public function IsAuthenticated() {
   
     // Check if user is authenticated.
     $user = isset($_SESSION['user']);
     
     if($user) {
       return TRUE;
     }

     return FALSE; 
  }
  
  public function Login($user, $password) {
  
    $loginResult = FALSE;
    $userName = strip_tags($user);
    $userPassword = strip_tags($password);
    
    $sql = "SELECT acronym, name FROM RM_User WHERE acronym = ? AND password = md5(concat(?, salt))";
    $params = array($userName, $userPassword);
    
    $res = $this->db->ExecuteSelectQueryAndFetchAll($sql, $params);
    
    if(isset($res[0])) { 
      $_SESSION['user'] = $res[0]; 
      $loginResult = TRUE;
    } 
    
    return $loginResult;      
  }
  
  public function Logout() {
    unset($_SESSION['user']);
    
    // Maybe remove
     // Unset all of the session variables.
		 $_SESSION = array();
  
  // If it's desired to kill the session, also delete the session cookie.
  // Note: This will destroy the session, and not just the session data!
  if (ini_get("session.use_cookies")) {
      $params = session_get_cookie_params();
      setcookie(session_name(), '', time() - 42000,
          $params["path"], $params["domain"],
          $params["secure"], $params["httponly"]
      );
  }
  
  // Finally, destroy the session.
  session_destroy();
    
  }

  
  public function GetAcronym() {
    // Check if user is authenticated.
    $user = isset($_SESSION['user']);

    if($user) {
      $userName = $_SESSION['user']->acronym;
      
      return $userName;
    } 
  }
  
  public function GetName() {
    // Check if user is authenticated.
    $user = isset($_SESSION['user']);

    if($user) {
      $userName = $_SESSION['user']->name;
      
      return $userName;
    } 
  }
  
  /*
  
  CUser::Login($user, $password) loggar in användaren om användare och lösenord stämmer.
CUser::Logout() loggar ut användaren.
CUser::IsAuthenticated() returnerar true om användaren är inloggad, annars false.
CUser::GetAcronym() returnera användarens akronym.
CUser::GetName() returnera användarens namn.
  
   */
  



}