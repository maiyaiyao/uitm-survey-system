<?php
/**
 * User Model
 * Handles all user-related database operations
 */

class User {
    private $db;
    private $conn;

    public function __construct() {
        // Assuming your Database class is correctly defined elsewhere
        $this->db = new Database(); 
        // Assuming $this->db->getConnection() returns a PDO connection object
        $this->conn = $this->db->getConnection(); 
    }

    /**
     * Create new user
     * Handles both email/password (with hashed password) and Google (with null password) users.
     * @param array $data Must contain 'primary_email', 'full_name'. Can contain 'password', 'google_sub_id', 'email_verified', and other user fields.
     * @return string|false The last inserted user_ID or false on failure.
     */
    public function create($data) {
        // Update SQL to include all necessary fields from the registration logic
        $sql = "INSERT INTO user (
                    primary_email, password, google_sub_id, full_name, 
                    email_verified, department, status, user_organization, 
                    user_position, user_phone_company, user_handphone_no, 
                    created_at, updated_at
                ) 
                VALUES (
                    :primary_email, :password, :google_sub_id, :full_name, 
                    :email_verified, :department, :status, :user_organization, 
                    :user_position, :user_phone_company, :user_handphone_no,
                    NOW(), NOW()
                )";
        
        $stmt = $this->conn->prepare($sql);
        
