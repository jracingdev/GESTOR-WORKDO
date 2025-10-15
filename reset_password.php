<?php

require __DIR__."/vendor/autoload.php";

$app = require_once __DIR__."/bootstrap/app.php";

$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$user = App\Models\User::where("email", "joelson@jracing.dev.br")->first();
if ($user) {
    $user->password = bcrypt("120507");
    $user->save();
    echo "Senha do usuário joelson@jracing.dev.br redefinida para 120507.";
} else {
    echo "Usuário joelson@jracing.dev.br não encontrado.";
}

?>
