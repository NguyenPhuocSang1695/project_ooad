const logoutBtn = document.querySelector(".logout-btn")

logoutBtn.addEventListener("click",()=>{
    window.location.replace("./admin-login.html")
})

window.history.forward();
function noBack() {
    window.history.forward();
}

