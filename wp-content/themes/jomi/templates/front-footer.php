<footer>
  <div class='row'>
	  <h4>Specialties:</h4>
  </div>
  <div class='row'>
	  <div class='col-md-12'>
		  <nav class="nav-bottom">
		    <ul>
		      <div class='col-md-4'>
		      	<li><a href="/articles/" class="border">All</a></li>
		      </div>
		      <div class='col-md-4'>
		      	<li><a href="/orthopedics/" class="border">Orthopedics</a></li>
		      </div>
		      <div class='col-md-4'>
		      	<li><a href="/general/" class="border">General Surgery</a></li>
		      </div>
		    </ul>

		    <ul>
		      <div class='col-md-4'>
		      	<li class='coming-soon'><a href="/area-notification-request/?area=ENT" class="border">ENT</a></li>
		      </div>
		      <div class='col-md-4'>
		      	<li class='coming-soon'><a href="/area-notification-request/?area=ophthalmology" class="border">Ophthalmology</a></li>
		      </div>
		      <div class='col-md-4'>
		      	<li class='coming-soon'><a href="/area-notification-request/?area=fundamentals" class="border">Fundamentals</a></li>
		      </div>
		    </ul>
		  </nav>
	  </div>
  </div>
</footer>

<script>
$('li').each(function(index){
	if($(this).hasClass('coming-soon')) {
		$(this).attr('og', $(this).find('a').text());
		$(this).hover( function() {
			$(this).find('a').text('Coming Soon');
		}, function() {
			$(this).find('a').text($(this).attr('og'));
		});
	}
});
</script>