// Requires jquery

$(document).ready(function() {
  $(document).on("click", "table button", function() {
    console.log(this);
    claim_gift(this.dataset.giftId);
  });
});

function claim_gift(gift_ident)
{
  console.log(gift_ident);
  // Confirm choice to claim gift
  answer = confirm("You are about to claim this gift to bring to Madeline's Party. Click OK to continue.");
  if (!answer)
  {
    return;
  }
  
  gift_row = $('#gift_'+gift_ident);
  gift_claim_button = $('#gift_'+gift_ident+'_claim');

  // Set claim status in button field
  //gift_claim_button.text("");

  // Make AJAX call
  $.ajax({ url: "claim_gift.php",
           data: { item_id: gift_ident },
           dataType: "text",
           success: function(data, textStatus, xhr)
             {
               // Change style tag to "claimed my_claim" for gift row
               gift_row.addClass("claimed");
               gift_row.addClass("my-claim");

               // Set button field to value of returned data
               gift_claim_button.text(data);
               gift_claim_button.addClass("btn-default");
               gift_claim_button.removeClass("gift-btn");
               gift_claim_button.prop("disabled", true);
             },
           error: function(xhr, textStatus, errorThrown)
             {
               // Alert user there was a failed claim
               alert(xhr.statusText);

               // Reload the page
               window.location.reload();
             }
         });
}

