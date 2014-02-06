$(document).ready(function() {
	$('#anonmode').click(function() {
		if (this.className === 'user')
		{
			this.remove();
			$('#name').replaceWith('<input type="text" id="postername" name="postername" />');
		}
		else
		{
			alert('moo. do not click');
		}
	});

	$('.postbutton').click(function() {
		if ($(':checkbox[class="postbutton"]', $('#board')).is(':checked'))
		{
			// ffs intellij
			var quickmenu = $('#modmenu');

			if (quickmenu.is(':hidden'))
			{
				quickmenu.fadeIn("medium");
			}
		}
		else
		{
			$('#modmenu').fadeOut("medium");
		}
	});

	$('#modSubmit').click(function() {
		alert('EXCEPTION 509: PERMISSION DENIED.');
	});
});