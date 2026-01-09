<script>
$(document).ready(function() {
    // Handle the share form submission
    $('.share-form').on('submit', function(e) {
        e.preventDefault();
        var form = $(this);
        var fileId = form.data('file-id');
        var email = form.find('input[type="email"]').val();

        $.ajax({
            url: '/share/add/' + fileId,
            type: 'POST',
            data: { email: email },
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    location.reload();
                } else {
                    alert('Error: ' + response.error);
                }
            },
            error: function() {
                alert('An unexpected error occurred.');
            }
        });
    });

    // Handle the unshare button click
    $('.unshare-btn').on('click', function(e) {
        e.preventDefault();
        var link = $(this);
        var url = link.attr('href');

        $.ajax({
            url: url,
            type: 'GET',
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    link.closest('li').remove();
                } else {
                    alert('Error: ' + response.error);
                }
            },
            error: function() {
                alert('An unexpected error occurred.');
            }
        });
    });
});
</script>
</body>
</html>
