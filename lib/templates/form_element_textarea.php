<textarea name="{{ inputName }}" class="form-control" 
  {{ maxLength > 0 ? ' maxlength="' ~ maxLength ~ '"' : '' }}>{{ value }}</textarea>