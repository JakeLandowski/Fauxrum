<?php
/**
 *  Class used to validate and log a user in.  
 *
 *  DataCore => Validator => Login
 */

/**
 *  Class used to validate and log a user in.
 *  
 *  @author Jacob Landowski
 */
class Login extends Validator 
{
    protected $data = 
    [
        'email'    => null,
        'username' => null,
        'password' => null,
        'auth_method' => 'username'
    ];
    
  //=========================================================//
 //                   PUBLIC FUNCTIONS                      //
//=========================================================//
    /**
     *  Validates email or username and password on the login page.
     *  Populates errors array with errors which can later be retrieved.
     *  Also stores the username or email validated for stickiness.
     */
    public function validate()
    { 
        $this->hasValidated(); // Used to prove this object has ran validation 

        $missingEmail = 'Please enter an email';
        $invalidEmail = 'Please enter a valid email ex: email@place.com';
        $missingUserName = 'Please enter a username';
        $invalidUserName = 'Username must be between 3-20 characters A-Z, a-z and 0-9 only.';
        $missingPassword = 'Please enter a password';
        $invalidPassword = 'Password must be 8 or more characters, 
                            and atleast 1 uppercase, 1 lowercase, 1 digit';


        if($this->getValue('auth_method') == 'email')
        {
            $this->_validateField('email', $missingEmail, $invalidEmail,
            function($value)
            {
                return filter_var($value, FILTER_VALIDATE_EMAIL); 
            });
        }
        else
        {   
            $this->_validateField('username', $missingUserName, $invalidUserName, 
            function($value)
            {
                return preg_match('/^[0-9a-z]{3,20}$/i', $value);
            });
        }
            
        $this->_validateField('password', $missingPassword, $invalidPassword, 
        function($value)
        {
            return preg_match('/[A-Z]/', $value) && 
                   preg_match('/[a-z]/', $value) && 
                   preg_match('/[0-9]/', $value) &&
                   strlen($value) >= 8;
        });
    }

    /**
     *  Attempts to log the user in. Will check either username or email plus
     *  password for authentication. Will return errors if either one fails. 
     *  Otherwise tries to pull the user's TextMap object out from the database
     *  and creates a User object with it to represent the User.
     * 
     *  @return mixed Error messages if User login failed otherwise User object
     */
    public function logUserIn()
    {
        if(count($this->_errors) == 0)
        {
            $email    = $this->getValue('email');
            $username = $this->getValue('username');
            $password = $this->getValue('password');
            
            $whereThisUser = new Condition('User');

            if($this->getValue('auth_method') == 'username')
                $whereThisUser->col('username')->equals($username);
            else
                $whereThisUser->col('email')->equals($email);

            $options = 
            [
                'condition' => $whereThisUser,
                'fetch'     => Database::ONE
            ];

            $result = Database::SELECT_ALL('User', $options);

            $returnValue = '';

            $failed = isset($result['success']) && !$result['success'];
            if($failed || $result['num_rows'] == 0) // No matches
            {
                $returnValue = 'This account doesn\'t exist';
            }
            else if(isset($result['row']) &&        // Verify password
                    !password_verify($this->getValue('password'), $result['row']['password']))
            {
                $returnValue = 'Invalid password';
            }
            else if(isset($result['row'])) // SUCCESSFUL LOGIN
            {
                $userId     = $result['row']['id'];
                $userName   = $result['row']['username'];
                $userEmail  = $result['row']['email'];
                $numThreads = $result['row']['num_threads'];
                $numPosts   = $result['row']['num_posts'];
                
                $returnValue = new User($userId, $email, $username);
                $returnValue->setValue('num_threads', $numThreads);
                $returnValue->setValue('num_posts',   $numPosts);
                $returnValue->fetchMapFromDatabase();
            }

            return $returnValue; // Return User Object or Error Message
        }
        else // If whoever uses this class forgets to check for errors
        {
            CustomError::throw('Tried to login new member in Login 
                                when there are still errors.', 2);
        }
    }
}