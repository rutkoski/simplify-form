<div class="form-inline">
  <div class="input-group col-sm-5" id="{{ id }}_begin">
    <input type="text" name="{{ inputName }}[begin]" value="{{ formatedBeginValue }}" class="form-control"/>
    <span class="input-group-addon"><span class="glyphicon glyphicon-calendar"></span></span>
  </div>
  
  <div class="input-group col-sm-5" id="{{ id }}_end">
    <input type="text" name="{{ inputName }}[end]" value="{{ formatedEndValue }}" class="form-control"/>
    <span class="input-group-addon"><span class="glyphicon glyphicon-calendar"></span></span>
  </div>
</div>

<script>
$(document).ready(function() {
	var $begin, $end, begin, end, options;

	$begin = $('#{{ id }}_begin');

	$end = $('#{{ id }}_end');

	options = {
		locale: 'pt-br'
	};

	begin = $begin.datetimepicker(options).data("DateTimePicker");

	end = $end.datetimepicker(options).data("DateTimePicker");

	$begin.on('dp.change', function(e) {
		if (begin.date().unix() > end.date().unix()) {
			  end.date(e.date.add({{ defaultRange }}, 's'));
		}
	});

	$end.on('dp.change', function(e) {
		if (begin.date().unix() > end.date().unix()) {
			  begin.date(e.date.subtract({{ defaultRange }}, 's'));
		}
	});
});
</script>