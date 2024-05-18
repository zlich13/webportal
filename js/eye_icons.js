 //show or hide passwords, toggle eye icons
function hide(input, eye, eyeslash){
    let x = document.getElementById(input);
    let y = document.getElementById(eye);
    let z = document.getElementById(eyeslash);

    if(x.type === 'password'){
        x.type = "text";
        y.style.display = "inline-block";
        z.style.display = "none";
    } else {
        x.type = "password";
        y.style.display = "none";
        z.style.display = "inline-block";
    }
}