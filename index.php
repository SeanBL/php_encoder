<?php
global $sourceGltf;
$sourceGltf = "gltfDemo\woolly-mammoth-100k-4096.gltf";
$uID = 123;
$shuffleSq = false;
$timeStampVal = 30;

global $UID_MAX_LENGTH;
global $USHF_MAX_LENGTH;
global $TIMESTAMP_MAX_LENGTH;
global $TIMESTAMP_VALIDITY_MAX_LENGTH;
global $SHFL_MATRIX_SIZE;

$UID_MAX_LENGTH = 10;
$USHF_MAX_LENGTH = 10;
$TIMESTAMP_MAX_LENGTH = 10;
$TIMESTAMP_VALIDITY_MAX_LENGTH = 5;
$SHFL_MATRIX_SIZE = 6 * 5;

//Create unique list of ten integer values from a list of values ranging from 0 to 29.
$shuffleSq = array_rand(range(0,29), $USHF_MAX_LENGTH);
//shuffle the array list
shuffle($shuffleSq);
//this is for testing purposes to confirm that each element is a unique value.
foreach($shuffleSq as $value) {
    echo "$value <br>";
}
$encoded;

//convert the BASE60 value to a unicode character
function num2B60($num) {
    if ($num > 59 or $num < 0) {
        echo "BASE60 encoder received number out of range: $num";
    }

    if ($num >= 52) {
        return mb_chr($num -52 + 50, 'UTF-8');
    } elseif ($num >= 26) {
        return mb_chr($num -26 + 97, "UTF-8");
    } else {
        return mb_chr($num + 65);
    }
}

function b602Num($uniChar) {
    $charList = array("A","B","C","D","E","F","G","H","I","J","K","L","M","N","O","P","Q","R","S","T","U","V","W","X","Y","Z","a","b","c","d","e","f","g","h","i","j","k","l","m","n","o","p","q","r","s","t","u","v","w","x","y","z","2","3","4","5","6","7","8","9");
    if (in_array($uniChar, $charList)) {
        $num = mb_ord($uniChar, "UTF-8");
        if ($num >= 97) {
            return $num -97 + 26;
        } elseif ($num >= 65) {
            return $num -65;
        } else {
            return $num -50 + 52;
        }
    } else {
        echo "BASE60 decoder received symbol out of range.";
    }
}


