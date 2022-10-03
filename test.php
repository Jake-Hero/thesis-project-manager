<style>
    .reply_container {
  display: none;
}
.comment {
  border-bottom: 1px solid #999;
  padding: 5px;
  cursor: pointer;
}
</style>

<script src="https://ajax.googleapis.com/ajax/libs/jquery/2.1.1/jquery.min.js"></script>
<div class="comments-pane">
  <div class="comment" id="4042311">
    The first one
  </div>
  <div class="comment" id="4042313">
    A follow-up comment
  </div>
  <div class="comment" id="4042317">
    Yet a third comment.
  </div>
</div>
  
  
<div class="reply_container">
  <h3></h3>
<form>
<input type="hidden" name="comment_id">
<input type="text" value="" name="reply_text">
<button type="submit">Reply</button>
</form>
</div>

<script>
$(".comments-pane").on("click", ".comment", function(){
  var el = $(this);
  var elID = $(this).attr("id");
  var elText = $(this).text();
  
  $(".reply_container")
     .find("h3")
       .text("Your reply to "+elText).end()
     .find(".comment_id")
       .val(elID).end()
     .show();
   });
</script>