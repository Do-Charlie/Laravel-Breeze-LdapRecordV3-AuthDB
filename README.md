## Warning
LdapRecord has been made by <a href="https://github.com/stevebauman"> Steve Bauman </a> , there is the original repo : <a href="https://github.com/DirectoryTree/LdapRecord-Laravel"> LdapRecord-Laravel </a>. <br/>
Before continuing you should try to follow the <a href="https://ldaprecord.com/docs/laravel/v3">documentation </a> for LdapRecord-Laravel V3 with breeze.

I have made this repositoring because the solution wasn't working for me when i tried to follow the documentation.
This solution is using <a href="https://ldaprecord.com/docs/core/v3"> LdapRecord </a> , not <a href="https://ldaprecord.com/docs/laravel/v3"> LdapRecord-Laravel</a>

## About 

A solution to authenticate via your ldap credentials in your laravel application using Breeze.

The file updated is "app\Http\Requests\Auth\LoginRequest.php" after breeze has been installed.
You have to add in your .env file : 
```
LDAP_HOST=
LDAP_BASE_DN=
```
## HOW DOES THAT WORK

The LoginRequest has been modified to attempts a connexion with users credentials retrieved from login.blade.php.
If this fails, an identifier error message is returned.

If it works, we will look for the user in our db from his email and connect him via the Auth class of Laravel.
If the user is not existing (has never logged in), a user will be created with just an ID and an email (no password is saved).



## IMPLEMENT
<ol>
    <li> Create a Laravel Project</li>
    <li> Install Breeze
    
```
        composer require laravel/breeze --dev
        php artisan breeze:install
```

</li>
    <li> Update user table <br>
        Set all field 'nullable' exept id and email in create_user_table.php (migration)
        
```
            public function up(): void
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('name')->nullable();
            $table->string('email')->unique();
            // $table->timestamp('email_verified_at')->nullable();
            // $table->string('password')->nullable();
            $table->rememberToken();
            $table->timestamps();
        });
    }
```
</li> 

<li>Run server
    
 ```
    php artisan migrate
    php artisan serve
    npm run dev
```
</li>

<li> Add .env variables
    
```
LDAP_HOST=
LDAP_BASE_DN=
```
    
</li>

<li> Update LoginRequest.php

```
### LoginRequest.php

//Added
use LdapRecord\Container;
use LdapRecord\Connection;
use LdapRecord\Models\Entry;
use LdapRecord\Auth\Events\Failed;

class LoginRequest extends FormRequest
{
...
 public function authenticate(): void
    {
        $this->ensureIsNotRateLimited();

        $email = $this->input('email');
         $password = $this->input('password');
        if (!$email || !$password) {
            throw ValidationException::withMessages([
                'email' => trans('auth.failed'),
            ]);
        }

        $connection = new Connection([
            'hosts' => [env('LDAP_HOST')],
            'username' => $email, 
            'password' => $password,
            'base_dn' => env('LDAP_BASE_DN'),
        ]);
    
         try {
            
        $connection->connect();
        
    
    } catch (\LdapRecord\Auth\BindException $e) {
        RateLimiter::hit($this->throttleKey());
        throw ValidationException::withMessages([
            'email' => trans('auth.failed'),
        ]);
    } catch (\Exception $e) {
        throw ValidationException::withMessages([
            'email' => trans('auth.failed'),
        ]);
    }

    $user = User::where('email', $email)->first();


    if (!$user) {
        $user = new User();
        $user->email = $email;
        $user->save();
    }

    Auth::login($user, $this->boolean('remember'));
    RateLimiter::clear($this->throttleKey());
    return;    
}
}
```

If you want to authenticate with username or other variable , you can replace 'username' in connexion object :

        $cn = $this->input('username');
         $password = $this->input('password');

        $connection = new Connection([
            'hosts' => [env('LDAP_HOST')],
            'cb' => $cn, 
            'password' => $password,
            'base_dn' => env('LDAP_BASE_DN'),
        ]);

Don't forget to also replace "email" field in login.blade.php
</li>

</ol>