//Generate Token
function encodeAndGenerateToken($uID, $ushf, $tsv) {
    global $UID_MAX_LENGTH;
    global $USHF_MAX_LENGTH;
    global $TIMESTAMP_MAX_LENGTH;
    global $TIMESTAMP_VALIDITY_MAX_LENGTH;
    global $SHFL_MATRIX_SIZE;

    $token = "";
    $currentTime = time();

    $headerLength = $UID_MAX_LENGTH + $USHF_MAX_LENGTH + $TIMESTAMP_MAX_LENGTH + $TIMESTAMP_VALIDITY_MAX_LENGTH;
    
    $idxs = array_rand(range(0,59), $headerLength);
    shuffle($idxs);

    for ($i = 0; $i < count($idxs); $i++) {
        $token .= num2B60($idxs[$i]);
    }
    for ($i = 0; $i < 61; $i++) {
        $token .= ".";
    }
    
    echo "Token: $token <br>";
    
    // Write symbols representing UID decimals one by one into formerly randomly chosen positions.
    $tempUID = str_pad($uID, $UID_MAX_LENGTH, "0", STR_PAD_LEFT);
    echo "Temp UID: $tempUID <br>";
    for ($i = 0; $i < $UID_MAX_LENGTH; $i++) {
        $newToken = substr($token, 0, $headerLength + $idxs[$i]); 
        $newToken .= num2B60(substr($tempUID, -1) + (rand(0, 5) * 10));
        $newToken .= substr($token, $headerLength + $idxs[$i] + 1); 
        $token = $newToken;
        //Don't understand this.
        $tempUID = substr($tempUID, 0, -1);
    }
    echo "Temp UID: $tempUID <br>";
    echo "<br>$token <br>";

    //Write symbols representing a unique Shuffling sequence for this particular user. 
    $idxOffSet = $UID_MAX_LENGTH;
    for ($i = 0; $i < $USHF_MAX_LENGTH; $i++) {
        $newToken = substr($token, 0, $headerLength + $idxs[$i + $idxOffSet]);
        $newToken .= num2B60($ushf[$USHF_MAX_LENGTH - $i - 1] + (rand(0, 1) * 30));
        $newToken .= substr($token, $headerLength + $idxs[$i + $idxOffSet] + 1);
        $token = $newToken;
    }
    echo "Token: $token <br>";

    //Write symbols representing Timestamp decimals.
    $idxOffSet += $USHF_MAX_LENGTH;
    $thisMomentTemp = strval($currentTime);

    for ($i = 0; $i < $TIMESTAMP_MAX_LENGTH; $i++) {
        $newToken = substr($token, 0, $headerLength + $idxs[$i + $idxOffSet]);
        $newToken .= num2B60($thisMomentTemp[-1] + (rand(0, 5) * 10));
        $newToken .= substr($token, $headerLength + $idxs[$i + $idxOffSet] + 1);
        $token = $newToken;
        $thisMomentTemp = substr($thisMomentTemp, 0, -1);
    }
    echo "This Moment Temp: $thisMomentTemp <br>";
    echo "New Token: $newToken <br>";

    //Write symbols representing Timestamp validity decimals.
    $idxOffSet += $UID_MAX_LENGTH;
    $tsvTemp = str_pad($tsv, $TIMESTAMP_VALIDITY_MAX_LENGTH, 0, STR_PAD_LEFT);
    for ($i = 0; $i < $TIMESTAMP_VALIDITY_MAX_LENGTH; $i++) {
        $newToken = substr($token, 0, $headerLength + $idxs[$i + $idxOffSet]);
        $newToken .= num2B60(substr($tsvTemp, -1) + (rand(0, 5) * 10));
        $newToken .= substr($token, $headerLength + $idxs[$i + $idxOffSet] + 1);
        $token = $newToken;
        $tsvTemp = substr($tsvTemp, 0, -1);
    }
    echo "Token: $token <br>";

    //Fill the remaining blank spaces with random-generated BASE60 symbols.
    for ($i = $headerLength; $i < strlen($token); $i++) {
        if($token[$i] == '.') {
            $newToken = substr($token, 0, $i);
            $newToken .= num2B60(rand(0, 59));
            $newToken .= substr($token, $i + 1);
            $token = $newToken;
        }
    }
    echo "Token: $token <br>";

    //Empty shuffling matrix with 6 rows and 5 columns.
    $shMat = array(
        array(0, 0, 0, 0, 0),
        array(0, 0, 0, 0, 0),
        array(0, 0, 0, 0, 0),
        array(0, 0, 0, 0, 0),
        array(0, 0, 0, 0, 0),
        array(0, 0, 0, 0, 0)
    );

    //Update the source GLTF file by adding zeros to the count value in the accessors.
    global $sourceGltf;
    $gltfContents = file_get_contents($sourceGltf);
    $data = json_decode($gltfContents, true);

    // Fill buffer length with zeros
    $sEncKey = str_pad($data["accessors"][0]["count"], $USHF_MAX_LENGTH, 0, STR_PAD_LEFT);
    $data['accessors'][0]['count'] = $sEncKey;
    $updatedGltf = json_encode($data, JSON_PRETTY_PRINT);
    file_put_contents($sourceGltf, $updatedGltf);
    echo "<br>";
    echo file_get_contents($sourceGltf);
    
    //Write hidden key digits into the shuffling matrix.
    $shOffsetList = array();
    for ($i = 0; $i < $USHF_MAX_LENGTH; $i++) {
        $shOffset = b602Num($token[$headerLength + b602Num($token[$i + $UID_MAX_LENGTH])]) %30;
        echo "<br> Shuffle Off Set: $shOffset <br>";
        array_push($shOffsetList, $shOffset);
        $shMat[floor($shOffset / 5)][$shOffset % 5] = intval($sEncKey[$i]);
    }
    echo "<br>";
    echo $shMat[0][0];
    echo $shMat[0][1];
    echo $shMat[0][2];
    echo $shMat[0][3];
    echo $shMat[0][4];
    echo "<br>";
    echo $shMat[1][0];
    echo $shMat[1][1];
    echo $shMat[1][2];
    echo $shMat[1][3];
    echo $shMat[1][4];
    echo "<br>";
    echo $shMat[2][0];
    echo $shMat[2][1];
    echo $shMat[2][2];
    echo $shMat[2][3];
    echo $shMat[2][4];
    echo "<br>";
    echo $shMat[3][0];
    echo $shMat[3][1];
    echo $shMat[3][2];
    echo $shMat[3][3];
    echo $shMat[3][4];
    echo "<br>";
    echo $shMat[4][0];
    echo $shMat[4][1];
    echo $shMat[4][2];
    echo $shMat[4][3];
    echo $shMat[4][4];
    echo "<br>";
    echo $shMat[5][0];
    echo $shMat[5][1];
    echo $shMat[5][2];
    echo $shMat[5][3];
    echo $shMat[5][4];
    


}
$tempArry = array(4, 12, 14, 0, 2, 17, 3, 20, 22, 16);
encodeAndGenerateToken(477, $tempArry, 123);


?>