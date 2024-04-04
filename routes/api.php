<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

use LdapRecord\Container;
use LdapRecord\Connection;
use LdapRecord\Models\Entry;
use LdapRecord\Auth\Events\Failed;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});



Route::post('/ldap-test', function (Request $request) {
    // Récupération des données d'authentification de la requête
    $username = $request->input('username');
    $password = $request->input('password');

    // Vérification si les données d'authentification sont présentes
    if (!$username || !$password) {
        return response()->json(['error' => 'Username and password are required.'], 400);
    }

    // Création de la connexion LDAP
    $connection = new Connection([
        'hosts' => ['192.168.253.2'],
        // 'username' => $username,
        // 'password' => $password,
        'base_dn' => 'DC=rcarre.eu,DC=eu',
    ]);

    try {
        // Connexion au serveur LDAP
        $connection->connect();
        return response()->json(['message' => 'LDAP Authentication Success']);

    } catch (\LdapRecord\Auth\BindException $e) {
        // Erreur de liaison LDAP, authentification échouée
        return response()->json(['error' => 'LDAP Authentication Failed: ' . $e->getMessage()], 401);
    } catch (\Exception $e) {
        // Autres erreurs
        return response()->json(['error' => 'An error occurred: ' . $e->getMessage()], 500);
    }
});
