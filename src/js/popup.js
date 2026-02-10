document.addEventListener("DOMContentLoaded", function() {
    registerEventListeners();

})

function registerEventListeners() {

  /* open popup */
    var popup = document.getElementById("openPopup")
    console.log(popup)
    if (popup !== null)
    {
        popup.addEventListener("click", popUp)
    }
    else
    {
        console.log("Pop Up button not found")
    }

    // close popup
    var closeBtn = document.querySelector(".close")
    if (closeBtn !== null)
    {
        closeBtn.addEventListener("click", closeModal)
    }
    else
    {
        console.log("close button not found")
    }

    // close by click outside modal
    var modal = document.getElementById("modal")
    if (modal !== null)
    {
        modal.addEventListener("click", closeModalOutside)
    }
    else
    {
        console.log("modal not found")
    }
}

function popUp()
{
    var popUpURL = "booking.php";
    var modal = document.getElementById("modal");
    var iframe = document.getElementById("popupFrame");
    
    iframe.onload = function () {
        modal.style.display = "block";
        iframe.style.opacity = "1"; // fade in
    }

    iframe.style.opacity = "0" // hide frame until style loaded

    fetch("api/api_generate_token.php")
    .then(response => {
        if (!response.ok) throw new Error("Token error");
        iframe.src = popUpURL;
        
    })
    .catch(err => console.error(err));
}

function closeModal()
{
    var modal = document.getElementById("modal");
    modal.style.display = "none";
}

function closeModalOutside(e)
{
    var modal = document.getElementById("modal");
    if (e.target == modal)
    {
        modal.style.display = "none";
    }
}