<link rel="icon" type="image/x-icon" href="../images/home.ico">
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<meta http-equiv="Content-Security-Policy" 
    content="script-src 'self' 'unsafe-inline'
    https://js.stripe.com https://cdn.jsdelivr.net 
    https://cdnjs.cloudflare.com
    https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.29.4/moment.min.js 
    https://ajax.googleapis.com/ajax/libs/jquery/3.7.1/jquery.min.js
    https://static.cloudflareinsights.com; 
    frame-src 'self' https://js.stripe.com https://hooks.stripe.com; 
    connect-src 'self' https://api.stripe.com https://cdn.jsdelivr.net
    https://static.cloudflareinsights.com https://ajax.googleapis.com
    https://cdnjs.cloudflare.com;">

<!-- CSS -->
<link href="https://fonts.googleapis.com/css?family=Lato:300,400,700&display=swap" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.1/dist/css/bootstrap.min.css"
    rel="stylesheet"
    integrity="sha384-4bw+/aepP/YC94hEpVNVgiZdgIC5+VKNBQNGCHeKRQN+PtmoHDEXuppvnDJzQIu9"
    crossorigin="anonymous">
<link rel="stylesheet" href="css/style.css">

<!--Bootstrap JS-->
<script defer
    src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.1/dist/js/bootstrap.bundle.min.js"
    integrity="sha384-HwwvtgBNo3bZJJLYd8oVXjrBZt8cqVSpeBNS5n7C8IVInixGAoxmnlMuBnhbgrkm"
    crossorigin="anonymous">
</script>
<script>
  document.addEventListener("DOMContentLoaded", function() {
    registerFilterListeners();
    filterRooms();
})

//function to find all filter inputs on the page
function registerFilterListeners() {
    const searchInput = document.querySelector('input[type="search"]');
    const fearRadios = document.querySelectorAll('input[name="fear"]');
    const actorRadios = document.querySelectorAll('input[name="actor"]');
    const difficultyRadios = document.querySelectorAll('input[name="difficulty"]');
    const genreCheckboxes = document.querySelectorAll('input[type="checkbox"]');

    //runs filterrooms functions 
    if (searchInput) {
        searchInput.addEventListener('keyup', filterRooms);
    }
    
    fearRadios.forEach(radio => {
        radio.addEventListener('change', filterRooms);
    });
    
    actorRadios.forEach(radio => {
        radio.addEventListener('change', filterRooms);
    });

    difficultyRadios.forEach(radio => {
        radio.addEventListener('change', filterRooms);
    });
    
    genreCheckboxes.forEach(checkbox => {
        checkbox.addEventListener('change', filterRooms);
    });
}


// the main filter function.
function filterRooms() {

    //basically finds if the room matches all the filter values and only shows it if it does else it wont show
    //also updates the room number with a counter
    const searchText = document.querySelector('input[type="search"]').value.toLowerCase();
    const fearValue = document.querySelector('input[name="fear"]:checked').value;
    const actorValue = document.querySelector('input[name="actor"]:checked').value;
    const difficultyValue = document.querySelector('input[name="difficulty"]:checked').value;
    //css selector ':checked' searches for the radio button pressed

    //checkbox: first generates an empty array and pushes values of checked checkbox into the array
    const checkedGenres = [];
    document.querySelectorAll('input[type="checkbox"]:checked').forEach(checkbox => {
        checkedGenres.push(checkbox.value);
    });

    //get all room cards and reset counter
    const roomCards = document.querySelectorAll('.room-card');
    let visibleCount = 0;

    //main loop
    roomCards.forEach(card => {
        
        //get data from the cards -> data labels in php
        const cardTitle = (card.dataset.title || '').toLowerCase();
        const cardFear = card.dataset.fear;
        const cardActor = card.dataset.actor;
        const cardGenre = card.dataset.genre;
        const cardDifficulty = card.dataset.difficulty;

        //does card title include text from the search bar
        const titleMatch = cardTitle.includes(searchText);
        //radio and checkbox logic
        const fearMatch = (fearValue === 'all' || fearValue === cardFear);
        const actorMatch = (actorValue === 'all' || actorValue === cardActor);
        const difficultyMatch = (difficultyValue === 'all' || difficultyValue === cardDifficulty);
        const genreMatch = (checkedGenres.length === 0 || checkedGenres.includes(cardGenre));

        //impt! only shows the room if ALL conditions are true! else nuh uh
        if (titleMatch && fearMatch && actorMatch && genreMatch && difficultyMatch) {
            card.style.display = 'block'; //show card
            visibleCount++;
        } else {
            card.style.display = 'none';  //hide card
        }
    });

    //update the "Showing {num} rooms" text
    const roomCountText = document.querySelector('.text-center.my-4 p');
    if (roomCountText) {
        roomCountText.textContent = `Showing ${visibleCount} room${visibleCount !== 1 ? 's' : ''}`;
    }
}


