<label for="filters_term">
	Term:
</label>
<select name="filters[term]" id="filters_term" class="form-control">
	<option></option>
{% for term in terms %}
	<option value="{{ term.canvas_term_id }}">{{ term.name }}</option>
{% endfor %}
</select>