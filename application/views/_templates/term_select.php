<div class="form-group">
	<label for="filters_term" class="col-sm-2 control-label">
		Select Term:
	</label>
	<div class="col-sm-8">
		<select name="filters[term]" id="filters_term" class="form-control">
			<option></option>
		{% for term in terms %}
			<option value="{{ term.canvas_term_id }}" {% if filters.term == term.canvas_term_id %}selected{% endif %}>{{ term.name }}</option>
		{% endfor %}
		</select>
	</div>
</div>