/*
* this function is used to filter the room listings
*/
function filterTable() {

  const input = document.getElementById("adminSearchInput");
  const filter = input.value.toUpperCase();


  const table = document.getElementById("roomsTable");
  const tableBody = table.getElementsByTagName("tbody")[0];
  const rows = tableBody.getElementsByTagName("tr");
  const noResults = document.getElementById("noAdminResults");

  let visibleCount = 0;


  for (let i = 0; i < rows.length; i++) {
    const idCell = rows[i].getElementsByTagName("td")[0];
    const nameCell = rows[i].getElementsByTagName("td")[2];

    if (nameCell && idCell) {
      const nameText = nameCell.textContent || nameCell.innerText;
      const idText = idCell.textContent || idCell.innerText;

      // check for matches
      if (nameText.toUpperCase().indexOf(filter) > -1 || idText.toUpperCase().indexOf(filter) > -1) {
        rows[i].style.display = ""; // show
        visibleCount++;
      } else {
        rows[i].style.display = "none"; // hide
      }
    }
  }

  // "no results" message
  if (visibleCount === 0) {
    noResults.style.display = "block";
    // aria 
    noResults.setAttribute("aria-hidden", "false");
  } else {
    table.style.display = "table";
    noResults.style.display = "none";
    noResults.setAttribute("aria-hidden", "true");
  }
}

/*
* this function is used in create_room and edit_room to check their logic
*/
document.addEventListener("DOMContentLoaded", function () {
  // select the first form on the page (works for create and edit pages)
  const form = document.querySelector('form');

  if (form) {
    form.addEventListener('submit', function (e) {
      let errorList = [];

      const minEl = form.querySelector('[name="roomMin"]');
      const maxEl = form.querySelector('[name="roomMax"]');
      const offEl = form.querySelector('[name="roomPriceOffpeak"]');
      const peakEl = form.querySelector('[name="roomPricePeak"]');

      const min = minEl ? parseInt(minEl.value) || 0 : 0;
      const max = maxEl ? parseInt(maxEl.value) || 0 : 0;
      const priceOff = offEl ? parseFloat(offEl.value) || 0 : 0;
      const pricePeak = peakEl ? parseFloat(peakEl.value) || 0 : 0;

      // logic checks

      // check Min vs Max players
      if (min > max) {
        errorList.push("Invalid Players: Minimum (" + min + ") cannot be greater than Maximum (" + max + ").");
      }

      // check prices non-negative
      if (priceOff < 0 || pricePeak < 0) {
        errorList.push("Invalid Price: Prices cannot be negative.");
      }

      // check Price when Peak vs Off-Peak
      if (priceOff > pricePeak) {
        errorList.push("Invalid Price: Off-Peak ($" + priceOff + ") should be lower than Peak ($" + pricePeak + ").");
      }

      if (errorList.length > 0) {
        e.preventDefault(); // stop submission of form
        alert(errorList.join("\n")); // show errors
      }
    });
  }
});
</script>
<script defer src="https://ajax.googleapis.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
<!-- moment.js -->
<script defer src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.29.4/moment.min.js"></script>