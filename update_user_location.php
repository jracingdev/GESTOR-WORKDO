<?php

require __DIR__."/vendor/autoload.php";

$app = require_once __DIR__."/bootstrap/app.php";

$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$user = App\Models\User::find(1);
if ($user) {
    $user->latitude = -23.55052;
    $user->longitude = -46.63330;
    $user->save();
    echo "Usuário 1 atualizado com sucesso.";
} else {
    echo "Usuário 1 não encontrado.";
}

?>
