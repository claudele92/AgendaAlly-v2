<?php
declare(strict_types=1);
// Safe, generated test documents only. No external document ingestion.
if (PHP_SAPI!=='cli') throw new RuntimeException('CLI only.');
$root=dirname(__DIR__,2);
$directory=$argv[1]??'';
if (!preg_match('~^'.preg_quote($root,'~').'/\.local/manual-finance/http-identity-[a-f0-9]{16}$~D',$directory)
    || !is_dir($directory) || is_link($directory)) throw new RuntimeException('Owned fixture directory required.');
$out=$directory.'/synthetic';
if (!mkdir($out,0700)) throw new RuntimeException('Synthetic documents are immutable; refuse reuse.');
$objects=[
    '<< /Type /Catalog /Pages 2 0 R >>',
    '<< /Type /Pages /Kids [3 0 R] /Count 1 >>',
    '<< /Type /Page /Parent 2 0 R /MediaBox [0 0 300 150] /Resources << /Font << /F1 4 0 R >> >> /Contents 5 0 R >>',
    '<< /Type /Font /Subtype /Type1 /BaseFont /Helvetica >>',
];
$text="BT /F1 12 Tf 20 100 Td (SYNTHETIC TEST RECEIPT - NO MONEY MOVED) Tj ET\n";
$objects[]='<< /Length '.strlen($text)." >>\nstream\n".$text.'endstream';
$pdf="%PDF-1.4\n"; $offsets=[0];
foreach ($objects as $i=>$object) {
    $offsets[]=strlen($pdf); $pdf.=($i+1)." 0 obj\n".$object."\nendobj\n";
}
$xref=strlen($pdf); $pdf.="xref\n0 6\n0000000000 65535 f \n";
foreach (array_slice($offsets,1) as $offset) $pdf.=sprintf("%010d 00000 n \n",$offset);
$pdf.="trailer\n<< /Size 6 /Root 1 0 R >>\nstartxref\n".$xref."\n%%EOF\n";
file_put_contents($out.'/receipt.pdf',$pdf);
if (!extension_loaded('gd')) throw new RuntimeException('GD required to generate safe images.');
$image=imagecreatetruecolor(380,70);
imagefill($image,0,0,imagecolorallocate($image,245,245,245));
imagestring($image,3,10,20,'SYNTHETIC TEST ONLY - NO MONEY MOVED',imagecolorallocate($image,0,0,0));
imagepng($image,$out.'/receipt.png');
imagejpeg($image,$out.'/receipt.jpg',90);
$manifest=[];
foreach (glob($out.'/receipt.*') as $file) {
    chmod($file,0600);
    $manifest[basename($file)]=['bytes'=>filesize($file),'sha256'=>hash_file('sha256',$file),
        'mime'=>(new finfo(FILEINFO_MIME_TYPE))->file($file)];
}
file_put_contents($out.'/manifest.json',json_encode($manifest,JSON_PRETTY_PRINT|JSON_THROW_ON_ERROR)."\n");
echo json_encode($manifest,JSON_THROW_ON_ERROR),"\n";
