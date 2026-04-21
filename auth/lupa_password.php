<?php
include "koneksi.php";
session_start();

if(isset($_POST['email'])){
  $email = mysqli_real_escape_string($koneksi,$_POST['email']);
  $cek = mysqli_query($koneksi,"SELECT * FROM calon_maba WHERE email='$email'");
  if(mysqli_num_rows($cek)==0){
    $_SESSION['popup']=['type'=>'error','title'=>'Email Tidak Ditemukan','message'=>'Email belum terdaftar.'];
    header("Location:lupa_password.php"); exit;
  }

  $token = bin2hex(random_bytes(32));
  $expiry = date("Y-m-d H:i:s", strtotime("+30 minutes"));
  mysqli_query($koneksi,"UPDATE calon_maba SET reset_token='$token', reset_expiry='$expiry' WHERE email='$email'");

  $link = "http://localhost/reset_password.php?token=$token";

  // ===== kirim email basic =====
  $subject = "Reset Password Akun PMB";
  $message = "Klik link berikut untuk reset password:\n\n$link\n\nLink berlaku 30 menit.";
  $headers = "From: noreply@kampus.ac.id";

  mail($email,$subject,$message,$headers);

  $_SESSION['popup']=['type'=>'success','title'=>'Link Terkirim','message'=>'Cek email kamu untuk reset password.'];
  header("Location:lupa_password.php"); exit;
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<title>Lupa Password</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@500;700;800&display=swap" rel="stylesheet">

<style>
:root{--primary:#0d3b66;--secondary:#0d6efd;}
body{
  font-family:'Plus Jakarta Sans',sans-serif;
  background:linear-gradient(135deg,#f8fafc,#f1f5f9);
  min-height:100vh;
  display:flex;
  align-items:center;
  justify-content:center;
}

/* popup modal */
.modal-overlay{
  position:fixed;inset:0;
  background:rgba(13,59,102,.35);
  backdrop-filter:blur(8px);
  display:none;
  align-items:center;
  justify-content:center;
  z-index:9999;
}
.modal-box{
  width:360px;
  background:white;
  border-radius:22px;
  overflow:hidden;
  box-shadow:0 30px 70px rgba(13,59,102,.35);
  animation:fadeScale .25s ease;
}
.modal-header{
  background:linear-gradient(135deg,#0d3b66,#0d6efd);
  padding:24px;
  text-align:center;
}
.modal-header i{font-size:44px;color:white;}
.modal-body{
  padding:22px;
  text-align:center;
}
.modal-body h4{font-weight:800;color:#0d3b66;}
.modal-body p{font-size:14px;color:#64748b;}
.modal-body button{
  width:100%;
  border:none;
  border-radius:12px;
  padding:12px;
  background:linear-gradient(135deg,#0d3b66,#0d6efd);
  color:white;
  font-weight:700;
}
.modal-error .modal-header{background:linear-gradient(135deg,#7c0a02,#dc3545);}
.modal-success .modal-header{background:linear-gradient(135deg,#0d3b66,#0d6efd);}

@keyframes fadeScale{
  from{opacity:0;transform:scale(.92);}
  to{opacity:1;transform:scale(1);}
}

/* card */
.card-reset{
  background:white;
  border-radius:22px;
  box-shadow:0 20px 50px rgba(13,59,102,.15);
  padding:36px;
  width:360px;
}
.card-reset h3{font-weight:800;color:#0d3b66;}
.card-reset p{font-size:14px;color:#64748b;}
.btn-reset{
  background:linear-gradient(135deg,#0d3b66,#0d6efd);
  border:none;
  color:white;
  font-weight:700;
  padding:12px;
  border-radius:12px;
  width:100%;
}
</style>
</head>
<body>

<!-- popup -->
<div class="modal-overlay" id="modalNotif">
  <div class="modal-box" id="modalBox">
    <div class="modal-header">
      <i class="bi bi-check-circle-fill" id="modalIcon"></i>
    </div>
    <div class="modal-body">
      <h4 id="modalTitle">Berhasil</h4>
      <p id="modalMessage">Pesan</p>
      <button onclick="closeModal()">Oke</button>
    </div>
  </div>
</div>

<div class="card-reset">
  <h3 class="mb-2">Lupa Password</h3>
  <p>Masukkan email yang terdaftar untuk menerima link reset password.</p>

  <form method="POST">
    <div class="mb-3">
      <input type="email" name="email" class="form-control rounded-3" placeholder="Email terdaftar" required>
    </div>
    <button class="btn-reset">
      <i class="bi bi-envelope-paper me-1"></i>Kirim Link Reset
    </button>
  </form>

  <div class="text-center mt-3">
    <a href="login.php" class="text-decoration-none fw-semibold text-primary">
      ← Kembali ke Login
    </a>
  </div>
</div>

<script>
function showModal(type,title,message){
  const modal=document.getElementById("modalNotif");
  const box=document.getElementById("modalBox");
  const icon=document.getElementById("modalIcon");
  box.className="modal-box modal-"+type;
  icon.className=type==="success"?"bi bi-check-circle-fill":"bi bi-x-circle-fill";
  document.getElementById("modalTitle").innerText=title;
  document.getElementById("modalMessage").innerText=message;
  modal.style.display="flex";
}
function closeModal(){
  document.getElementById("modalNotif").style.display="none";
}
</script>

<?php if(isset($_SESSION['popup'])): ?>
<script>
showModal(
  "<?= $_SESSION['popup']['type'] ?>",
  "<?= $_SESSION['popup']['title'] ?>",
  "<?= $_SESSION['popup']['message'] ?>"
);
</script>
<?php unset($_SESSION['popup']); endif; ?>

</body>
</html>
