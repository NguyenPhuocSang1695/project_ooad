const form = document.querySelector("form")

form.addEventListener("submit",(e)=>{
    e.preventDefault()

    const gmail = form.gmail.value
    const password = form.password.value

    const authenticated = authentication(gmail,password)

    if(authenticated){
        window.location.href = "./admin.html"
    }else{
        alert("wrong")
    }
})

// function for checking username and password

function authentication(gmail,password){
    if(gmail === "thieu0311@gmail.com" && password === "123456"){
        return true
    }else{
        return false
    }
}

window.history.forward();
function noBack() {
    window.history.forward();
}