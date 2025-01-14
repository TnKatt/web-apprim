document.querySelector(".submit-btn").addEventListener("click", function(event) {
    document.getElementById("loading-popup").style.display = "flex";
    setTimeout(function() {
        window.location.href = "../admin/daftar-ruang.php?id_gedung=<?= $id_gedung ?>";
    }, 3000);
});