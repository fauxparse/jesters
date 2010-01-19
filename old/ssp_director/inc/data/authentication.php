<?php

function authenticate_user($username, $password){
  global $utbl, $db;
  // Test the username and password parameters
  if (!isset($username) || !isset($password))
    return false;

  $username = make_safe($username);
  $password = make_safe($password);

  // Formulate the SQL find the user
  $q = "SELECT id FROM $utbl WHERE usr = '{$username}'
            AND pwd = '{$password}'";

  // Execute the query
  $results = $db->get_results($q);

  // exactly one row? then we have found the user
  if (count($results) != 1):
    return false;
  else:
  	return true;
  endif;
}

// Connects to a session and checks that the user has
// authenticated and that the remote IP address matches
// the address used to create the session.
function session_authenticate(){

  // Check if the user hasn't logged in
  if (!isset($_SESSION["login"]))
  {
    // The request does not identify a session
    $_SESSION["message"] = "You are not authorized to access the URL 
                            {$_SERVER["REQUEST_URI"]}";
    $_SESSION['status'] = 'Please Login...';
	if (!empty($_GET['p']))
		$_SESSION['redirect'] = $_SERVER['QUERY_STRING'];
    header("Location: index.php?p=login");
    exit;
  }
}

?>