<?php

namespace Tests\Feature;

use App\User;
use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

class AuthTest extends TestCase
{
    use RefreshDatabase;
    use WithFaker;
    
    // public function setUp(): void
    // {
    //     parent::setUp();
    // }

    /**
     *Test successful registration with good credentials
    */
    public function test_register(){
        $password = $this->faker->password;
        $goodUserData = [
            'name' => $this->faker->name,
            'email' => $this->faker->safeEmail,
            'password' => $password,
            'password_confirmation' => $password,
        ];
        //try to register
        $response = $this->json('POST',route('api.register'), $goodUserData);
        //Assert that it is successful
        $response->assertStatus(200);
        //check for token in the response
        $this->assertArrayHasKey('access_token', $response->json());
    }

    /**
     *Test registration with invalid email
    */
    public function test_register_with_invalid_email(){
        $password = $this->faker->password;
        $badUserData = [
            'name' => $this->faker->name,
            'email' => 'notAValidEmail@',
            'password' => $password,
            'password_confirmation' => $password, //same as above
        ];
        //try to register
        $response = $this->json('POST',route('api.register'), $badUserData);
        //Assert that it is NOT successful
        $response->assertStatus(422);
    }

     /**
     *Test registration with invalid email
    */
    public function test_register_with_not_matching_password(){
        $badUserData = [
            'name' => $this->faker->name,
            'email' => $this->faker->safeEmail,
            'password' => $this->faker->password,
            'password_confirmation' => $this->faker->password, //diff from the first one
        ];
        //Send post request
        $response = $this->json('POST',route('api.register'), $badUserData);
        //Assert that it is NOT successful, because the password confirmation does not match
        $response->assertStatus(422);
    }


    /**
     *Test successful login
    */
    public function test_login()
    {
        $email = $this->faker->safeEmail;
        $password = $this->faker->password;
        factory(User::class)->create([
            'email' => $email,
            'password' => bcrypt($password)
        ]);

        //Try to log in
        $response = $this->json('POST',route('api.authenticate'), [
            'email' => $email,
            'password' => $password,
        ]);
        //Assert that it succeeded and received the token
        $response->assertStatus(200);
        $this->assertArrayHasKey('access_token',$response->json());
    }

    /**
     *Test successful login
    */
    public function test_unsuccessful_login()
    {
        $email = $this->faker->freeEmail;
        $password = $this->faker->password;
        factory(User::class)->create([
            'email' => $email,
            'password' => bcrypt($password)
        ]);

        //Try to log in
        $response = $this->json('POST',route('api.authenticate'), [
            'email' => str_replace('@',".", $email),
            'password' => $password,
        ]);
        //Assert that it did NOT succeeded and received the token
        $response->assertStatus(422);
    }
}
