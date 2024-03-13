<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://www.phptutorial.net/app/css/style.css">
    <title>Update Pengguna</title>
</head>
<script type="text/javascript" src="https://code.jquery.com/jquery-3.6.4.min.js"> </script>

 <script type="text/javascript">
$(document).ready(function() {
  $('#role').change(function() {
    var val = $("#role").val();
    if(val === "approval"){
        //MASIH BUG HELP
    $.ajax({
      url: 'register-jabatan.php',
      type: 'POST',
      dataType: 'html',
      success: function(data) {
        //alert(val);
        $('#jabatan').html(data);
      },
      error: function() {
        alert(val);
        console.log('Something went wrong!');
      }
    });
    }
    else{
        alert("haah");
    }
    //alert("um");
  });
});
    </script>
<body>
<main>
<?php
    $user=$_SESSION['user'];
    echo "<a href='/api/user-detail/" . $user['id'] . "'> Kembali </a>";
    
    echo "<form action='/api/updateUser/" . $user['id'] . "'method='post'>"?>
        <h1>Edit Pengguna</h1>
        <div>
            <label for="name">Nama Lengkap:</label>
            <input type="text" name="name" id="name">
        </div>
        <div>
            <label for="nomorpegawai">NPP:</label>
            <input type="text" name="nomorpegawai" id="nomorpegawai">
        </div>
        <div>
            <label for="role">Pilih role</label>
            <select name="role" id="role">
                <option value="">Pilih role</option>
            <option value="approval">Kepala Divisi</option>
        <option value="checker">Kepala Bagian</option>
            </select>
        </div>
        <div>
            <label for="jabatan">Pilih jabatan di Jamkrindo</label>
            <select name="jabatan" id="jabatan">
            <!-- <option class="jabatan-select" value="kabag2"> test</option> -->
                <option value=""></option>
        </div>
    
        <div>
            <label for="agree">
                <input type="checkbox" name="agree" id="agree" value="yes"/> I agree
                with the
                <a href="#" title="term of services">term of services</a>
            </label>
        </div>
        <button type="submit">Update</button>
    </form>
</main>
</body>
</html>