<?php include 'partials/header.php'; ?>

<div class="container">
    <h2>My Files</h2>
    
    <?php if (isset($message)): ?>
        <div class="alert alert-success"><?php echo $message; ?></div>
    <?php endif; ?>
    <?php if (isset($error)): ?>
        <div class="alert alert-danger"><?php echo $error; ?></div>
    <?php endif; ?>

    <h3>Upload File</h3>
    <form action="/files/add" method="post" enctype="multipart/form-data">
        <div class="form-group">
            <input type="file" name="file" class="form-control-file">
        </div>
        <button type="submit" class="btn btn-primary">Upload</button>
    </form>

    <hr>

    <h3>My Files</h3>
    <ul class="list-group">
        <?php foreach ($files as $file): ?>
            <li class="list-group-item" id="file-<?php echo $file['id']; ?>">
                <?php echo htmlspecialchars($file['name']); ?>
                <a href="/files/remove/<?php echo $file['id']; ?>" class="btn btn-danger btn-sm float-right delete-file-btn">Delete</a>
                <button class="btn btn-info btn-sm float-right mr-2" data-toggle="modal" data-target="#shareModal-<?php echo $file['id']; ?>">Share</button>

                <!-- Share Modal -->
                <div class="modal fade" id="shareModal-<?php echo $file['id']; ?>" tabindex="-1" role="dialog">
                    <div class="modal-dialog" role="document">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title">Share <?php echo htmlspecialchars($file['name']); ?></h5>
                                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                    <span aria-hidden="true">&times;</span>
                                </button>
                            </div>
                            <div class="modal-body">
                                <form class="share-form" data-file-id="<?php echo $file['id']; ?>">
                                    <div class="form-group">
                                        <label for="email-<?php echo $file['id']; ?>">Email address</label>
                                        <input type="email" class="form-control" id="email-<?php echo $file['id']; ?>" placeholder="Enter email">
                                    </div>
                                    <button type="submit" class="btn btn-primary">Share</button>
                                </form>
                                <hr>
                                <h5>Shared with:</h5>
                                <ul class="list-group shared-with-list">
                                    <?php foreach ($file['shared_with'] as $share): ?>
                                        <li class="list-group-item" data-user-id="<?php echo $share['id']; ?>">
                                            <?php echo htmlspecialchars($share['email']); ?>
                                            <a href="/share/remove/<?php echo $file['id']; ?>/<?php echo $share['id']; ?>" class="btn btn-warning btn-sm float-right unshare-btn">Unshare</a>
                                        </li>
                                    <?php endforeach; ?>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            </li>
        <?php endforeach; ?>
    </ul>

    <hr>

    <h3>Shared With Me</h3>
    <ul class="list-group">
        <?php foreach ($sharedFiles as $file): ?>
            <li class="list-group-item">
                <?php echo htmlspecialchars($file['name']); ?>
            </li>
        <?php endforeach; ?>
    </ul>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const deleteButtons = document.querySelectorAll('.delete-file-btn');
    deleteButtons.forEach(button => {
        button.addEventListener('click', function (event) {
            event.preventDefault();
            const url = this.getAttribute('href');
            const fileId = url.split('/').pop();
            
            if (confirm('Are you sure you want to delete this file?')) {
                fetch(url, {
                    method: 'DELETE',
                })
                .then(response => response.json())
                .then(data => {
                    if (data.message) {
                        // Remove the file item from the list
                        const fileItem = document.getElementById('file-' + fileId);
                        if (fileItem) {
                            fileItem.remove();
                        }
                    } else if (data.error) {
                        alert('Error: ' + data.error);
                    }
                })
                .catch(error => console.error('Error:', error));
            }
        });
    });
});
</script>

<?php include 'partials/footer.php'; ?>
