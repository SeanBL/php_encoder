<?php

$sourceGltf = "gltfDemo\woolly-mammoth-100k-4096.gltf";
function gltfFile($sGltf) {
    
    $contents = file_get_contents($sGltf);
    $arr = json_decode($contents, true);
    $arrEnc = json_encode($arr);
    return $arrEnc;
}

//$arrayLine = $arr['accessors'][0]["max"][0];
echo gltfFile($sourceGltf);
$dataTest = array("John", "Mary", "Peter");
$datat = json_encode($dataTest);
//echo $datat;
?>