        try {
            $stmt->execute([
                // The array key and binding parameter are now consistently 'primary_email'
                ':primary_email' => $data['primary_email'], 
                ':password' => $data['password'] ?? null,
                ':google_sub_id' => $data['google_sub_id'] ?? null,
                ':full_name' => $data['full_name'],
                // Set defaults for other user fields if not provided
                ':email_verified' => $data['email_verified'] ?? 0,
                ':department' => $data['department'] ?? null,
                ':status' => $data['status'] ?? 'active',
                ':user_organization' => $data['user_organization'] ?? null,
                ':user_position' => $data['user_position'] ?? null,
                ':user_phone_company' => $data['user_phone_company'] ?? null,
                ':user_handphone_no' => $data['user_handphone_no'] ?? null,
            ]);
            return $this->conn->lastInsertId();
        } catch (PDOException $e) {
            // Log error if necessary
            // error_log("User creation failed: " . $e->getMessage()); 
            return false;
        }
    }

    /**
     * Create a new user account specifically from Google OAuth data and assign default role (ID 2).
     * @param array $data Google user data (primary_email, full_name, google_sub_id)
     * @return string|false The new user_ID or false on failure.
     */
    public function createGoogleUser(array $data) {
        $userData = [
            // Changed key to 'primary_email'
            'primary_email' => $data['email'], 
            'password' => null, 
            'google_sub_id' => $data['google_sub_id'],
            'full_name' => $data['full_name'],
            'email_verified' => 1 // Google verification is assumed trustworthy
        ];

        // 1. Create the user record in the main 'user' table
        $newUserId = $this->create($userData);

        if ($newUserId) {
            // 2. Assign the default 'user' role (Role ID 2)
            $defaultRoleID = 2; 
            $assignedBy = 'Google OAuth System';

            $roleAssigned = $this->assignRole($newUserId, $defaultRoleID, $assignedBy);
            
            if ($roleAssigned) {
                return $newUserId;
            } else {
                // IMPORTANT: If role assignment fails, you might want to remove the user record here.
                return false; 
            }
        }
        return false;
    }

    /**
     * Find user by Google ID or Email, or create a new user and assign default role.
     * This is the core logic for Google sign-in/registration.
     * @param array $googleData Must contain 'google_sub_id', 'email', 'full_name'.
     * @return array|false The user record on success, or false on failure.
     */
    public function findOrCreateByGoogleId(array $googleData) {
        $google_sub_id = $googleData['google_sub_id'];
        $email = $googleData['email'];

        // 1. Check if user exists by Google Sub ID (standard login/returning user)
        $user = $this->findByGoogleId($google_sub_id);

        if ($user) {
            $this->updateLastLogin($user['user_ID']);
            return $user;
        }

        // 2. Check if user exists by Email (account linking scenario)
        // Passes the email variable to findByEmail
        $user = $this->findByEmail($email); 

        if ($user) {
            // Existing user found via email, link the Google ID and log them in
            $this->linkGoogleAccount($user['user_ID'], $google_sub_id);
            $this->updateLastLogin($user['user_ID']);
            // Refetch the user to ensure data like google_sub_id is current
            return $this->findById($user['user_ID']); 
        }

        // 3. New Registration: Create user and assign default 'user' role (ID 2)
        $newUserId = $this->createGoogleUser($googleData);
        
        if ($newUserId) {
            // Retrieve the newly created user record
            return $this->findById($newUserId);
        }

        return false;
    }


    /**
     * Find user by email
     * @param string $primary_email The email address to search for.
     */
    public function findByEmail($primary_email) {
        // Note: GROUP_CONCAT is used to fetch all roles efficiently
        $sql = "SELECT u.*, GROUP_CONCAT(r.role_name) as roles 
                FROM user u 
                LEFT JOIN user_role ur ON u.user_ID = ur.user_ID 
                LEFT JOIN role r ON ur.role_id = r.role_id 
                WHERE u.primary_email = :primary_email 
                GROUP BY u.user_ID";
        
        $stmt = $this->conn->prepare($sql);
        // Changed binding parameter name to :primary_email
        $stmt->execute([':primary_email' => $primary_email]); 
        return $stmt->fetch();
    }


    /**
     * Find user by Google Sub ID
     */
    public function findByGoogleId($google_sub_id) {
        $sql = "SELECT u.*, GROUP_CONCAT(r.role_name) as roles 
                FROM user u 
                LEFT JOIN user_role ur ON u.user_ID = ur.user_ID 
                LEFT JOIN role r ON ur.role_id = r.role_id 
                WHERE u.google_sub_id = :google_sub_id 
                GROUP BY u.user_ID";
        
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([':google_sub_id' => $google_sub_id]);
        return $stmt->fetch();
    }

    /**
     * Find user by ID
     */
    public function findById($user_ID) {
        $sql = "SELECT u.*, GROUP_CONCAT(r.role_name) as roles 
                FROM user u 
                LEFT JOIN user_role ur ON u.user_ID = ur.user_ID 
                LEFT JOIN role r ON ur.role_id = r.role_id 
                WHERE u.user_ID = :user_ID 
                GROUP BY u.user_ID";
        
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([':user_ID' => $user_ID]);
        return $stmt->fetch();
    }

    /**
     * Update last login
     */
    public function updateLastLogin($user_ID) {
        $sql = "UPDATE user SET last_login = NOW() WHERE user_ID = :user_ID";
        $stmt = $this->conn->prepare($sql);
        return $stmt->execute([':user_ID' => $user_ID]);
    }

    /**
     * Assign role to user
     * @param int $user_ID The user's ID.
     * @param int $role_id The role's ID (e.g., 2 for 'user').
     * @param string|null $assigned_by Identifier of who/what assigned the role (default to null for self-registration/system).
     */
    public function assignRole($user_ID, $role_id, $assigned_by = null) {
        // Ensure all columns are included in the INSERT statement
        $sql = "INSERT INTO user_role (user_ID, role_id, assigned_by, assigned_at) 
                 VALUES (:user_ID, :role_id, :assigned_by, NOW())"; 

        $stmt = $this->conn->prepare($sql);
        
        // Ensure the number of parameters passed in the array matches the placeholders in the SQL
        return $stmt->execute([
            ':user_ID' => $user_ID,
            ':role_id' => $role_id,
            // The third parameter passed from google-callback.php is bound here
            ':assigned_by' => $assigned_by ?? 'System' // Use a default value if null
        ]);
    }

    /**
     * Get user roles
     */
    public function getUserRoles($user_ID) {
        $sql = "SELECT r.role_name
                FROM role r
                INNER JOIN user_role ur ON r.role_id = ur.role_id
                WHERE ur.user_ID = :user_ID";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([':user_ID' => $user_ID]);
        return $stmt->fetchAll(PDO::FETCH_COLUMN);
    }


    /**
     * Check if user has role
     */
    public function hasRole($user_ID, $role_name) {
        $sql = "SELECT COUNT(*) as count FROM user_role ur 
                INNER JOIN role r ON ur.role_id = r.role_id 
                WHERE ur.user_ID = :user_ID AND r.role_name = :role_name";
        
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([
            ':user_ID' => $user_ID,
            ':role_name' => $role_name
        ]);
        
        $result = $stmt->fetch();
        return $result['count'] > 0;
    }

    /**
     * Check if email exists
     * @param string $primary_email The email address to check for existence.
     */
    public function emailExists($primary_email) {
        $sql = "SELECT user_ID FROM user WHERE primary_email = :primary_email";
        $stmt = $this->conn->prepare($sql);
        // Changed binding parameter name to :primary_email
        $stmt->execute([':primary_email' => $primary_email]); 
        return $stmt->fetch() !== false;
    }

    /**
     * Verify password
     */
    public function verifyPassword($password, $hash) {
        // Ensure that a hash exists before verifying
        if (empty($hash) || is_null($hash)) {
            return false;
        }
        return password_verify($password, $hash);
    }

    /**
     * Hash password
     */
    public function hashPassword($password) {
        return password_hash($password, PASSWORD_DEFAULT);
    }

    /**
     * Update user password
     */
    public function updatePassword($user_ID, $new_password) {
        $sql = "UPDATE user SET password = :password WHERE user_ID = :user_ID";
        $stmt = $this->conn->prepare($sql);
        return $stmt->execute([
            ':password' => $this->hashPassword($new_password),
            ':user_ID' => $user_ID
        ]);
    }

    /**
     * Link Google account
     */
    public function linkGoogleAccount($user_ID, $google_sub_id) {
        $sql = "UPDATE user SET google_sub_id = :google_sub_id WHERE user_ID = :user_ID";
        $stmt = $this->conn->prepare($sql);
        return $stmt->execute([
            ':google_sub_id' => $google_sub_id,
            ':user_ID' => $user_ID
        ]);
    }

    /**
     * Get role ID by name
     */
    public function getRoleIdByName($role_name) {
        $sql = "SELECT role_id FROM role WHERE role_name = :role_name";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([':role_name' => $role_name]);
        $result = $stmt->fetch();
        return $result ? $result['role_id'] : null;
    }

    /**
     * Updates fields for a specific user ID.
     * @param int $user_id
     * @param array $fields_to_update Associative array of column => value
     * @return bool
     */
    public function update(int $user_id, array $fields_to_update): bool {
        if (empty($fields_to_update)) {
            return false;
        }

        $set_parts = [];
        $params = [':user_id' => $user_id];

        foreach ($fields_to_update as $column => $value) {
            if ($column === 'user_ID') continue; 
            
            $param_key = ":update_{$column}"; // Use unique placeholder name
            $set_parts[] = "`{$column}` = {$param_key}";
            $params[$param_key] = $value;
        }

        $set_clause = implode(', ', $set_parts);
        
        // Use the query method available in the Database class
        $sql = "UPDATE `user` SET {$set_clause}, `updated_at` = NOW() WHERE `user_ID` = :user_id";
        
        $stmt = $this->db->query($sql, $params); 
        return $stmt->rowCount() > 0;
    }


    /**
     * Updates the role assigned to a specific user (in the user_role bridge table).
     * @param int $user_id
     * @param int $role_id
     * @return bool
     */
    public function updateRole(int $user_id, int $role_id): bool {
        // Update should use the query method from the Database class
        $sql = "UPDATE `user_role` SET `role_ID` = :role_id, `assigned_at` = NOW() WHERE `user_ID` = :user_id";
        
        $params = [
            ':role_id' => $role_id,
            ':user_id' => $user_id
        ];

        $stmt = $this->db->query($sql, $params);
        return $stmt->rowCount() > 0;
    }
}
?>