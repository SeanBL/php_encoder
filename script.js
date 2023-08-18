var responseText = "123234234";
var messageElement = document.getElementById("message");
var fetchButton = document.getElementById("fetchButton");

var xhr = new XMLHttpRequest();
        
xhr.onload = function() {
    // this
    const myArr = JSON.parse(this.responseText);
    document.getElementById("message").innerHTML = myArr["accessors"][0]['max'][0];
    console.log(myArr["accessors"][0]['max'][0]);

    // call the function decode, passing the value in to be decoded

    // As an analogy, it dynamically created the variable in the script block outside of all functions
    //window.responseText = this.responseText;
    
};
xhr.open("GET", "index.php", true);
xhr.send();


fetchButton.addEventListener("click", function() {
    var xhr = new XMLHttpRequest();
    xhr.onload = function() {
        console.log(this.responseText);
    };
    
    xhr.open("PUT", "index.php", true);
    xhr.send(0.47384738);
});

function init() {

    //var responseText = "askhfsdk"
    console.log(responseText)
    setTimeout(()=>{
        console.log("**** Outside of onload ****")
        console.log(responseText)
    },2000)
}

init();