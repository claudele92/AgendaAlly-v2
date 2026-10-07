<?php
declare(strict_types=1);
require __DIR__.'/runtime.php';
$app=stagingApplication();$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
$files=json_decode(file_get_contents(getcwd().'/.local/staging-mvp/media-fixtures.json'),true,512,JSON_THROW_ON_ERROR)['examples'];
$url=fn($path)=>Illuminate\Support\Facades\Storage::disk('public')->url($path);
Illuminate\Support\Facades\DB::transaction(function()use($files,$url) {
    Illuminate\Support\Facades\DB::table('shops')->whereIn('id',[101,102])->update([
        'logo_img'=>$url($files['shop']),'background_img'=>$url($files['shop'])]);
    Illuminate\Support\Facades\DB::table('users')->whereIn('id',[101,102,103,105,107])->update(['img'=>$url($files['specialist'])]);
    Illuminate\Support\Facades\DB::table('services')->where('id',101)->update(['img'=>$url($files['service'])]);
    Illuminate\Support\Facades\DB::table('galleries')->where('loadable_type','App\\Models\\Service')->where('loadable_id',101)
        ->update(['path'=>$url($files['service'])]);
});
echo json_encode(['scope'=>'Approved copied media metadata for isolated synthetic staging identities only',
    'cause'=>'Fixture stored relative image paths; native production consumers require absolute public URLs; Service img was unset.',
    'changes'=>['Shop101/102 logo/background','Synthetic actor photos','Service101 img/gallery'],
    'financialMutation'=>false,'status'=>'PASS'],JSON_PRETTY_PRINT).PHP_EOL;