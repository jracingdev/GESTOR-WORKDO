<?php

require __DIR__."/vendor/autoload.php";

$app = require_once __DIR__."/bootstrap/app.php";

$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$user = App\Models\User::where("email", "joelson@jracing.dev.br")->first();

if ($user) {
    echo "User ID: " . $user->id . PHP_EOL;
    echo "User Type: " . $user->type . PHP_EOL;
    echo "Roles: ";
    foreach ($user->roles as $role) {
        echo $role->name . ", ";
        echo "Permissions for role " . $role->name . ": ";
        foreach ($role->permissions as $permission) {
            echo $permission->name . ", ";
        }
        echo PHP_EOL;
    }
    echo PHP_EOL;
} else {
    echo "Usuário joelson@jracing.dev.br não encontrado.";
}

?>
