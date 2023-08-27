var messageElement = document.getElementById("message");
var fetchButton = document.getElementById("fetchButton");
const UID_MAX_LENGTH = 10;
const USHF_MAX_LENGTH = 10;
const TIMESTAMP_MAX_LENGTH = 10;
const TIMESTAMP_VALIDITY_MAX_LENGTH = 5;
const SHFL_MATRIX_SIZE = 6 * 5;

let egltf = '{"accessors":[{"bufferView":0,"componentType":5126,"count":1,"max":[0.9607167317084,1.7219889198941,2.5408611968757],"min":[-0.9601930000524,-1.7222570020573,-2.5406730822498],"type":"VEC3"},{"bufferView":1,"componentType":5126,"count":1,"type":"VEC3"},{"bufferView":2,"componentType":5126,"count":1,"type":"VEC2"},{"bufferView":3,"componentType":5125,"count":300000,"type":"SCALAR"}],"asset":{"generator":"MeshSmith mesh conversion tool","version":"2.0"},"bufferViews":[{"buffer":0,"byteLength":883056,"target":34962},{"buffer":0,"byteLength":883056,"byteOffset":883056,"target":34962},{"buffer":0,"byteLength":588704,"byteOffset":1766112,"target":34962},{"buffer":0,"byteLength":1200000,"byteOffset":2354816,"target":34963}],"buffers":[{"byteLength":3554816,"uri":"woolly-mammoth-100k-4096.bin"}],"images":[{"uri":"woolly-mammoth-100k-4096-occlusion.jpg"},{"uri":"woolly-mammoth-100k-4096-normals.jpg"}],"materials":[{"name":"default","normalTexture":{"index":1},"occlusionTexture":{"index":0},"pbrMetallicRoughness":{"metallicFactor":0.100000001490116,"roughnessFactor":0.800000011920929}}],"meshes":[{"primitives":[{"attributes":{"NORMAL":1,"POSITION":0,"TEXCOORD_0":2},"indices":3,"material":0,"mode":4}]}],"nodes":[{"mesh":0}],"scene":0,"scenes":[{"nodes":[0]}],"textures":[{"source":0},{"source":1}]}';

let egltfObj = JSON.parse(egltf);
console.log(egltfObj);

const charList = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz23456789';
function b602num(asc) {
    if (charList.includes(asc)) {
        let num = asc.charCodeAt();
        if (num >= 97) {
            return num - 97 + 26;
        } else if (num >= 65) {
            return num - 65;
        } else {
            return num - 50 + 52;
        }
        
    } else {
        console.log("Base60 decoder received symbol out of range: " + asc);
    }
}



let etkn = "EXpmuP2dS7UFKiGJzqbWlxQevtw4cCMTYL6JnLPH2vBxCyAhEFesYo2QZEHBTeqaKh8ORD9FPoBlOOJoseigroeKDxVKoBGb";

function decodeGltfAndToken(egltf, etkn) {
    const headerLength = UID_MAX_LENGTH + USHF_MAX_LENGTH + TIMESTAMP_MAX_LENGTH + TIMESTAMP_VALIDITY_MAX_LENGTH;
    let unshOffsetList = [];
    for (let i = 0; i < USHF_MAX_LENGTH; i++) {
        let unshOffset = b602num(etkn.charAt(headerLength + b602num(etkn.charAt(i + UID_MAX_LENGTH)))) % 30;
        unshOffsetList.push(unshOffset);
    }

    //Transcribe data from encoded GLTF into matrix form
    let unshMatrix = [[0,0,0,0,0], [0,0,0,0,0], [0,0,0,0,0], [0,0,0,0,0], [0,0,0,0,0], [0,0,0,0,0]];
    for (let i = 0; i < 3; i++) {
        let encValStr = String(egltf["accessors"][0]["max"][i]);
        let encVal = encValStr.slice(-6, -1);
        for (let j = 0; j < 5; j++) {
            unshMatrix[i][j] = encVal.charAt(j);
        }
    }

    for (let i = 0; i < 3; i++) {
        let encValStr = String(egltfObj["accessors"][0]["min"][i]);
        let encVal = encValStr.slice(-6, -1);
        for (let j = 0; j < 5; j++) {
            unshMatrix[i + 3][j] = encVal.charAt(j);
        }
    }

    for (let i = 0; i < 6; i++) {
        
        for (let j = 0; j < 5; j++) {
            console.log(unshMatrix[i][j]);
        }
    }

    //Extract matrix shuffling offsets from token and reconstruct the missing key value.
    let decKey = "";
    for (let i = 0; i < USHF_MAX_LENGTH; i++) {
        decKey += String(unshMatrix[Math.floor(unshOffsetList[i] / 5)][unshOffsetList[i] % 5]);
    }
    decKey = String(parseInt(decKey));
    console.log(decKey);
    
    // Decode user ID.
    let decUID = "";
    for (let i = 0; i < UID_MAX_LENGTH; i++) {
        let uidDec = String(b602num(etkn.charAt(headerLength + b602num(etkn.charAt(i)))) % 10);
        decUID += uidDec;
    }
    
    console.log(decUID);
    // Reverse the string function.
    function reverseString(str) {
        let splitString = str.split("");
        let reverseString = splitString.reverse();
        let joinString = reverseString.join("");
        return joinString;
    }
    let newDecUID = reverseString(decUID);
    console.log(newDecUID);
    newDecUID = String(parseInt(newDecUID));
    console.log(newDecUID);

    // Decode UNIX timestamp.
    let decTstp = '';
    for (let i = 0; i < TIMESTAMP_MAX_LENGTH; i++) {
        let tstpDec = String(b602num(etkn.charAt(headerLength + b602num(etkn.charAt(i + UID_MAX_LENGTH + USHF_MAX_LENGTH)))) % 10);
        decTstp += tstpDec;
    }
    let newDecTstp = reverseString(decTstp);
    newDecTstp = String(parseInt(newDecTstp));
    console.log(newDecTstp);

    // Decode UNIX timestamp validity interval.
    let decTstpVal = '';
    for (let i = 0; i < TIMESTAMP_VALIDITY_MAX_LENGTH; i++) {
        let tstpValDec = String(b602num(etkn.charAt(headerLength + b602num(etkn.charAt(i + UID_MAX_LENGTH + USHF_MAX_LENGTH + TIMESTAMP_MAX_LENGTH)))) % 10);
        decTstpVal += tstpValDec;
    }
    let newDecTstpVal = reverseString(decTstpVal);
    newDecTstpVal = String(parseInt(newDecTstpVal));
    console.log(newDecTstpVal);

    let decGltf = egltfObj;
    decGltf["accessors"][0]["count"] = parseInt(decKey);
    decGltf["accessors"][1]["count"] = parseInt(decKey);
    decGltf["accessors"][2]["count"] = parseInt(decKey);

    console.log(decGltf["accessors"][0]["count"]);
    console.log(decGltf["accessors"][1]["count"]);
    console.log(decGltf["accessors"][2]["count"]);

    return decGltf;
    
}
decodeGltfAndToken(egltfObj, etkn);