<?php
function randomDigits( $length ) {
	$digits = '';

	for($i = 0; $i < $length; $i++) {
		$digits .= mt_rand(0, 9);
	}

	return $digits;
}

function generateNewAccountNumber() {
	$accountNumber = randomDigits(10);
	
	// make sure account is unique
	while ( checkAccountExists( $accountNumber )) {
		$accountNumber = randomDigits(10);
	}
	
	return $accountNumber;
}

function checkAccountExists( $accountNumber ) {
	try {
		$connection = new PDO( DB_NAME, DB_USER, DB_PASS );
		$connection->setAttribute( PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION );
		
		// Obtain user_id associated with given account
		$sql = "SELECT user_id FROM accounts WHERE account_number = :account_number";
		$stmt = $connection->prepare( $sql );
		$stmt->bindValue( "account_number", $accountNumber, PDO::PARAM_STR );
		$stmt->execute();
		
		$result = $stmt->fetch();
		
		// Make sure Source Account belongs to this user
		if ( $stmt->rowCount() > 0 ) {
			return true;
		} else {
			return false;
		}
	} catch (PDOException $e) {
		echo "<br />Connect Error: ". $e->getMessage();
	}
}
?>