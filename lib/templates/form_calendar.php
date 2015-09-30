<h3>{{ title }}</h3>

{% if showForm %}
  <form action="{{ saveUrl }}" method="POST" enctype="multipart/form-data" class="form-horizontal" role="form" id="calendarForm">
    {% for row in data %}
    <input type="hidden" name="{{ row['name'] }}" value="{{ row['_id'] }}" class="_id" />
  
    {% for element in row['elements'] %} {% include 'form_row.php' %} {% endfor %} {% if row['menu'] %}
    <div class="form-group">
      <div class="col-sm-12 text-right">{% include 'form_menu.php' with { 'menu' : row['menu'].getItemAt(0) } %}</div>
    </div>
    {% endif %} {% endfor %}
  
    <div class="col-sm-offset-2 col-sm-10">
      <div class="form-group">
        <button class="btn btn-cancel">
          <span class="glyphicon glyphicon-remove"></span> Cancel
        </button>
        <button type="submit" class="btn btn-primary">
          <span class="glyphicon glyphicon-ok"></span> Save
        </button>
      </div>
    </div>
  </form>
  
  <script>
  (function() {
  	$(document).ready(init);
  
  	function init() {
  		  $('form#calendarForm').submit(onSubmit);
  		  $('form#calendarForm .btn-cancel').click(onCancel);
  	}

  	function onCancel(e) {
  		e.preventDefault();

    	$('#calendarForm').trigger('sy-form-calendar-close');
  		
  		$.fancybox.close();
  	}
  
  	function onSubmit(e) {
  		var $this = $(this), url, data;
  
  		e.preventDefault();
  
  		url = $this.attr('action');
  		data = $this.find(':input');
  		
  		$.post(url, data, function(response) {
  			/*if (response.data) {
  				$(':input._id').each(function(k, v) {
  					$(this).val(response.data[k]._id);
  				});
  			}*/

  			$('#calendarForm').trigger('sy-form-calendar-save');

  			$.fancybox.close();
  		});
  	}
  }());
  </script>
{% else %}
  <div class="sy-form-calendar" data-calendar='{{ calendarOptions }}'></div>
  
  <script>
  (function() {
  	$(document).ready(init);
  
  	function init() {
  		$('.sy-form-calendar').each(function() {
  			var $this = $(this);

  			var options = {
  				header: {
  					left: 'title',
  					right: 'month,agendaWeek,agendaDay today prev,next'
  				},
  				lang:'pt-br',
  				height: 'auto'
  	  		};
  
  			$.extend(options, $this.data('calendar'));
  			$.extend(options, {
  		    	events: function(start, end, timezone, callback) {
  		      		  $.ajax({
  		                    url: options.dataUrl,
  		                    dataType:'json',
  		                    data: {
  		                        start: start.unix(),
  		                        end: end.unix()
  		                    },
  		                    success: function(data) {
  		                        callback(data.data);
  		                    }
  		      		  });
  		        },
  		        
  		        dayClick: function(date, jsEvent, view) {
  	  		        var time = Math.floor(date.valueOf() / 1000);
  			        $.fancybox.open({
  				        type : 'ajax',
  				        href: options.createUrl.replace(/__startTime__/, time),
  				        modal : true,
    				      scrolling : 'no',
    				      afterShow: function() {
    				    	  $('#calendarForm').on('sy-form-calendar-save', function() {
    				    		  $this.fullCalendar('refetchEvents');
    				    	  });
    				      }
  				      });
  		        },
  
  		        eventClick: function( event, jsEvent, view ) {
  		        	$.fancybox.open({
  				        type : 'ajax',
  				        href: event.editUrl,
  				        modal : true,
    				      scrolling : 'no',
    				      afterShow: function() {
    				    	  $('#calendarForm').on('sy-form-calendar-save', function() {
    				    		  $this.fullCalendar('refetchEvents');
    				    	  });
    				      }
  				      });
  			      }
  		    });
  
  			$this.fullCalendar(options);
  		});
  	}
  }());
  </script>
{% endif %}