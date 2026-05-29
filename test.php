<?php
require 'vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$user = \App\Models\User::first();

$request = Illuminate\Http\Request::create('/api/diagrams/drawing', 'POST', [
    'project_title' => 'Test',
    'status' => 'submitted',
    'project_id' => 1
]);
$request->setUserResolver(function () use ($user) {
    return $user;
});
// Skip auth middleware for testing
app()->instance('middleware.disable', true);

$response = app()->handle($request);
echo $response->getContent();
