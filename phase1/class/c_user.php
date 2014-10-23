<?php
include_once(__DIR__.'/../include/conf.php');
include_once(__DIR__.'/../include/helper.php');
include_once(__DIR__.'/../include/TransferException.php');

class User {
	public $email = null;
	public $password = null;
	public $salt = null;
	public $id = null;
	public $isEmployee = null;
	public $isActive = null;
	
	
	
	public function getAccountNumberID( $accountNumber ) {
		try {
			$connection = new PDO( DB_NAME, DB_USER, DB_PASS );
			$connection->setAttribute( PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION );
			
			$sql = "SELECT id FROM accounts WHERE account_number = :account_number";
			$stmt = $connection->prepare ( $sql );
			$stmt->bindValue( "account_number", $accountNumber, PDO::PARAM_STR );
			$stmt->execute();
			$result = $stmt->fetch();
			
			if ($stmt->rowCount() > 0) {
				return $result['id'];
			} else {
				return -1;
			}
		} catch ( PDOException $e ) {
			echo "<br />Connect Error: ". $e->getMessage();
			return -1;
		}	
	}
	
	
	public function getTransactions( $accountNumber ) {
		try {
			$connection = new PDO( DB_NAME, DB_USER, DB_PASS );
			$connection->setAttribute( PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION );
				
			$sql = "SELECT * FROM transactions WHERE source = :account_number OR destination = :account_number";
			$stmt = $connection->prepare ( $sql );
			$stmt->bindValue( "account_number", $accountNumber, PDO::PARAM_STR );
			$stmt->execute();
			$result = $stmt->fetchAll();
				
			if ($stmt->rowCount() > 0) {
				return $result;
			} else {
				return array();
			}
		} catch ( PDOException $e ) {
			echo "<br />Connect Error: ". $e->getMessage();
			return array();
		}
	}
	
	
	public function generateTANList( $accountNumber ) {
		/* Generate 100 random, unique transaction Codes of length 15 digits for this user */
		$maxNumTries = 100; // maximum number of rerolls in case a code is not unique
		
		try {
			$connection = new PDO( DB_NAME, DB_USER, DB_PASS );
			$connection->setAttribute( PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION );
			
			// Obtain ID of given accountNumber
			$accountID = $this->getAccountNumberID ( $accountNumber );
			
			if ($accountID < 0) {
				return false;
			}
			
			for ( $codeNumber = 0; $codeNumber < 100; $codeNumber++) {
				$tries = 0;
				$unique = false;
				while ( !$unique && ( $tries <= $maxNumTries ) ) {
					$tries++;
					
					// Generate random code of length 15
					$code = randomDigits(15);
					
					// Make sure this code is unique
					$sql = "SELECT code FROM trans_codes WHERE code = :code";
					$stmt = $connection->prepare ( $sql );
					$stmt->bindValue ( "code", $code, PDO::PARAM_STR );
					$stmt->execute();
					
					// If code was not found set unique to true
					if ( $stmt->rowCount() == 0 ) {
						$unique = true;
					}
				}
				
				if ($tries >= $maxNumTries) {
					echo "Failed to Generate TAN List, too many tries!";
					return false;
				}
				
				// Code is unique, insert it into db
				$sql = "INSERT INTO trans_codes (account_id, code_number, code, is_used) VALUES (:account_id, :code_number, :code, :is_used)";
				$stmt = $connection->prepare ( $sql );
				$stmt->bindValue ( "account_id", $accountID, PDO::PARAM_STR );
				$stmt->bindValue ( "code_number", $codeNumber, PDO::PARAM_STR );
				$stmt->bindValue ( "code", $code, PDO::PARAM_STR );
				$stmt->bindValue ( "is_used", false, PDO::PARAM_STR );
				
				$stmt->execute();
			}
			
			// Sanity Check
			$sql = "SELECT * FROM trans_codes WHERE account_id = :account_id";
			$stmt = $connection->prepare ( $sql );
			$stmt->bindValue ( "account_id", $this->id, PDO::PARAM_STR );
			$stmt->execute();
			
			if ( $stmt->rowCount() >= 100 ) {
				return true;
			} else {
				return false;
			}
		} catch ( PDOException $e ) {
			echo "<br />Connect Error: ". $e->getMessage();
			return false;
		}
	}
	
	
	public function commitTransaction( $source, $destination, $amount, $code ) {
		$is_approved = true;
		if ( $amount >= 10000 ) {
			$is_approved = false;
		}
		
		try {
			$connection = new PDO( DB_NAME, DB_USER, DB_PASS );
			$connection->setAttribute( PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION );
	
			$sql = "UPDATE trans_codes SET is_used = :is_used WHERE code = :code";
			$stmt = $connection->prepare( $sql );
			$stmt->bindValue( "code", $code, PDO::PARAM_STR );
			$stmt->bindValue( "is_used", true, PDO::PARAM_STR);
			$stmt->execute();
			
			if ( $stmt->rowCount() > 0 ) {
				if ( $this->updateNextTan( $source ) ) {
					$sql = "INSERT INTO transactions (source, destination, amount, code, is_approved) VALUES (:source, :destination, :amount, :code, :is_approved)";
					$stmt = $connection->prepare( $sql );
					$stmt->bindValue( "source", $source, PDO::PARAM_STR );
					$stmt->bindValue( "destination", $destination, PDO::PARAM_STR );
					$stmt->bindValue( "amount", $amount, PDO::PARAM_STR );
					$stmt->bindValue( "code", $code, PDO::PARAM_STR );
					$stmt->bindValue( "is_approved", $is_approved, PDO::PARAM_STR );
					$stmt->execute();
					
					if ( $stmt->rowCount() > 0) {
						return true;
					} else {
						throw new TransferException("Failed to insert transaction.");
					}
				}
			} else { throw new TransferException("TAN was already used.");}
		} catch ( PDOException $e ) {
			echo "<br />Connect Error: ". $e->getMessage();
			return false;
		}
	}
	
	
	public function verifyTAN( $accountNumber, $tan, $tanNumber ) {
		$accountID = $this->getAccountNumberID( $accountNumber );
		try {
			$connection = new PDO( DB_NAME, DB_USER, DB_PASS );
			$connection->setAttribute( PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION );
		
			$sql = "SELECT code FROM trans_codes WHERE account_id = :account_id AND code_number = :code_number";
			$stmt = $connection->prepare ( $sql );
			$stmt->bindValue( "account_id", $accountID, PDO::PARAM_STR );
			$stmt->bindValue( "code_number", $tanNumber, PDO::PARAM_STR );
			$stmt->execute();
			
			$result = $stmt->fetch();
			
			if ( $tan == $result['code'] ) {
				return true;
			} else {
				return false;
			}
		} catch ( PDOException $e ) {
			echo "<br />Connect Error: ". $e->getMessage();
			return false;
		}
	}
	
	
	public function selectRandomTAN( $accountNumber ) {
		$accountID = $this->getAccountNumberID ( $accountNumber );
		try {
			$connection = new PDO( DB_NAME, DB_USER, DB_PASS );
			$connection->setAttribute( PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION );
				
			$sql = "SELECT code_number, code FROM trans_codes WHERE account_id = :account_id AND is_used = false";
			$stmt = $connection->prepare ( $sql );
			$stmt->bindValue( "account_id", $accountID, PDO::PARAM_STR );
			$stmt->execute();
			$result = $stmt->fetchAll();
				
			$connection = null;
			
			if ($stmt->rowCount() > 0) {
				$index = rand ( 0, ($stmt->rowCount() - 1) );
				$tanNumber = $result[$index]['code_number'];
				$tan = $result[$index]['code'];
				//echo "<br />TAN NUMBER: " .$tanNumber;
				//echo "<br />TAN: " .$tan;
				return $tanNumber;
			} else {
				return -1;
			}
		} catch ( PDOException $e ) {
			echo "<br />Connect Error: ". $e->getMessage();
			return -1;
		}
	}
	
	
	public function transferCredits( $data = array(), $source ) {
		$destination = "";
		$amount = 0;

		if ( isset( $data['destination'] ) ) $destination = stripslashes( strip_tags( $data['destination'] ) );
		else throw new TransferException("Destination invalid.");
		if ( isset( $data['amount'] ) ) $amount = stripslashes( strip_tags( $data['amount'] ) );
		else throw new TransferException("Amount Invalid.");
		if ( isset( $data['tan'] ) ) $tan = stripslashes( strip_tags( $data['tan'] ) );
		else throw new TransferException("TAN invalid.");
		if ( $destination == $source )
			throw new TransferException("Destination account must be different from source account.");
		if ( $amount <= 0 )
			throw new TransferException("Amount must be positive.");;
		
		try {
			$connection = new PDO( DB_NAME, DB_USER, DB_PASS );
			$connection->setAttribute( PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION );
			
			// Obtain user_id associated with given source account
			$sql = "SELECT user_id FROM accounts WHERE account_number = :account_number";
			$stmt = $connection->prepare( $sql );
			$stmt->bindValue( "account_number", $source, PDO::PARAM_STR );
			$stmt->execute();
			
			$result = $stmt->fetch();
			$connection = null;
			
			// Was user_id found for given source account?
			if ( $stmt->rowCount() > 0 ) {
				// Make sure source account belongs to this user
				if ( $result['user_id'] != $this->id ) {
					throw new TransferException("User mismatch detected. Please Log out and Sign back in.");
				} else {
					// source account belongs to user, make sure destination account exists
					if (!checkAccountExists( $destination )) {
						throw new TransferException("The destination account doesn't exist.");
					} else {
						$currentTANNumber = $this->getNextTan( $source );
						if ( $currentTANNumber < 0 )
							throw new TransferException("Unable to obtain TAN number.");
						if ( $this->verifyTAN( $source, $tan, $currentTANNumber ) ) {
							return $this->commitTransaction($source, $destination, $amount, $tan);
						} else {
							throw new TransferException("Invalid TAN.");
						}
					}
				}
			} else {
				return false;
			}
		} catch ( PDOException $e ) {
			echo "<br />Connect Error: ". $e->getMessage();
			return false;
		}
	}
	
	
	public function register( $data = array() ) {
		if( isset( $data['email'] ) ) $this->email = stripslashes( strip_tags( $data['email'] ) );
		else return false;
		if( isset( $data['password'] ) ) $this->password = stripslashes( strip_tags( $data['password'] ) );
		else return false;
		
		try{
			$connection = new PDO( DB_NAME, DB_USER, DB_PASS );
			$connection->setAttribute( PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION );
	 
			$sql = "INSERT INTO users (email,passwd,salt,is_employee,is_active) VALUES (:email,:password,:salt,:isEmployee,:isActive)";
			$stmt = $connection->prepare( $sql );
			$stmt->bindValue( "email", $this->email, PDO::PARAM_STR );
			$stmt->bindValue( "password", $this->password, PDO::PARAM_STR );
			$stmt->bindValue( "salt", 'salt', PDO::PARAM_STR );
			$stmt->bindValue( "isEmployee", false, PDO::PARAM_STR );
			$stmt->bindValue( "isActive", true, PDO::PARAM_STR );
			$stmt->execute();
				
			$connection = null;
			
			if ( $stmt->rowCount() > 0 ) {
				$this->getUserDataFromEmail( $this->email );
				$this->addAccount( generateNewAccountNumber() );
				return true;
			} else {
				return false;
			}
			
		} catch ( PDOException $e ) {
			echo "<br />Connect Error: ". $e->getMessage();
			return false;
		}
	}
	
	
	public function getNextTan( $accountNumber ) {
		try {
			$connection = new PDO( DB_NAME, DB_USER, DB_PASS );
			$connection->setAttribute( PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION );
			
			$sql = "SELECT next_tan FROM accounts WHERE account_number = :account_number";
			$stmt = $connection->prepare ( $sql );
			$stmt->bindValue ( "account_number", $accountNumber, PDO::PARAM_STR );
			$stmt->execute();
			$result = $stmt->fetch();
			
			return $result['next_tan'];
		} catch ( PDOException $e ) {
			echo "<br />Connect Error: ". $e->getMessage();
			return -1;
		}
	}
	
	
	public function updateNextTan( $accountNumber ) {
		$randomTANNumber = $this->selectRandomTAN( $accountNumber );

		if ( $randomTANNumber < 0 )
			throw new TransferException("Failed to generate new TAN number (All TANs exhausted?).");
			
		try {
			$connection = new PDO( DB_NAME, DB_USER, DB_PASS );
			$connection->setAttribute( PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION );
			
			$sql = "UPDATE accounts SET next_tan = :next_tan WHERE account_number = :account_number";
			$stmt = $connection->prepare ( $sql );
			$stmt->bindValue ( "next_tan", $randomTANNumber, PDO::PARAM_STR );
			$stmt->bindValue ( "account_number", $accountNumber, PDO::PARAM_STR );
			$stmt->execute();
			
			if ($stmt->rowCount() > 0) {
				return true;
			} else {
				throw new TransferException("Failed to update TAN number.");
			}
		} catch ( PDOException $e ) {
			echo "<br />Connect Error: ". $e->getMessage();
			return false;
		}
	}
	
	
	public function addAccount( $accountNumber ) {
		try{
			$connection = new PDO( DB_NAME, DB_USER, DB_PASS );
			$connection->setAttribute( PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION );
		
			$sql = "INSERT INTO accounts (user_id,account_number,next_tan) VALUES (:user_id,:account_number,:next_tan)";
			$stmt = $connection->prepare( $sql );
			$stmt->bindValue( "user_id", $this->id, PDO::PARAM_STR );
			$stmt->bindValue( "account_number", $accountNumber, PDO::PARAM_STR );
			$stmt->bindValue( "next_tan", rand(0,99));
			$stmt->execute();
		
			$connection = null;
				
			if ( $stmt->rowCount() > 0 ) {
				$this->generateTANList( $accountNumber );
				return true;
			} else {
				return false;
			}
				
		} catch ( PDOException $e ) {
			echo "<br />Connect Error: ". $e->getMessage();
			return false;
		}
	}
	
	
	public function getAccounts () {
		$result = array ();
		try{
			$connection = new PDO( DB_NAME, DB_USER, DB_PASS );
			$sql = "SELECT * FROM accounts WHERE user_id = :id";
		
			$stmt = $connection->prepare( $sql );
			$stmt->bindValue( "id", $this->id, PDO::PARAM_STR );
			$stmt->execute();
		
			$result = $stmt->fetchAll(PDO::FETCH_COLUMN, 2);
			// var_dump($result);
			$connection = null;
			return $result;
		} catch ( PDOException $e ) {
			echo "<br />Connect Error: ". $e->getMessage();
			return array();
		}
	}
	
	
	public function getUserDataFromEmail( $email ) {
		$result = array ();
		try{
			$connection = new PDO( DB_NAME, DB_USER, DB_PASS );
			$sql = "SELECT * FROM users WHERE email = :email LIMIT 1";
		
			$stmt = $connection->prepare( $sql );
			$stmt->bindValue( "email", $email, PDO::PARAM_STR );
			$stmt->execute();
		
			$result = $stmt->fetch();
			
			$this->email = $result['email'];
			$this->password = $result['passwd'];
			$this->salt = $result['salt'];
			$this->isEmployee = $result['is_employee'];
			$this->isActive = $result['is_active'];
			$this->id = $result['id'];
			
			$connection = null;
			return $result;
		} catch ( PDOException $e ) {
			echo "<br />Connect Error: ". $e->getMessage();
			return array();
		}
	}
	
	
	public function checkCredentials( $data = array() ) {
		if( isset( $data['email'] ) ) $this->email = stripslashes( strip_tags( $data['email'] ) );
		else return false;
		if( isset( $data['password'] ) ) $this->password = stripslashes( strip_tags( $data['password'] ) );
		else return false;
		
		$success = false;
		
		try{
			$connection = new PDO( DB_NAME, DB_USER, DB_PASS );
			$sql = "SELECT * FROM users WHERE email = :email AND passwd = :password LIMIT 1";
				
			$stmt = $connection->prepare( $sql );
			$stmt->bindValue( "email", $this->email, PDO::PARAM_STR );
			$stmt->bindValue( "password", $this->password, PDO::PARAM_STR );
			$stmt->execute();
				
			if( $stmt->fetchColumn() ) {
				$success = true;
			}
				
			$connection = null;
			return $success;
		} catch ( PDOException $e ) {
			echo "<br />Connect Error: ". $e->getMessage();
			return $success;
		}
	}
}
?>