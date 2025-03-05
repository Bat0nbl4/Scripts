<div class="entry">
    <label>
        <script>
            function getFileName() {
                var file = document.getElementById('file').files[0].name;
                if (file == "") {
                    document.getElementById('file-name').innerHTML = 'Выберете обложку';
                } else {
                    document.getElementById('file-name').innerHTML = file;
                }
            }
        </script>
        <input type="file" name="file" id="file" onchange="getFileName()" hidden>
        <span id="file-name" class="button full-w title">Выберете файл</span>
    </label>
</div>
