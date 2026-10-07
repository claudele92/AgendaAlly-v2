<?php
declare(strict_types=1);
require __DIR__.'/db-evidence.php';
$pdo=stagingRootPdo();
$pdo->exec("CREATE TABLE IF NOT EXISTS staging_operational_probes (
 id varchar(80) NOT NULL PRIMARY KEY, state varchar(80) NOT NULL,
 payload text NULL, updated_at timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
// Copy approved public demo media, not private application data or credentials.
$source=getcwd().'/.migration-backup/backend/storage/app/public';
$destination=getcwd().'/.local/staging-mvp/media';
$iterator=new RecursiveIteratorIterator(new RecursiveDirectoryIterator($source,FilesystemIterator::SKIP_DOTS));
$media=[];
foreach($iterator as $file) {
    if(!$file->isFile()||$file->isLink())continue;
    $relative=substr($file->getPathname(),strlen($source)+1);
    if(!preg_match('/\.(png|jpe?g|webp|gif)$/i',$relative))continue;
    $target=$destination.'/'.$relative;
    if(!is_dir(dirname($target)))mkdir(dirname($target),0750,true);
    copy($file->getPathname(),$target);chmod($target,0640);
    $media[]=$relative;
}
sort($media);
if(count($media)<3)throw new RuntimeException('Approved demo media unavailable');
foreach(['shop'=>$media[0],'service'=>$media[1],'specialist'=>$media[2]]as$kind=>$path) {
    $publicUrl='https://localhost:8443/storage/'.$path;
    if($kind==='shop') {
        $q=$pdo->prepare('UPDATE shops SET logo_img=?,background_img=? WHERE id IN(101,102)');
        $q->execute([$publicUrl,$publicUrl]);
    } elseif($kind==='specialist') {
        $q=$pdo->prepare('UPDATE users SET img=? WHERE id IN(101,102,103,105,107)');
        $q->execute([$publicUrl]);
    } else {
        $q=$pdo->prepare("INSERT INTO galleries(title,loadable_type,loadable_id,type,path,mime) SELECT ?,?,101,'image',?,'image/jpeg' WHERE NOT EXISTS(SELECT 1 FROM galleries WHERE loadable_type=? AND loadable_id=101)");
        $q->execute(['Clearly identified isolated staging media','App\\Models\\Service',$publicUrl,'App\\Models\\Service']);
        $pdo->prepare('UPDATE services SET img=? WHERE id=101')->execute([$publicUrl]);
    }
}
file_put_contents(getcwd().'/.local/staging-mvp/media-fixtures.json',json_encode([
    'scope'=>'Approved public demo photos copied into isolated persistent storage; synthetic identities only',
    'files'=>count($media),'examples'=>['shop'=>$media[0],'service'=>$media[1],'specialist'=>$media[2]],
],JSON_PRETTY_PRINT|JSON_THROW_ON_ERROR));
echo 'Prepared isolated operational probe table and '.count($media)." approved persistent image files.\n";