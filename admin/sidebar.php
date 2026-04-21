<style>
@import url("https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css");

.sidebar {
    width: 220px;
    height: 100vh;
    position: fixed;
    top: 0;
    left: 0;
    padding: 24px 16px;
    background: linear-gradient(180deg, #0d3b66, #0d6efd);
    font-family: 'Segoe UI', Arial, sans-serif;
    color: #fff;
    display: flex;
    flex-direction: column;
    gap: 8px;
    box-shadow: 2px 0 15px rgba(0,0,0,0.15);
}

/*LOGO*/
.sidebar .logo {
    display: flex;
    justify-content: center;
    margin-bottom: 10px;
}
.sidebar .logo img {
    width: 95px;
    height: auto;
}

/*JUDUL*/
.sidebar h2 {
    text-align: center;
    margin-bottom: 24px;
    font-size: 16px;
    font-weight: 600;
    letter-spacing: 1px;
}

/*MENU*/
.sidebar a {
    color: #ffffff;
    text-decoration: none;
    padding: 12px 14px;
    border-radius: 10px;
    font-size: 14px;
    font-weight: 500;
    display: flex;
    align-items: center;
    gap: 12px;
    transition: all 0.25s ease;
    background: rgba(255,255,255,0.08);
}
.sidebar a:hover {
    background: rgba(255,255,255,0.2);
    transform: translateX(4px);
}

/*ICON BASE*/
.sidebar .icon::before {
    font-family: "bootstrap-icons";
    font-size: 16px;
}

/*ICON MAPPING*/
.sidebar .dashboard .icon::before    { content: "\f425"; }
.sidebar .mahasiswa .icon::before    { content: "\f4e1"; }
.sidebar .daftar-ulang .icon::before { content: "\f4d8"; }
.sidebar .soal .icon::before         { content: "\f4cb"; }
.sidebar .hasil .icon::before        { content: "\f3f1"; }
.sidebar .logout .icon::before       { content: "\f1c3"; }

/*JAM*/
.sidebar .clock {
    margin-top: auto;
    margin-bottom: 12px;
    text-align: center;
    font-size: 14px;
    font-weight: 500;
    padding: 8px;
    border-radius: 8px;
    background: rgba(255,255,255,0.15);
}

/*LOGOUT*/
.sidebar .logout {
    background: rgba(255,255,255,0.18);
}
.sidebar .logout:hover {
    background: rgba(255,255,255,0.3);
}
</style>

<div class="sidebar">
    <div class="logo">
        <img src="../assets/logokampus1.png" alt="Logo Universitas">
    </div>
    <h2>ADMIN PANEL</h2>
    <a href="dashboard_admin.php" class="dashboard">
        <span class="icon"></span>Dashboard
    </a>
    <a href="calon_maba.php" class="mahasiswa">
        <span class="icon"></span>Calon Mahasiswa
    </a>
    <a href="verifikasi_daftar_ulang.php" class="daftar-ulang">
        <span class="icon"></span>Daftar Ulang
    </a>
    <a href="soal_tes.php" class="soal">
        <span class="icon"></span>Soal Tes
    </a>
    <a href="hasil_tes.php" class="hasil">
        <span class="icon"></span>Hasil Tes
    </a>
    <div class="clock" id="clock">00:00:00</div>
    <a href="../auth/logout_admin.php" class="logout">
        <span class="icon"></span>Logout
    </a>
</div>

<script>
function updateClock() {
    const now = new Date();
    document.getElementById('clock').innerText =
        now.toLocaleTimeString('id-ID');
}
setInterval(updateClock, 1000);
updateClock();
</script>