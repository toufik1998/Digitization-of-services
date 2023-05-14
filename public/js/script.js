
let step = document.querySelectorAll(".step");
let compt =0;
let clickNext =document.querySelector("#click-next")
let cercle = document.querySelectorAll(".cercle");
let progressEmpty = document.querySelector(".progress-empty");
let progressFull = document.querySelector(".progress-full");
cercle[compt].style.background="black";
color="#005e73";
// clickNext.onclick = () => {
$(document).ready(function() {

    $('#click-next').submit(function(event) {
        $('.invalid-feedback').remove();
        event.preventDefault();
          var formData = $(this).serialize();
          var url = $(this).attr('action');
          $.ajax({
            url: url,
            type: 'POST',
            data: formData,
            success: function(response) {
                if (compt < 4) {
                    compt++;
                    cercle[compt].style.background = "black";
                    cercle[compt - 1].style.background = color;
                    if (compt == 3) {
                        clickNext.style.display = "none";
                        cercle[compt].style.background = color
                        cercle[compt - 1].innerHTML = '<svg  width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round">  <path stroke="none" d="M0 0h24v24H0z"/>  <path d="M5 12l5 5l10 -10" /></svg>'
                        cercle[compt].innerHTML = '<svg  width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round">  <path stroke="none" d="M0 0h24v24H0z"/>  <path d="M5 12l5 5l10 -10" /></svg>'
                    }else{
                        cercle[compt - 1].innerHTML = '<svg  width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round">  <path stroke="none" d="M0 0h24v24H0z"/>  <path d="M5 12l5 5l10 -10" /></svg>'
                    }
                    // content of divs
                    step[compt].style.display = "block";
                    step[compt - 1].style.display = "none";
                    // end
                   
                    progressFull.style.width = `${compt * 33}%`;
                }
                $('.message-success-updated').text(response.message);
            },
            error: function(xhr) {
              var errors = xhr.responseJSON.errors;
              $.each(errors, function(key, value) {
                console.log(key);
                console.log(value)
                $('#' + key).after('<span class="text-danger"><strong>' + value + '</strong></span>');
            });
            }
        });
   
    });
});
// }