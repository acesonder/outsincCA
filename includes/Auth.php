<?php
/**
 * OUTSINC - Authentication Class
 * Handles user authentication, registration, and password management
 */

class Auth {
    private $db;
    private $conn;

    public function __construct() {
        $this->db = new Database();
        $this->conn = $this->db->getConnection();
    }

    /**
     * Generate unique user ID from first name, last name, and DOB
     */
    public function generateUserId($firstName, $lastName, $dob) {
        // Format: FIRSTLASTMMDDYY (e.g., MICBRO050684)
        $first = strtoupper(substr($firstName, 0, 3));
        $last = strtoupper(substr($lastName, 0, 3));
        $date = date('mdY', strtotime($dob));
        $userId = $first . $last . substr($date, 0, 4) . substr($date, 6, 2);
        
        // Check if exists, add counter if needed
        $counter = 1;
        $originalUserId = $userId;
        while ($this->userIdExists($userId)) {
            $userId = $originalUserId . $counter;
            $counter++;
        }
        
        return $userId;
    }

    /**
     * Check if user ID exists
     */
    private function userIdExists($userId) {
        $query = "SELECT id FROM users WHERE user_id = :user_id LIMIT 1";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':user_id', $userId);
        $stmt->execute();
        return $stmt->rowCount() > 0;
    }

    /**
     * Register a new user
     */
    public function register($data) {
        try {
            // Start transaction
            $this->conn->beginTransaction();

            // Generate user ID
            $userId = $this->generateUserId($data['first_name'], $data['last_name'], $data['date_of_birth']);
            
            // Hash password
            $passwordHash = password_hash($data['password'], PASSWORD_DEFAULT);
            
            // Hash security answer
            $securityAnswerHash = password_hash(strtolower(trim($data['security_answer'])), PASSWORD_DEFAULT);

            // Insert user
            $query = "INSERT INTO users 
                      (user_id, username, password_hash, role, first_name, last_name, 
                       date_of_birth, security_question, security_answer_hash, status) 
                      VALUES 
                      (:user_id, :username, :password_hash, :role, :first_name, :last_name, 
                       :date_of_birth, :security_question, :security_answer_hash, 'active')";
            
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':user_id', $userId);
            $stmt->bindParam(':username', $userId); // Username is same as user_id
            $stmt->bindParam(':password_hash', $passwordHash);
            $stmt->bindParam(':role', $data['role']);
            $stmt->bindParam(':first_name', $data['first_name']);
            $stmt->bindParam(':last_name', $data['last_name']);
            $stmt->bindParam(':date_of_birth', $data['date_of_birth']);
            $stmt->bindParam(':security_question', $data['security_question']);
            $stmt->bindParam(':security_answer_hash', $securityAnswerHash);
            $stmt->execute();
            
            $newUserId = $this->conn->lastInsertId();

            // Create user preferences
            $prefQuery = "INSERT INTO user_preferences (user_id) VALUES (:user_id)";
            $prefStmt = $this->conn->prepare($prefQuery);
            $prefStmt->bindParam(':user_id', $newUserId);
            $prefStmt->execute();

            // If client role, create client profile
            if ($data['role'] === ROLE_CLIENT) {
                $clientQuery = "INSERT INTO clients (user_id) VALUES (:user_id)";
                $clientStmt = $this->conn->prepare($clientQuery);
                $clientStmt->bindParam(':user_id', $newUserId);
                $clientStmt->execute();
            }

            // Log action
            $this->logAction($newUserId, 'user_registration', 'users', $newUserId);

            $this->conn->commit();

            return [
                'success' => true,
                'user_id' => $userId,
                'message' => 'Registration successful'
            ];
        } catch (Exception $e) {
            $this->conn->rollBack();
            return [
                'success' => false,
                'message' => 'Registration failed: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Login user
     */
    public function login($username, $password) {
        try {
            // Check if account is locked
            $lockQuery = "SELECT id, locked_until, failed_login_attempts 
                         FROM users 
                         WHERE username = :username 
                         LIMIT 1";
            $lockStmt = $this->conn->prepare($lockQuery);
            $lockStmt->bindParam(':username', $username);
            $lockStmt->execute();
            
            if ($lockStmt->rowCount() > 0) {
                $lockData = $lockStmt->fetch(PDO::FETCH_ASSOC);
                
                // Check if locked
                if ($lockData['locked_until'] && strtotime($lockData['locked_until']) > time()) {
                    $remainingTime = ceil((strtotime($lockData['locked_until']) - time()) / 60);
                    return [
                        'success' => false,
                        'message' => "Account locked. Try again in {$remainingTime} minutes."
                    ];
                }
            }

            // Get user
            $query = "SELECT u.*, up.* 
                     FROM users u 
                     LEFT JOIN user_preferences up ON u.id = up.user_id 
                     WHERE u.username = :username 
                     AND u.status = 'active' 
                     LIMIT 1";
            
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':username', $username);
            $stmt->execute();

            if ($stmt->rowCount() === 0) {
                return [
                    'success' => false,
                    'message' => 'Invalid username or password'
                ];
            }

            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            // Verify password
            if (!password_verify($password, $user['password_hash'])) {
                // Increment failed attempts
                $this->incrementFailedAttempts($user['id']);
                
                return [
                    'success' => false,
                    'message' => 'Invalid username or password'
                ];
            }

            // Reset failed attempts
            $this->resetFailedAttempts($user['id']);

            // Update last login
            $updateQuery = "UPDATE users SET last_login = NOW() WHERE id = :id";
            $updateStmt = $this->conn->prepare($updateQuery);
            $updateStmt->bindParam(':id', $user['id']);
            $updateStmt->execute();

            // Set session
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_id_string'] = $user['user_id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['role'] = $user['role'];
            $_SESSION['first_name'] = $user['first_name'];
            $_SESSION['last_name'] = $user['last_name'];
            $_SESSION['theme_mode'] = $user['theme_mode'] ?? 'light';
            $_SESSION['login_time'] = time();

            // Log action
            $this->logAction($user['id'], 'login', 'users', $user['id']);

            return [
                'success' => true,
                'user' => [
                    'id' => $user['id'],
                    'user_id' => $user['user_id'],
                    'username' => $user['username'],
                    'role' => $user['role'],
                    'first_name' => $user['first_name'],
                    'last_name' => $user['last_name']
                ],
                'message' => 'Login successful'
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Login failed: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Increment failed login attempts
     */
    private function incrementFailedAttempts($userId) {
        $query = "UPDATE users 
                 SET failed_login_attempts = failed_login_attempts + 1,
                     locked_until = CASE 
                         WHEN failed_login_attempts + 1 >= :max_attempts 
                         THEN DATE_ADD(NOW(), INTERVAL :lockout_time SECOND)
                         ELSE NULL 
                     END
                 WHERE id = :id";
        
        $stmt = $this->conn->prepare($query);
        $maxAttempts = MAX_LOGIN_ATTEMPTS;
        $lockoutTime = LOCKOUT_TIME;
        $stmt->bindParam(':max_attempts', $maxAttempts);
        $stmt->bindParam(':lockout_time', $lockoutTime);
        $stmt->bindParam(':id', $userId);
        $stmt->execute();
    }

    /**
     * Reset failed login attempts
     */
    private function resetFailedAttempts($userId) {
        $query = "UPDATE users 
                 SET failed_login_attempts = 0, locked_until = NULL 
                 WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $userId);
        $stmt->execute();
    }

    /**
     * Password recovery - verify user and get security question
     */
    public function getSecurityQuestion($firstName, $lastName, $dob) {
        try {
            $query = "SELECT id, user_id, username, security_question 
                     FROM users 
                     WHERE first_name = :first_name 
                     AND last_name = :last_name 
                     AND date_of_birth = :dob 
                     LIMIT 1";
            
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':first_name', $firstName);
            $stmt->bindParam(':last_name', $lastName);
            $stmt->bindParam(':dob', $dob);
            $stmt->execute();

            if ($stmt->rowCount() === 0) {
                return [
                    'success' => false,
                    'message' => 'No account found with those details'
                ];
            }

            $user = $stmt->fetch(PDO::FETCH_ASSOC);
            
            return [
                'success' => true,
                'user_id' => $user['id'],
                'username' => $user['username'],
                'security_question' => $user['security_question']
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Error: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Verify security answer and reset password
     */
    public function resetPassword($userId, $securityAnswer, $newPassword) {
        try {
            // Get user's security answer hash
            $query = "SELECT security_answer_hash FROM users WHERE id = :id LIMIT 1";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':id', $userId);
            $stmt->execute();

            if ($stmt->rowCount() === 0) {
                return [
                    'success' => false,
                    'message' => 'User not found'
                ];
            }

            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            // Verify security answer
            if (!password_verify(strtolower(trim($securityAnswer)), $user['security_answer_hash'])) {
                return [
                    'success' => false,
                    'message' => 'Incorrect security answer'
                ];
            }

            // Update password
            $newPasswordHash = password_hash($newPassword, PASSWORD_DEFAULT);
            $updateQuery = "UPDATE users SET password_hash = :password_hash WHERE id = :id";
            $updateStmt = $this->conn->prepare($updateQuery);
            $updateStmt->bindParam(':password_hash', $newPasswordHash);
            $updateStmt->bindParam(':id', $userId);
            $updateStmt->execute();

            // Log action
            $this->logAction($userId, 'password_reset', 'users', $userId);

            return [
                'success' => true,
                'message' => 'Password reset successful'
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Error: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Logout user
     */
    public function logout() {
        if (isset($_SESSION['user_id'])) {
            $this->logAction($_SESSION['user_id'], 'logout', 'users', $_SESSION['user_id']);
        }
        
        session_destroy();
        return ['success' => true];
    }

    /**
     * Check if user is logged in
     */
    public function isLoggedIn() {
        return isset($_SESSION['user_id']) && isset($_SESSION['login_time']);
    }

    /**
     * Check if user has specific role
     */
    public function hasRole($role) {
        return isset($_SESSION['role']) && $_SESSION['role'] === $role;
    }

    /**
     * Check if user has any of the specified roles
     */
    public function hasAnyRole($roles) {
        return isset($_SESSION['role']) && in_array($_SESSION['role'], $roles);
    }

    /**
     * Log action to audit log
     */
    private function logAction($userId, $action, $tableName = null, $recordId = null, $oldValues = null, $newValues = null) {
        try {
            $query = "INSERT INTO audit_log 
                     (user_id, action, table_name, record_id, old_values, new_values, ip_address, user_agent) 
                     VALUES 
                     (:user_id, :action, :table_name, :record_id, :old_values, :new_values, :ip_address, :user_agent)";
            
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':user_id', $userId);
            $stmt->bindParam(':action', $action);
            $stmt->bindParam(':table_name', $tableName);
            $stmt->bindParam(':record_id', $recordId);
            
            $oldValuesJson = $oldValues ? json_encode($oldValues) : null;
            $newValuesJson = $newValues ? json_encode($newValues) : null;
            $stmt->bindParam(':old_values', $oldValuesJson);
            $stmt->bindParam(':new_values', $newValuesJson);
            
            $ipAddress = $_SERVER['REMOTE_ADDR'] ?? null;
            $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? null;
            $stmt->bindParam(':ip_address', $ipAddress);
            $stmt->bindParam(':user_agent', $userAgent);
            
            $stmt->execute();
        } catch (Exception $e) {
            // Fail silently for logging errors
            error_log("Audit log error: " . $e->getMessage());
        }
    }
}
?>
